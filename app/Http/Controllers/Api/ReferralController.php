<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PointHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferralController extends Controller
{
    // POST /api/referrals/redeem
    public function redeem(Request $request)
    {
        $request->validate([
            'referral_code' => 'required|string',
        ]);

        $currentUser = Auth::user();

        // 1. Cek apakah user memasukkan kodenya sendiri
        if ($currentUser->referral_code === $request->referral_code) {
            return response()->json(['message' => 'Tidak bisa menggunakan kode referral sendiri'], 400);
        }

        // 2. Cari pemilik kode referral tersebut
        $referrer = User::where('referral_code', $request->referral_code)->first();

        if (!$referrer) {
            return response()->json(['message' => 'Kode referral tidak valid'], 442);
        }

        // 3. Cek apakah user ini sebelumnya sudah pernah redeem referral (biar ga spam)
        $alreadyRedeemed = PointHistory::where('user_id', $currentUser->id)
            ->where('description', 'like', 'Redeem kode referral%')
            ->exists();

        if ($alreadyRedeemed) {
            return response()->json(['message' => 'Kamu sudah pernah mengklaim kode referral'], 400);
        }

        // 4. Proses pembagian hadiah poin (Misal: masing-masing dapet 100 poin)
        $bonusPoints = 100;

        // Tambah poin ke user yang meredeem
        $currentUser->increment('points', $bonusPoints);
        PointHistory::create([
            'user_id' => $currentUser->id,
            'amount' => $bonusPoints,
            'description' => 'Redeem kode referral dari ' . $referrer->name
        ]);

        // Tambah poin ke pemilik kode (referrer)
        $referrer->increment('points', $bonusPoints);
        PointHistory::create([
            'user_id' => $referrer->id,
            'amount' => $bonusPoints,
            'description' => 'Bonus referral dari ' . $currentUser->name
        ]);

        // 5. Update Tier Otomatis berdasarkan point baru (Simple Logic)
        $this->updateUserTier($currentUser);
        $this->updateUserTier($referrer);

        return response()->json([
            'message' => 'Kode referral berhasil di-redeem! Bonus 100 poin telah ditambahkan.',
            'current_points' => $currentUser->points
        ], 200);
    }

    private function updateUserTier($user)
    {
        if ($user->points >= 1000) {
            $user->update(['tier' => 'Gold']);
        } elseif ($user->points >= 500) {
            $user->update(['tier' => 'Silver']);
        } else {
            $user->update(['tier' => 'Bronze']);
        }
    }
}
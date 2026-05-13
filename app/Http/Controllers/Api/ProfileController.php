<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class ProfileController extends Controller
{
    /**
     * Menampilkan data profile user yang sedang login.
     */
    public function show(Request $request)
    {
        // Mengambil data user berdasarkan token yang dikirim di header
        $user = $request->user();

        // Jika user tidak ditemukan (meskipun harusnya sudah dicegat middleware)
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        // Return response JSON
        return response()->json([
            'success' => true,
            'message' => 'Data profile berhasil diambil',
            'data' => $user
        ], 200);
    }
}
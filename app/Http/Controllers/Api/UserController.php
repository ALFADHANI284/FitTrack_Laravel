<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::query()->latest()->get();

        return response()->json([
            'status' => true,
            'message' => 'List users',
            'data' => $users,
        ], 200);
    }

    public function show(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User tidak ditemukan',
                'data' => null,
            ], 404);
        }

        $authUser = $request->user();
        if ($authUser->role !== 'admin' && $authUser->id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Tidak memiliki akses',
            ], 403);
        }

        return response()->json([
            'status' => true,
            'message' => 'Detail user',
            'data' => $user,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User tidak ditemukan',
                'data' => null,
            ], 404);
        }

        $authUser = $request->user();
        if ($authUser->role !== 'admin' && $authUser->id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Tidak memiliki akses',
            ], 403);
        }

        $rules = [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
        ];

        if ($authUser->role === 'admin') {
            $rules['role'] = 'sometimes|string|in:admin,user';
        }

        $validated = $request->validate($rules);
        $user->fill($validated);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'User berhasil diupdate',
            'data' => $user,
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User tidak ditemukan',
                'data' => null,
            ], 404);
        }

        $authUser = $request->user();
        if ($authUser->role !== 'admin' && $authUser->id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Tidak memiliki akses',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'status' => true,
            'message' => 'User berhasil dihapus',
            'data' => null,
        ], 200);
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|max:2048',
        ]);

        $user = $request->user();
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar_path = $path;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Avatar berhasil diupload',
            'data' => [
                'avatar_path' => $path,
            ],
        ], 200);
    }
}

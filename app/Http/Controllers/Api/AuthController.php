<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Register
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()){
            return response()->json([
            'message' => 'Validasi gagal',
            'errors'  => $validator->errors()
        ], 422);
}

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user'
        ]);

        $token = $user->createToken('FitTrack_AuthToken')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil',
            'data' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'role' => $user->role
        ], 201);
    }

    // Login
    public function login(Request $request){

    if (!Auth::attempt($request->only('email', 'password'))) {
        return response()->json([
            'message' => 'Email atau password salah'
        ], 401);
    }

    $user = User::where('email', $request->email)->firstOrFail();
    $token = $user->createToken('FitTrack_AuthToken')->plainTextToken;

    return response()->json([
        'message' => 'Login berhasil',
        'data' => $user,
        'access_token' => $token,
        'token_type' => 'Bearer',
        'role' => $user->role
    ]);
    }

    //Logout 
    public function logout(Request $request){
        // Menghapus token yang sedang digunakan
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }
}

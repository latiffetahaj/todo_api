<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UserRegistrationRequest;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    //
    public function register(UserRegistrationRequest $request)
    {
        $user = User::create($request->validated());

        $user->assignRole($request->role);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function login(Request $request) 
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            $user = auth()->user()->load('roles');

            $token = JWTAuth::claims(['role' => $user->role])->fromUser($user);

            return response()->json([
                'message' => 'Login successful',
                'user' => Auth::user(),
                'token' => $token
            ], 200);

        } catch (JWTException $e) {
            return response()->json(['message' => 'Could not create token'], 500);
        }
        
        

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
    } 
    
    
    }
}

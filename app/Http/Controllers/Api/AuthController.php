<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;


class AuthController extends Controller
{
    /**
     * Register a new user
     */


     public function register(Request $request)
     {
         $request->validate([
             'name' => 'required|string|max:255',
             'email' => 'required|string|email|max:255|unique:users',
             'password' => 'required|string|min:6|confirmed',
             'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
         ]);
     
         $imagePath = 'image.png'; // default
     
         if ($request->hasFile('image')) {
             $imagePath = $request->file('image')->store('users', 'public');
         }
     
         $user = User::create([
             'name' => $request->name,
             'email' => $request->email,
             'password' => bcrypt($request->password),
             'is_admin' => false,
             'image' => $imagePath,
         ]);
     
         event(new Registered($user));
     
         return response()->json([
             'message' => "User registered successfully. Please check your email.",
             'user' => $user,
         ]);
     }
     

    /**
     * Authenticate user and return token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = $request->user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Get authenticated user details
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function redirectToGoogle()
    {
        return response()->json([
            'url' => Socialite::driver('google')
                ->stateless()  // Critical for APIs
                ->redirect()
                ->getTargetUrl(),  // Get the Google OAuth URL
        ]);
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')
                ->stateless()  // Critical for APIs
                ->user();

            // Find or create user
            $user = User::firstOrCreate(
                ['email' => $googleUser->email],
                [
                    'name' => $googleUser->name,
                    'password' => bcrypt(Str::random(16)),
                    'email_verified_at' => now(),
                ]
            );

            // Generate API token
            $token = $user->createToken('google-token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Google authentication failed',
                'message' => $e->getMessage(),
            ], 401);
        }
    }
}

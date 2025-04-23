<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Auth\Events\Verified;

class VerificationController extends Controller
{
    /**
     * Verify email without requiring authentication
     */

    public function verify(Request $request, $id, $hash)
    {
        // 1. Find the user
        $user = User::findOrFail($id);

        // 2. Verify the hash
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Invalid verification link'], 403);
        }

        // 3. Check if already verified
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified']);
        }

        // 4. Mark as verified
        $user->markEmailAsVerified();
        event(new Verified($user));

        // 5. Return success response
        return response()->json([
            'message' => 'Email successfully verified',
            'verified' => true
        ]);
    }

    /**
     * Resend verification email (requires authentication)
     */
    public function resend(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified']);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification link resent']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Mail\OtpVerificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AuthController extends Controller
{
   public function register(Request $request)
   {
       $request->validate([
           'first_name' => 'required',
           'last_name' => 'required',
           'email' => 'required|email|unique:users',
           'password' => 'required|min:6|confirmed'
       ]);
   
       $otp = rand(100000, 999999);
   
       $user = User::create([
           'first_name' => $request->first_name,
           'last_name' => $request->last_name,
           'email' => $request->email,
           'password' => Hash::make($request->password),
           'otp_code' => $otp,
           'otp_expires_at' => Carbon::now()->addMinutes(10),
       ]);
   
       try {
           Mail::to($user->email)->send(new OtpVerificationMail($otp));
       } catch (\Throwable $error) {
           Log::error('Failed to send OTP email.', [
               'email' => $user->email,
               'message' => $error->getMessage(),
           ]);

           return response()->json([
               'message' => 'Account created, but the OTP email could not be sent. Please try resending the OTP.',
               'email' => $user->email,
           ], 500);
       }
   
       return response()->json([
           'message' => 'OTP sent successfully.',
           'email' => $user->email
       ]);
   }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string',
        ]);
    
        $user = User::where('email', $request->email)->first();
    
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }
    
        if ($user->otp_code !== $request->otp) {
            return response()->json(['message' => 'Invalid OTP.'], 422);
        }
    
        if (Carbon::now()->greaterThan($user->otp_expires_at)) {
            return response()->json(['message' => 'OTP has expired.'], 422);
        }
    
        $user->update([
            'email_verified_at' => now(),
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);
    
        $token = $user->createToken('auth_token')->plainTextToken;
    
        return response()->json([
            'message' => 'Email verified successfully.',
            'token' => $token,
            'user' => $user,
        ]);
    }
    
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        if (!$user->email_verified_at) {
            return response()->json([
                'message' => 'Please verify your email before logging in.',
                'code' => 'email_not_verified',
                'email' => $user->email,
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Email is already verified.'], 422);
        }

        $otp = rand(100000, 999999);

        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        try {
            Mail::to($user->email)->send(new OtpVerificationMail($otp));
        } catch (\Throwable $error) {
            Log::error('Failed to resend OTP email.', [
                'email' => $user->email,
                'message' => $error->getMessage(),
            ]);

            return response()->json([
                'message' => 'The OTP email could not be sent. Please check the mail settings.',
            ], 500);
        }

        return response()->json([
            'message' => 'OTP sent successfully.',
            'email' => $user->email,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}

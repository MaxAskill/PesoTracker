<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetOtp;
use App\Models\User;
use App\Services\OtpEmailService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PasswordResetController extends Controller
{
    private const GENERIC_MESSAGE = 'If the email exists, a password reset OTP has been sent.';

    public function __construct(private OtpEmailService $otpEmailService)
    {
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower($request->email);
        $user = User::where('email', $email)->first();

        if (! $user) {
            return response()->json([
                'success' => true,
                'message' => self::GENERIC_MESSAGE,
            ]);
        }

        PasswordResetOtp::where('email', $email)->delete();

        $otp = (string) random_int(100000, 999999);

        PasswordResetOtp::create([
            'email' => $email,
            'otp' => $otp,
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        try {
            $this->otpEmailService->sendPasswordReset($email, $otp);
        } catch (\Throwable $error) {
            Log::error('Failed to send password reset OTP email.', [
                'email' => $email,
                'message' => $error->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'The password reset email could not be sent. Please check the mail settings.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => self::GENERIC_MESSAGE,
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ]);

        $otpRecord = $this->latestOtp(strtolower($request->email));

        if (! $otpRecord || ! $this->isUsableOtp($otpRecord, $request->otp)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP.',
            ], 422);
        }

        $otpRecord->update([
            'verified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'OTP verified. You can now reset your password.',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
            'password' => 'required|min:6|confirmed',
        ]);

        $email = strtolower($request->email);
        $user = User::where('email', $email)->first();
        $otpRecord = $this->latestOtp($email);

        if (! $user || ! $otpRecord || ! $this->isUsableOtp($otpRecord, $request->otp) || ! $otpRecord->verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        PasswordResetOtp::where('email', $email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password reset successful. You can now login.',
        ]);
    }

    private function latestOtp(string $email): ?PasswordResetOtp
    {
        return PasswordResetOtp::where('email', $email)
            ->latest()
            ->first();
    }

    private function isUsableOtp(PasswordResetOtp $otpRecord, string $otp): bool
    {
        return $otpRecord->otp === $otp &&
            Carbon::now()->lessThanOrEqualTo($otpRecord->expires_at);
    }
}

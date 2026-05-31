<?php

namespace App\Services;

use App\Mail\OtpVerificationMail;
use App\Mail\PasswordResetOtpMail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class OtpEmailService
{
    public function send(string $email, string $otp): void
    {
        if (config('services.brevo.key')) {
            $this->sendWithBrevo($email, $otp);

            return;
        }

        Mail::to($email)->send(new OtpVerificationMail($otp));
    }

    public function sendPasswordReset(string $email, string $otp): void
    {
        if (config('services.brevo.key')) {
            $this->sendWithBrevo(
                $email,
                $otp,
                'PesoTracker Password Reset OTP',
                'emails.password-reset-otp'
            );

            return;
        }

        Mail::to($email)->send(new PasswordResetOtpMail($otp));
    }

    private function sendWithBrevo(
        string $email,
        string $otp,
        string $subject = 'Your PesoTracker verification code',
        string $view = 'emails.otp-verification'
    ): void
    {
        $senderEmail = config('services.brevo.sender_email');
        $senderName = config('services.brevo.sender_name', 'PesoTracker');

        if (!$senderEmail) {
            throw new \RuntimeException('BREVO_SENDER_EMAIL is not configured.');
        }

        $response = Http::withHeaders([
            'accept' => 'application/json',
            'api-key' => config('services.brevo.key'),
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => $senderName,
                'email' => $senderEmail,
            ],
            'to' => [
                ['email' => $email],
            ],
            'subject' => $subject,
            'htmlContent' => view($view, [
                'otp' => $otp,
            ])->render(),
        ]);

        if ($response->failed()) {
            throw new \RuntimeException(
                'Brevo email API failed: '.$response->status().' '.$response->body()
            );
        }
    }
}

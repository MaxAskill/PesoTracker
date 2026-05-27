<?php

namespace App\Services;

use App\Mail\OtpVerificationMail;
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

    private function sendWithBrevo(string $email, string $otp): void
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
            'subject' => 'Your PesoTracker verification code',
            'htmlContent' => view('emails.otp-verification', [
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

<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('auth-api', function (Request $request) {
            return Limit::perMinute(120)
                ->by($this->rateLimitKey($request))
                ->response(fn (Request $request, array $headers) => $this->rateLimitResponse(
                    'Too many requests. Please slow down and try again shortly.',
                    $headers
                ));
        });

        RateLimiter::for('transaction-create', function (Request $request) {
            return Limit::perMinute(10)
                ->by($this->rateLimitKey($request))
                ->response(fn (Request $request, array $headers) => $this->rateLimitResponse(
                    'Too many transaction requests. Please wait before adding more transactions.',
                    $headers
                ));
        });

        RateLimiter::for('registration', function (Request $request) {
            return [
                Limit::perMinute(3)
                    ->by($request->ip())
                    ->response(fn (Request $request, array $headers) => $this->rateLimitResponse(
                        'Too many registration attempts. Please wait before trying again.',
                        $headers
                    )),
                Limit::perMinutes(60, 10)
                    ->by($request->ip())
                    ->response(fn (Request $request, array $headers) => $this->rateLimitResponse(
                        'Too many registration attempts. Please wait before trying again.',
                        $headers
                    )),
            ];
        });

        RateLimiter::for('otp-send', function (Request $request) {
            $email = strtolower(trim((string) $request->input('email')));

            return [
                Limit::perMinutes(10, 3)
                    ->by('email:'.$email)
                    ->response(fn (Request $request, array $headers) => $this->rateLimitResponse(
                        'Too many OTP requests. Please wait before requesting another code.',
                        $headers
                    )),
                Limit::perMinutes(10, 5)
                    ->by('ip:'.$request->ip())
                    ->response(fn (Request $request, array $headers) => $this->rateLimitResponse(
                        'Too many OTP requests. Please wait before requesting another code.',
                        $headers
                    )),
            ];
        });
    }

    private function rateLimitKey(Request $request): string
    {
        return $request->user()
            ? 'user:'.$request->user()->id
            : 'ip:'.$request->ip();
    }

    private function rateLimitResponse(string $message, array $headers)
    {
        return response()->json([
            'message' => $message,
        ], 429, $headers);
    }
}

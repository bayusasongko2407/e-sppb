<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Attempt login using email or nik.
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function attemptLogin(string $login, string $password, bool $remember = false): User
    {
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'nik';

        $user = User::where($field, $login)->first();

        if ($user) {
            // Check if locked
            if ($user->locked_until && $user->locked_until->isFuture()) {
                throw ValidationException::withMessages([
                    'email' => __('Akun Anda terkunci. Silakan coba lagi setelah '.$user->locked_until->diffForHumans().'.'),
                ]);
            }
            // If lock expired, reset
            if ($user->locked_until && $user->locked_until->isPast()) {
                $user->failed_login_attempts = 0;
                $user->locked_until = null;
                $user->save();
            }
        }

        if (! $user || ! Hash::check($password, $user->password)) {
            $this->incrementFailedAttempts($user);
            throw ValidationException::withMessages([
                'email' => __('Kredensial yang diberikan tidak cocok dengan catatan kami.'),
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => __('Akun Anda tidak aktif. Silakan hubungi administrator.'),
            ]);
        }

        Auth::login($user, $remember);
        if (request()->hasSession()) {
            request()->session()->regenerate();
        }

        $user->last_login_at = now();
        $user->failed_login_attempts = 0;
        $user->locked_until = null;
        $user->save();

        return $user;
    }

    /**
     * Increment failed login attempts atomically.
     */
    private function incrementFailedAttempts(?User $user): void
    {
        if ($user) {
            $user->increment('failed_login_attempts');
            $user->refresh();

            // Threshold: 5 attempts
            if ($user->failed_login_attempts >= 5) {
                $user->locked_until = now()->addMinutes(15);
                $user->save();
                Log::warning("User ID: {$user->id} locked due to too many failed login attempts.");
            } else {
                Log::warning("Failed login attempt for user ID: {$user->id}. Attempt: {$user->failed_login_attempts}");
            }
        } else {
            Log::warning('Failed login attempt for unknown user.');
        }
    }
}

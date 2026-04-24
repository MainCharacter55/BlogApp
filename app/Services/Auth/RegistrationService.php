<?php
// app/Services/Auth/RegistrationService.php

namespace App\Services\Auth;

use App\Models\PendingRegistration;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegistrationService
{
    /**
     * 会員登録用トークンを発行してメール送信する。
     */
    public function issueToken(string $email): PendingRegistration
    {
        if (User::query()->where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => [__('auth.email_already_registered')],
            ]);
        }

        $plainToken = Str::random(32);
        $pendingRegistration = PendingRegistration::query()->updateOrCreate(
            ['email' => $email],
            [
                'token_hash' => hash('sha256', $plainToken),
                'expires_at' => now()->addMinutes(30),
                'verified_at' => null,
            ]
        );

        Mail::raw(
            __('auth.verification_email_body', [
                'token' => $plainToken,
                'expires_at' => $pendingRegistration->expires_at->format('Y-m-d H:i:s'),
            ]),
            function ($message) use ($email): void {
                $message->to($email)->subject(__('auth.verification_email_subject'));
            }
        );

        return $pendingRegistration;
    }

    /**
     * トークンを検証して会員登録を完了する。
     */
    public function completeRegistration(array $validated): User
    {
        return DB::transaction(function () use ($validated): User {
            $pendingRegistration = PendingRegistration::query()
                ->where('email', $validated['email'])
                ->lockForUpdate()
                ->first();

            if (! $pendingRegistration) {
                throw ValidationException::withMessages([
                    'email' => [__('auth.pending_email_not_found')],
                ]);
            }

            if ($pendingRegistration->expires_at->isPast()) {
                throw ValidationException::withMessages([
                    'token' => [__('auth.registration_token_expired')],
                ]);
            }

            if (! hash_equals($pendingRegistration->token_hash, hash('sha256', $validated['token']))) {
                throw ValidationException::withMessages([
                    'token' => [__('auth.registration_token_invalid')],
                ]);
            }

            try {
                $user = User::query()->create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'email_verified_at' => now(),
                ]);
            } catch (QueryException $exception) {
                if ((string) $exception->getCode() === '23000') {
                    throw ValidationException::withMessages([
                        'email' => [__('auth.email_already_registered')],
                    ]);
                }

                throw $exception;
            }

            $pendingRegistration->delete();

            return $user;
        });
    }
}

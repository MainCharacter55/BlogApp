<?php
// app/Http/Controllers/Api/AuthController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RequestRegistrationTokenRequest;
use App\Http\Requests\Auth\VerifyRegistrationRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Auth\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * 会員登録用の認証トークンを発行する。
     */
    public function requestRegistrationToken(
        RequestRegistrationTokenRequest $request,
        RegistrationService $registrationService
    ): JsonResponse {
        $pendingRegistration = $registrationService->issueToken($request->validated()['email']);

        return response()->json([
            'message' => __('auth.registration_token_sent'),
            'data' => [
                'email' => $pendingRegistration->email,
                'expires_at' => $pendingRegistration->expires_at,
            ],
        ], 202);
    }

    /**
     * 認証トークンを検証して会員登録を完了する。
     */
    public function verifyRegistration(
        VerifyRegistrationRequest $request,
        RegistrationService $registrationService
    ): JsonResponse {
        $user = $registrationService->completeRegistration($request->validated());
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => __('auth.registration_completed'),
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 201);
    }

    /**
     * メールアドレスとパスワードでログインする。
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.invalid_login_credentials')],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => __('auth.login_success'),
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * 現在のログインユーザー情報を返す。
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => [
                'user' => new UserResource($request->user()),
            ],
        ]);
    }

    /**
     * 現在のアクセストークンを無効化する。
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => __('auth.logout_success'),
        ]);
    }
}

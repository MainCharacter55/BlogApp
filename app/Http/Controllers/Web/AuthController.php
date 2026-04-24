<?php
// app/Http/Controllers/Web/AuthController.php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Web\Auth\CompleteRegistrationTokenRequest;
use App\Http\Requests\Web\Auth\StartRegistrationRequest;
use App\Services\Auth\RegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * ログイン画面を表示する。
     */
    public function createLogin(): View
    {
        return view('auth.login');
    }

    /**
     * 登録画面を表示する。
     */
    public function createRegister(): View
    {
        return view('auth.register');
    }

    /**
     * トークン確認画面を表示する。
     */
    public function createRegisterVerify(Request $request): RedirectResponse|View
    {
        $registrationData = $request->session()->get('web_registration');

        if (! $registrationData) {
            return redirect()->route('register')->withErrors([
                'email' => __('auth.registration_data_not_found'),
            ]);
        }

        return view('auth.register-verify', [
            'email' => $registrationData['email'],
        ]);
    }

    /**
     * セッションログインを行う。
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $credentials = [
            'email' => $validated['email'],
            'password' => $validated['password'],
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => [__('auth.invalid_login_credentials')],
            ]);
        }

        $request->session()->regenerate();

        return redirect()->route('home')->with('status', __('auth.login_success'));
    }

    /**
     * 会員登録用の認証トークンを送信する。
     */
    public function requestRegistrationToken(
        StartRegistrationRequest $request,
        RegistrationService $registrationService
    ): RedirectResponse {
        $validated = $request->validated();

        $registrationService->issueToken($validated['email']);
        $request->session()->put('web_registration', [
            'email' => $validated['email'],
            'name' => $validated['name'],
            'password' => $validated['password'],
        ]);

        return redirect()->route('register.verify.form')->with('status', __('auth.registration_token_sent'));
    }

    /**
     * 認証トークンを検証して会員登録を完了する。
     */
    public function verifyRegistration(
        CompleteRegistrationTokenRequest $request,
        RegistrationService $registrationService
    ): RedirectResponse {
        $registrationData = $request->session()->get('web_registration');

        if (! $registrationData) {
            return redirect()->route('register')->withErrors([
                'email' => __('auth.registration_data_not_found'),
            ]);
        }

        $user = $registrationService->completeRegistration([
            'email' => $registrationData['email'],
            'name' => $registrationData['name'],
            'password' => $registrationData['password'],
            'token' => $request->validated()['token'],
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget('web_registration');

        return redirect()->route('home')->with('status', __('auth.registration_completed'));
    }

    /**
     * ログアウトする。
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', __('auth.logout_success'));
    }
}

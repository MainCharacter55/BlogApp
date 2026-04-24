@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-lg rounded-[2rem] border border-white/10 bg-white/5 p-8 shadow-2xl shadow-cyan-950/20 backdrop-blur">
        <p class="text-sm uppercase tracking-[0.28em] text-cyan-300">Login</p>
        <h1 class="mt-3 text-3xl font-semibold text-white">ログイン</h1>
        <p class="mt-2 text-sm leading-6 text-slate-300">メールアドレスとパスワードでログインしてください。</p>

        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
            @csrf
            <input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
            <div>
                <label class="mb-2 block text-sm text-slate-300" for="email">メールアドレス</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" class="w-full rounded-2xl border border-white/10 bg-slate-950/80 px-4 py-3 text-slate-100 focus:border-cyan-400 focus:outline-none" />
                @error('email')
                    <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm text-slate-300" for="password">パスワード</label>
                <input id="password" name="password" type="password" class="w-full rounded-2xl border border-white/10 bg-slate-950/80 px-4 py-3 text-slate-100 focus:border-cyan-400 focus:outline-none" />
                @error('password')
                    <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-300">
                <input type="checkbox" name="remember" value="1" class="rounded border-white/20 bg-slate-950 text-cyan-400 focus:ring-cyan-400" />
                ログイン状態を保持する
            </label>

            <button class="w-full rounded-full bg-cyan-400 px-5 py-3 font-medium text-slate-950 transition hover:bg-cyan-300">ログイン</button>
        </form>

        <p class="mt-5 text-sm text-slate-400">
            会員登録は <a href="{{ route('register') }}" class="text-cyan-300 underline decoration-cyan-300/40 underline-offset-4">こちら</a>
        </p>
    </div>
@endsection

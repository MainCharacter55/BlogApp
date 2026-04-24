@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-2xl rounded-[2rem] border border-white/10 bg-white/5 p-8 shadow-2xl shadow-cyan-950/20 backdrop-blur">
        <p class="text-sm uppercase tracking-[0.28em] text-cyan-300">Register Step 1 / 2</p>
        <h1 class="mt-3 text-3xl font-semibold text-white">会員情報を入力してトークンを送信</h1>
        <p class="mt-2 text-sm leading-6 text-slate-300">
            メールアドレス・名前・パスワード（確認用含む）を先に入力し、Mailpit に送信される認証トークンを受け取ってください。
        </p>

        <form method="POST" action="{{ route('register.request') }}" class="mt-6 space-y-4">
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
                <label class="mb-2 block text-sm text-slate-300" for="name">名前</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" class="w-full rounded-2xl border border-white/10 bg-slate-950/80 px-4 py-3 text-slate-100 focus:border-cyan-400 focus:outline-none" />
                @error('name')
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

            <div>
                <label class="mb-2 block text-sm text-slate-300" for="password_confirmation">パスワード（確認用）</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="w-full rounded-2xl border border-white/10 bg-slate-950/80 px-4 py-3 text-slate-100 focus:border-cyan-400 focus:outline-none" />
            </div>

            <button class="w-full rounded-full bg-cyan-400 px-5 py-3 font-medium text-slate-950 transition hover:bg-cyan-300">トークンを送信</button>
        </form>

        <p class="mt-5 text-sm text-slate-400">
            すでにトークン送信済みの場合は <a href="{{ route('register.verify.form') }}" class="text-cyan-300 underline decoration-cyan-300/40 underline-offset-4">トークン入力画面</a> へ進んでください。
        </p>
        <p class="mt-2 text-sm text-slate-400">
            すでにアカウントをお持ちの場合は <a href="{{ route('login') }}" class="text-cyan-300 underline decoration-cyan-300/40 underline-offset-4">ログイン画面</a> へ。
        </p>
    </div>
@endsection

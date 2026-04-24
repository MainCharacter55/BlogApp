@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-xl rounded-[2rem] border border-white/10 bg-slate-900/80 p-8 shadow-2xl shadow-cyan-950/20">
        <p class="text-sm uppercase tracking-[0.28em] text-cyan-300">Register Step 2 / 2</p>
        <h1 class="mt-3 text-3xl font-semibold text-white">認証トークンを入力</h1>
        <p class="mt-2 text-sm leading-6 text-slate-300">
            <span class="text-slate-100">{{ $email }}</span> 宛に送信されたトークンを入力して登録を完了してください。
        </p>

        <form method="POST" action="{{ route('register.verify') }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <label class="mb-2 block text-sm text-slate-300" for="token">トークン</label>
                <input id="token" name="token" type="text" value="{{ old('token') }}" class="w-full rounded-2xl border border-white/10 bg-slate-950/80 px-4 py-3 text-slate-100 focus:border-cyan-400 focus:outline-none" />
                @error('token')
                    <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                @enderror
            </div>

            <button class="w-full rounded-full bg-white px-5 py-3 font-medium text-slate-950 transition hover:bg-cyan-300">登録を完了</button>
        </form>

        <p class="mt-5 text-sm text-slate-400">
            メールが届いていない場合は <a href="{{ route('register') }}" class="text-cyan-300 underline decoration-cyan-300/40 underline-offset-4">登録情報入力画面</a> に戻って再送してください。
        </p>
        <p class="mt-2 text-sm text-slate-400">
            すでに登録済みの場合は <a href="{{ route('login') }}" class="text-cyan-300 underline decoration-cyan-300/40 underline-offset-4">ログイン画面</a> へ。
        </p>
    </div>
@endsection

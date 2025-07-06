@extends('layouts.app')

@section('title', '会員登録(一般ユーザ)')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/pg01_register.css') }}">
@endsection

@section('content')
<div class="register-wrapper">
    <div class="register-container">
        <h1 class="register-title">会員登録</h1>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-group">
                <div class="label-error-wrapper">
                    <label for="name" class="form-label">名前</label>
                    @error('name')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>
                <input id="name" type="text" name="name" class="form-input" value="{{ old('name') }}">
            </div>

            <div class="form-group">
                <div class="label-error-wrapper">
                    <label for="email" class="form-label">メールアドレス</label>
                    @error('email')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>
                <input id="email" type="email" name="email" class="form-input" value="{{ old('email') }}">
            </div>

            <div class="form-group">
                <div class="label-error-wrapper">
                    <label for="password" class="form-label">パスワード</label>
                    @error('password')
                        @if (!str_contains($message, '一致しません'))
                            <span class="error">{{ $message }}</span>
                        @endif
                    @enderror
                </div>
                <input id="password" type="password" name="password" class="form-input">
            </div>

            <div class="form-group">
                <div class="label-error-wrapper">
                    <label for="password_confirmation" class="form-label">パスワード確認</label>

                    {{-- password_confirmation に直接ついたエラーを表示 --}}
                    @error('password_confirmation')
                        <span class="error">{{ $message }}</span>
                    @enderror

                    {{-- password.confirmed による一致エラーもここで表示（passwordにエラーがついた場合でも拾う） --}}
                    @error('password')
                        @if (str_contains($message, '一致しません'))
                            <span class="error">{{ $message }}</span>
                        @endif
                    @enderror
                </div>
                <input id="password_confirmation" type="password" name="password_confirmation" class="form-input">
            </div>

            <button type="submit" class="register-button">登録する</button>

            <div class="login-link">
                <a href="{{ route('login') }}">ログインはこちら</a>
            </div>
        </form>
    </div>
</div>
@endsection

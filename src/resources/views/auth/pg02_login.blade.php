@extends('layouts.app')

@section('title', 'ログイン')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/pg02_login.css') }}">
@endsection

@section('content')
<div class="login-wrapper">
    <div class="login-container">
        <h2 class="login-title">ログイン</h2>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <div class="label-error-wrapper">
                    <label for="login" class="login-label">メールアドレス</label>
                        @error('login')
                            <div class="error">{{ $message }}</div>
                        @enderror
                </div>
                <input type="text" name="login" id="login" class="login-input" value="{{ old('login') }}">
            </div>

            <div class="form-group">
                <div class="label-error-wrapper">
                    <label for="password" class="login-label">パスワード</label>
                        @error('password')
                            <div class="error">{{ $message }}</div>
                        @enderror
                </div>    
                <input type="password" name="password" id="password" class="login-input">
            </div>

            <button type="submit">ログインする</button>
        </form>

        <a href="{{ route('register') }}" class="register-link">会員登録はこちら</a>
    </div>
</div>
@endsection

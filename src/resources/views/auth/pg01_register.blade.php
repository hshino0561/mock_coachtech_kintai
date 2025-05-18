@extends('layouts.app')

@section('title', '会員登録(一般ユーザ)')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/pg01_register.css') }}">
@endsection

@section('content')
<div class="register-wrapper">
    <div class="register-box">
        <h1 class="register-title">会員登録</h1>
        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-group">
                <label for="name">名前</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}">
                @error('name')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="email">メールアドレス</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}">
                @error('email')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="password">パスワード</label>
                <input id="password" type="password" name="password">
                @error('password')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">パスワード確認</label>
                <input id="password_confirmation" type="password" name="password_confirmation">
                @error('password_confirmation')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-button">
                <button type="submit">登録する</button>
            </div>
        </form>

        <div class="login-link">
            <a href="{{ route('login') }}">ログインはこちら</a>
        </div>
    </div>
</div>
@endsection

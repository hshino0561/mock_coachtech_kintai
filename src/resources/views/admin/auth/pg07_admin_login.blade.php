@extends('layouts.admin_app')

@section('title', '管理者ログイン')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/auth/pg07_admin_login.css') }}">
@endsection

@section('content')
<div class="login-wrapper">
    <h2 class="login-title">管理者ログイン</h2>

    <form method="POST" action="{{ route('admin.login') }}" class="login-form">
        @csrf

        <div class="form-group">
            <div class="label-error-wrapper">
                <label for="login">メールアドレス</label>
                @error('login')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>
            <input type="text" name="login" id="login" class="login-input" value="{{ old('login') }}">
        </div>

        <div class="form-group">
            <div class="label-error-wrapper">
                <label for="password">パスワード</label>
                @error('password')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>
            <input type="password" name="password" id="password" class="login-input">
        </div>

        <div class="form-actions">
            <button type="submit">管理者ログインする</button>
        </div>
    </form>
</div>
@endsection

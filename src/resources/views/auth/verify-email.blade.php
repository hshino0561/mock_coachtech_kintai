@extends('layouts.app')

@section('title', 'メール認証') {{-- タイトルは専用セクションに分離 --}}

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify-email.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="message">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。
    </div>

    {{-- 開発用にMailhogへのリンクを表示（本番では非表示推奨） --}}
    <a href="http://localhost:8025/" class="button" target="_blank" rel="noopener noreferrer">認証はこちらから</a><br>

    {{-- 再送信は POST /email/verification-notification --}}
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="resend-link">認証メールを再送する</button>
    </form>
    @if (session('status') == 'verification-link-sent')
        <p class="success-message">
            新しい認証リンクを送信しました。
        </p>
    @endif
</div>
@endsection

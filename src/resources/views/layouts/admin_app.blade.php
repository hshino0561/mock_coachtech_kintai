<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', '勤怠管理アプリ')</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/admin_common.css') }}">
    @yield('css')
</head>
<body>
    <header class="app-header">
        <div class="logo-area">
            <img src="{{ asset('storage/img/logo.png') }}" alt="COACHTECHロゴ" class="logo">
        </div>

    @php
        use Illuminate\Support\Facades\Auth;
    @endphp

    @if(Auth::guard('admin')->check())
    <nav class="nav-bar">
        <ul>
            <li><a href="/admin/attendance/list">勤怠一覧</a></li>
            <li><a href="/admin/staff/list">スタッフ一覧</a></li>
            <li><a href="/stamp_correction_request/list">申請一覧</a></li>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="logout-button">ログアウト</button>
            </form>
        </ul>
    </nav>
    @endauth
</header>

    <main>
        @yield('content')
    </main>

    @yield('js')
</body>
</html>

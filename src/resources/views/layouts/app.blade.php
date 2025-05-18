<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', '勤怠管理アプリ')</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet">
    @yield('css')
</head>

<body>
    <header class="app-header">
        <div class="logo-container">
            <img src="{{ asset('storage/img/logo.png') }}" alt="COACHTECHロゴ" class="logo">
        </div>

        @auth
        @php
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $work_status = $user->work_status ?? 'before_work';
            $isAttendancePage = request()->is('attendance');
        @endphp

        <nav class="nav-bar">
            <ul>
                {{-- 条件：URLが /attendance かつ 勤務終了済み --}}
                @if ($isAttendancePage && $work_status === 'after_work')
                    <li><a href="{{ url('/attendance/list') }}">今月の出勤一覧</a></li>
                    <li><a href="{{ url('/stamp_correction_request/list') }}">申請一覧</a></li>

                {{-- 条件：他のページ（URLが /attendance 以外） --}}
                @else
                    <li><a href="{{ url('/attendance') }}">勤怠</a></li>
                    <li><a href="{{ url('/attendance/list') }}">勤怠一覧</a></li>
                    <li><a href="{{ url('/stamp_correction_request/list') }}">申請</a></li>
                @endif

                <li><a href="{{ url('/logout') }}">ログアウト</a></li>
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
@extends('layouts.app')

@section('title', '勤怠登録')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/pg03_attendance.css') }}">
@endsection

@section('content')
<div class="attendance-container">
    <p class="status-label">
        @if ($work_status === 'before_work')
            勤務外
        @elseif ($work_status === 'on_work')
            出勤中
        @elseif ($work_status === 'on_break')
            休憩中
        @elseif ($work_status === 'after_work')
            退勤済
        @endif
    </p>

    <p class="date" id="main-date">{{ \Carbon\Carbon::now()->locale('ja')->isoFormat('YYYY年M月D日(ddd)') }}</p>
    <p class="time" id="main-time">{{ \Carbon\Carbon::now()->format('H:i') }}</p>

    @if ($work_status === 'before_work') 
        <form action="{{ route('attendance.start') }}" method="POST"> 
            @csrf 
            <button type="submit" class="btn-start">出勤</button> 
        </form> 

    @elseif ($work_status === 'on_work') 
        <div class="btn-group"> 
            <form action="{{ route('attendance.end') }}" method="POST"> 
                @csrf 
                <button type="submit" class="btn-end">退勤</button> 
            </form> 
            <form action="{{ route('attendance.break.start') }}" method="POST"> 
                @csrf 
                <button type="submit" class="btn-break">休憩入</button> 
            </form> 
        </div> 

    @elseif ($work_status === 'on_break') 
        <form action="{{ route('attendance.break.end') }}" method="POST">
            @csrf
            <button type="submit" class="btn-break-return">休憩戻</button>
        </form>
    @endif
    @if (session('message'))
        <p class="flash-message">{{ session('message') }}</p>
    @endif
</div>
@endsection

@section('js')
<script>
    function updateTime() {
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');

        // 時刻更新
        const mainTime = document.getElementById('main-time');
        if (mainTime) {
            mainTime.textContent = `${h}:${m}`;
        }

        // 日付更新（YYYY年M月D日(曜)）
        const weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        const year = now.getFullYear();
        const month = now.getMonth() + 1;
        const date = now.getDate();
        const weekday = weekdays[now.getDay()];
        const formattedDate = `${year}年${month}月${date}日(${weekday})`;

        const mainDate = document.getElementById('main-date');
        if (mainDate) {
            mainDate.textContent = formattedDate;
        }
    }

    setInterval(updateTime, 1000);
    updateTime(); // 初回即時反映
</script>
@endsection

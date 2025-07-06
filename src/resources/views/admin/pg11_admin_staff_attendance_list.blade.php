@extends('layouts.admin_app')

@section('title', 'スタッフ別勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/pg11_admin_staff_attendance_list.css') }}">
@endsection

@section('content')
<div class="staff-attendance-page">
    <div class="title-area">
        <h2 class="page-title">{{ str_replace([' ', '　'], '', $staff->name) }}さんの勤怠</h2>
        <div class="title-line"></div>
    </div>

    <div class="calendar-nav">
        <a href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'month' => \Carbon\Carbon::parse($month)->subMonth()->format('Y-m')]) }}" class="month-nav">
            <img src="{{ asset('storage/img/left_arrow.png') }}" alt="前月" class="arrow-icon">
            前月
        </a>

        <div class="month-center">
            <img src="{{ asset('storage/img/calendar_icon.png') }}" alt="カレンダー" width="20" height="20">
            <span class="current-month">{{ \Carbon\Carbon::parse($month)->format('Y/m') }}</span>
        </div>

        <a href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'month' => \Carbon\Carbon::parse($month)->addMonth()->format('Y-m')]) }}" class="month-nav">
            翌月
            <img src="{{ asset('storage/img/right_arrow.png') }}" alt="翌月" class="arrow-icon">
        </a>
    </div>

    <div class="attendance-table">
        <div class="table-header">
            <div class="col-date">日付</div>
            <div class="col-start">出勤</div>
            <div class="col-end">退勤</div>
            <div class="col-break">休憩</div>
            <div class="col-total">合計</div>
            <div class="col-detail">詳細</div>
        </div>

        @foreach ($attendances as $attendance)
            <div class="table-row">
                <div class="col-date">
                    {{ $attendance->date ? \Carbon\Carbon::parse($attendance->date)->locale('ja')->isoFormat('MM/DD(ddd)') : '-' }}
                </div>
                <div class="col-start">
                    {{ $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '-' }}
                </div>
                <div class="col-end">
                    {{ $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '-' }}
                </div>

                <div class="col-break">
                    {{ $attendance->break_duration_formatted ?? '-' }}
                </div>

                <div class="col-total">
                    {{ $attendance->work_duration_formatted ?? '-' }}
                </div>

                <div class="col-detail">
                    <a href="{{ route('admin.attendance.detail', $attendance->id) }}" class="detail-link">詳細</a>
                </div>
            </div>
        @endforeach
    </div>

    <div class="csv-button-area">
        <form action="{{ route('admin.attendance.staff.export', ['id' => $staff->id]) }}" method="GET">
            <input type="hidden" name="month" value="{{ $month }}">
            <button type="submit" class="csv-button">CSV出力</button>
        </form>
    </div>
</div>
@endsection

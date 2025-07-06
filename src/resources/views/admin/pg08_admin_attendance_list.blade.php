@extends('layouts.admin_app')

@section('title', '勤怠一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/pg08_admin_attendance_list.css') }}">
@endsection

@section('content')
<div class="attendance-list-page">
    <h2 class="page-title">
        {{ $currentDate->format('Y年n月j日') }}の勤怠
    </h2>

    <div class="calendar-nav">
        {{-- 前日 --}}
        <a href="{{ route('admin.attendance.list', ['date' => $currentDate->copy()->subDay()->format('Y-m-d')]) }}" class="month-nav">
            <img src="{{ asset('storage/img/left_arrow.png') }}" alt="前日" class="arrow-icon">
            <span>前日</span>
        </a>

        {{-- 現在の日付 --}}
        <div class="current-month">
            <img src="{{ asset('storage/img/calendar_icon.png') }}" alt="カレンダー" width="20" height="20">
            {{ $currentDate->format('Y/m/d') }}
        </div>

        {{-- 翌日 --}}
        <a href="{{ route('admin.attendance.list', ['date' => $currentDate->copy()->addDay()->format('Y-m-d')]) }}" class="month-nav">
            <span>翌日</span>
            <img src="{{ asset('storage/img/right_arrow.png') }}" alt="翌日" class="arrow-icon">
        </a>
    </div>

    <div class="table-wrapper">
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendances as $attendance)
                    <tr>
                        <td>{{ $attendance->user->name }}</td>
                        <td>{{ $attendance->start_time ? $attendance->start_time->format('H:i') : '-' }}</td>
                        <td>{{ $attendance->end_time ? $attendance->end_time->format('H:i') : '-' }}</td>
                        <td>{{ $attendance->break_duration_formatted ?? '-' }}</td>
                        <td>{{ $attendance->work_duration_formatted ?? '-' }}</td>
                        <td>
                            <a href="{{ route('admin.attendance.detail', ['id' => $attendance->id]) }}" class="detail-link">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

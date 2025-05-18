@extends('layouts.app')

@section('title', '勤怠一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/pg04_attendance_list.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet">
@endsection

@section('content')
    <div class="attendance-list-page">
        {{-- タイトル --}}
        <h2 class="page-title">勤怠一覧</h2>

        @php
            $prevMonth = $currentDate->copy()->subMonth()->format('Y-m');
            $nextMonth = $currentDate->copy()->addMonth()->format('Y-m');
        @endphp

        {{-- カレンダー ナビゲーション --}}
        <div class="calendar-nav">
            <a href="{{ route('attendance.list', ['month' => $prevMonth]) }}" class="month-nav">
                <img src="{{ asset('storage/img/left_arrow.png') }}" alt="前月" class="arrow-icon">
                前月
            </a>

            <span class="current-month">
                <img src="{{ asset('storage/img/calendar_icon.png') }}" alt="カレンダー" class="calendar-icon">
                {{ $currentDate->format('Y/m') }}
            </span>

            <a href="{{ route('attendance.list', ['month' => $nextMonth]) }}" class="month-nav">
                翌月
                <img src="{{ asset('storage/img/right_arrow.png') }}" alt="翌月" class="arrow-icon">
            </a>
        </div>

        {{-- 勤怠テーブル --}}
        <div class="table-wrapper">
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>日付</th>
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
                            <td>{{ $attendance->date ? \Carbon\Carbon::parse($attendance->date)->locale('ja')->isoFormat('MM/DD(ddd)') : '-' }}
                            </td>
                            <td>{{ $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '-' }}
                            </td>
                            <td>{{ $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '-' }}
                            </td>
                            <td>{{ $attendance->break_duration ?? '-' }}</td>
                            <td>{{ $attendance->work_duration ?? '-' }}</td>
                            <td><a href="{{ route('attendance.detail', $attendance->id) }}" class="detail-link">詳細</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

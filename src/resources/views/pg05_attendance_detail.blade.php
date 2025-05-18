@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/pg05_attendance_detail.css') }}">
@endsection

@section('content')
<form action="{{ route('stamp_correction_request.store', ['attendance' => $attendance->id]) }}" method="POST">
    @csrf

    <!-- hiddenフィールドを追加 -->
    <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">

    <div class="attendance-detail-wrapper">
        <div class="detail-title-container">
            <h2 class="detail-title">勤怠詳細</h2>
        </div>

        <div class="detail-card">
            <div class="detail-row">
                <span class="label">名前</span>
                <span class="value no-border">{{ $attendance->user->name }}</span>
            </div>

            <div class="detail-row">
                <span class="label">日付</span>
                <span class="value no-border">{{ $attendance->date->format('Y年') }}</span>
                <span class="value no-border">{{ $attendance->date->format('n月j日') }}</span>
            </div>

            <div class="detail-row">
                <span class="label">出勤・退勤</span>
                <input type="time" name="start_time" class="value"
                       value="{{ old('start_time', optional($attendance->start_time)->format('H:i')) }}">
                <input type="time" name="end_time" class="value"
                       value="{{ old('end_time', optional($attendance->end_time)->format('H:i')) }}">
            </div>

            <div class="detail-row">
                <span class="label">休憩</span>
                <input type="time" name="break_start" class="value"
                       value="{{ old('break_start', optional($attendance->break_start)->format('H:i')) }}">
                <input type="time" name="break_end" class="value"
                       value="{{ old('break_end', optional($attendance->break_end)->format('H:i')) }}">
            </div>

            <div class="detail-row note-row">
                <span class="label">備考</span>
                <textarea name="memo" class="note-box">{{ old('memo') }}</textarea>
            </div>
        </div>

        <div class="edit-button-container">
            <button type="submit" class="btn-edit">修正</button>
        </div>
    </div>
</form>
@endsection

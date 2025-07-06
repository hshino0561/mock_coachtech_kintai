@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/pg05_attendance_detail.css') }}">
@endsection

@section('content')
<form action="{{ route('stamp_correction_request.store', ['attendance' => $attendance->id]) }}" method="POST">
    @csrf

    <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">

    <div class="attendance-detail-wrapper">
        <div class="detail-title-container">
            <h2 class="detail-title">勤怠詳細</h2>
            @if ($errors->any())
                <div class="global-errors">
                    @if ($errors->has('start_time') || $errors->has('end_time'))
                        <div class="error-msg">出勤時間もしくは退勤時間が不適切な値です</div>
                    @endif

                    @php
                        $hasBreakError = collect($errors->keys())->contains(function ($key) {
                            return preg_match('/^breaks\.\d+\.(start|end)$/', $key);
                        });
                    @endphp

                    @if ($hasBreakError)
                        <div class="error-msg">休憩時間が勤務時間外です</div>
                    @endif

                    @if ($errors->has('memo'))
                        <div class="error-msg">備考を記入してください</div>
                    @endif
                </div>
            @endif
        </div>

        <div class="detail-card">
            {{-- 名前 --}}
            <div class="detail-row">
                <span class="label">名前</span>
                <span class="value no-border name-text">{{ $attendance->user->name ?? $user->name }}</span>
            </div>

            {{-- 日付 --}}
            <div class="detail-row date">
                <span class="label">日付</span>
                @php
                    $date = $from_request ? $attendance->attendance_date : $attendance->date;
                @endphp
                <span class="text year-cell">{{ optional($date)->format('Y年') }}</span>
                <span></span>
                <span class="text">{{ optional($date)->format('n月j日') }}</span>
            </div>

            {{-- 出勤・退勤 --}}
            <div class="detail-row">
                <span class="label">出勤・退勤</span>

                @if ($readonly)
                    <span class="value no-border">{{ optional($attendance->start_time)->format('H:i') ?? '－' }}</span>
                    <span class="separator">～</span>
                    <span class="value no-border">{{ optional($attendance->end_time)->format('H:i') ?? '－' }}</span>
                @else
                    <div class="input-group">
                        <input type="time" name="start_time" class="value"
                            value="{{ old('start_time', optional($attendance->start_time)->format('H:i')) }}">
                    </div>
                    <span class="separator">～</span>
                    <div class="input-group">
                        <input type="time" name="end_time" class="value"
                            value="{{ old('end_time', optional($attendance->end_time)->format('H:i')) }}">
                    </div>
                @endif
            </div>

            {{-- 休憩 --}}
            @foreach ($breaks as $index => $break)
                @php
                    $start = $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '';
                    $end   = $break->break_end   ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '';
                    $label = $loop->first ? '休憩' : '休憩' . ($index + 1);
                @endphp

                <div class="detail-row">
                    <span class="label">{{ $label }}</span>

                    @if ($readonly)
                        <span class="value no-border">{{ $start }}</span>

                        @if ($start || $end)
                            <span class="separator">～</span>
                        @endif

                        <span class="value no-border">{{ $end }}</span>
                    @else
                        <div class="input-group">
                            <input type="time" name="breaks[{{ $index }}][start]" class="value"
                                value="{{ old("breaks.$index.start", $start) }}">
                        </div>
                        <span class="separator">～</span>
                        <div class="input-group">
                            <input type="time" name="breaks[{{ $index }}][end]" class="value"
                                value="{{ old("breaks.$index.end", $end) }}">
                        </div>
                    @endif
                </div>
            @endforeach

            {{-- 備考 --}}
            <div class="detail-row memo-row">
                <span class="label">備考</span>
                @if ($readonly)
                    <div class="value no-border memo-text">{{ $attendance->memo ?? '' }}</div>
                @else
                    <div class="input-group">
                        <textarea name="memo" class="memo-box">{{ old('memo', $attendance->memo) }}</textarea>
                    </div>
                @endif
            </div>
        </div>

        {{-- 修正 or 承認待ちメッセージ --}}
        @if (! $readonly)
            <div class="edit-button-container">
                <button type="submit" class="btn-edit">修正</button>
            </div>
        @else
            <div>
                <p class="readonly-memo">※承認待ちのため修正はできません。</p>
            </div>
        @endif
    </div>
</form>
@endsection

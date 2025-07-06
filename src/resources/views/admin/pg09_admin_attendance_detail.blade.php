@extends('layouts.admin_app')

@section('title', '勤怠詳細（管理者）')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/pg09_admin_attendance_detail.css') }}">
@endsection

@section('content')
<form action="{{ route('admin.attendance.update', ['attendance' => $attendance->id]) }}" method="POST">
    @csrf
    @method('PATCH')

    <div class="attendance-detail-wrapper">
        <div class="detail-title-container">
            <h2 class="detail-title">勤怠詳細</h2>

            @if ($errors->any())
                <div class="global-errors">
                    @if ($errors->has('start_time') || $errors->has('end_time'))
                        <div class="error-msg">出勤時間もしくは退勤時間が不適切な値です。</div>
                    @endif

                    @php
                        $hasBreakError = collect($errors->keys())->contains(function ($key) {
                            return preg_match('/^breaks\.\d+\.(start|end)$/', $key);
                        });
                    @endphp

                    @if ($hasBreakError)
                        <div class="error-msg">休憩時間が勤務時間外です。</div>
                    @endif

                    @if ($errors->has('memo'))
                        <div class="error-msg">備考を記入してください。</div>
                    @endif
                </div>
            @endif
        </div>

        <div class="detail-card">
            {{-- 名前 --}}
            <div class="detail-row">
                <span class="label">名前</span>
                <span class="value no-border name-text">
                    {{ str_replace([' ', '　'], '', $attendance->user->name) }}
                </span>
            </div>

            {{-- 日付 --}}
            <div class="detail-row date">
                <span class="label">日付</span>
                <span class="text year-cell">{{ optional($attendance->date)->format('Y年') }}</span>
                <span></span>
                <span class="text">{{ optional($attendance->date)->format('n月j日') }}</span>
            </div>

            {{-- 出勤・退勤 --}}
            <div class="detail-row">
                <span class="label">出勤・退勤</span>
                <input type="time" name="start_time" class="value"
                    value="{{ old('start_time', optional($attendance->start_time)->format('H:i')) }}">
                <span class="separator">～</span>
                <input type="time" name="end_time" class="value"
                    value="{{ old('end_time', optional($attendance->end_time)->format('H:i')) }}">
            </div>

            {{-- 休憩 --}}
            @foreach ($breaks as $index => $break)
                <div class="detail-row">
                    <span class="label">{{ $loop->first ? '休憩' : '休憩' . ($index + 1) }}</span>
                    <input type="time" name="breaks[{{ $index }}][start]" class="value"
                        value="{{ old("breaks.$index.start", optional($break->break_start)->format('H:i')) }}">
                    <span class="separator">～</span>
                    <input type="time" name="breaks[{{ $index }}][end]" class="value"
                        value="{{ old("breaks.$index.end", optional($break->break_end)->format('H:i')) }}">
                </div>
            @endforeach

            {{-- 休憩追加行 --}}
            @php $nextIndex = $breaks->count(); @endphp
            <div class="detail-row">
                <span class="label">休憩{{ $nextIndex + 1 }}</span>
                <input type="time" name="breaks[{{ $nextIndex }}][start]" class="value"
                    value="{{ old("breaks.$nextIndex.start") }}">
                <span class="separator">～</span>
                <input type="time" name="breaks[{{ $nextIndex }}][end]" class="value"
                    value="{{ old("breaks.$nextIndex.end") }}">
            </div>

            {{-- 備考 --}}
            <div class="detail-row memo-row">
                <span class="label">備考</span>
                <textarea name="memo" class="memo-box">{{ old('memo', $attendance->memo) }}</textarea>
            </div>
        </div>

        {{-- ボタン表示エリア --}}
        <div class="edit-button-container">
            <button type="submit" class="btn-edit">修正</button>
        </div>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const timeInputs = document.querySelectorAll('input[type="time"]');

        timeInputs.forEach(input => {
            if (!input.value) {
                input.classList.add('empty-time');
                input.addEventListener('focus', function () {
                    this.classList.remove('empty-time');
                });
            }
        });
    });
</script>
@endsection
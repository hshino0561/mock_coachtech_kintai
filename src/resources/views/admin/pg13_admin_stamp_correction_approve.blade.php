@extends('layouts.admin_app')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/pg13_admin_stamp_correction_approve.css') }}">
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.stamp_correction_request.approve', ['attendance_correct_request' => $stampRequest->id]) }}">
        @csrf
        @method('PATCH')

        <div class="attendance-detail-wrapper">
            <div class="detail-title-container">
                <h2 class="detail-title">勤怠詳細</h2>
            </div>

            <div class="detail-card">
                {{-- 名前 --}}
                <div class="detail-row">
                    <span class="label">名前</span>
                    <span class="value no-border name-text">{{ $attendance->user->name }}</span>
                </div>

                {{-- 日付 --}}
                <div class="detail-row date">
                    <span class="label">日付</span>
                    <span class="value no-border year-cell">{{ optional($date)->format('Y年') }}</span>
                    <span class="value no-border">{{ optional($date)->format('n月j日') }}</span>
                </div>

                {{-- 出勤・退勤 --}}
                <div class="detail-row">
                    <span class="label">出勤・退勤</span>
                    <span class="value no-border">{{ $stampRequest->start_time ? \Carbon\Carbon::parse($stampRequest->start_time)->format('H:i') : '-' }}</span>
                    <span class="separator">～</span>
                    <span class="value no-border">{{ $stampRequest->end_time ? \Carbon\Carbon::parse($stampRequest->end_time)->format('H:i') : '-' }}</span>

                    {{-- hidden --}}
                    <input type="hidden" name="start_time" value="{{ \Carbon\Carbon::parse($stampRequest->start_time)->format('H:i') }}">
                    <input type="hidden" name="end_time" value="{{ \Carbon\Carbon::parse($stampRequest->end_time)->format('H:i') }}">
                </div>

                {{-- 休憩 --}}
                @foreach ($breaks as $index => $break)
                    @php
                        $start = $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '';
                        $end   = $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '';
                    @endphp
                    <div class="detail-row">
                        <span class="label">{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</span>
                        <span class="value no-border">{{ $start }}</span>
                        @if ($start || $end)
                            <span class="separator">～</span>
                        @else
                            <span class="separator">&nbsp;</span>
                        @endif
                        <span class="value no-border">{{ $end }}</span>

                        <input type="hidden" name="breaks[{{ $index }}][start]" value="{{ $start }}">
                        <input type="hidden" name="breaks[{{ $index }}][end]" value="{{ $end }}">
                        @if (!empty($break->id))
                            <input type="hidden" name="breaks[{{ $index }}][id]" value="{{ $break->id }}">
                        @endif
                    </div>
                @endforeach

                {{-- 備考 --}}
                <div class="detail-row memo-row">
                    <span class="label">備考</span>
                    <div class="value no-border memo-text">{{ $stampRequest->memo }}</div>
                    <input type="hidden" name="memo" value="{{ $stampRequest->memo }}">
                </div>
            </div>

            {{-- ボタン --}}
            <div class="edit-button-container">
                @if ($stampRequest->status === 'pending')
                    <button type="submit" name="action" value="approve" class="btn-edit">承認</button>
                @elseif ($stampRequest->status === 'approved')
                    <button type="button" class="btn-edit" disabled>承認済み</button>
                @endif
            </div>
        </div>
    </form>
@endsection

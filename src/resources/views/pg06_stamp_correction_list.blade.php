@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/pg06_stamp_correction_list.css') }}">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
@endsection

@section('content')
<div class="pg06-wrapper">
    <div class="pg06-title-box">
        <div class="pg06-title-line"></div>
        <h1 class="pg06-title">申請一覧</h1>
    </div>

    <div class="pg06-tab-wrapper">
        <div class="pg06-tab-row">
            <a href="{{ route('stamp_correction_request.list', ['status' => 'pending']) }}"
                class="pg06-tab {{ request('status', 'pending') === 'pending' ? 'active' : '' }}">承認待ち</a>
            <a href="{{ route('stamp_correction_request.list', ['status' => 'approved']) }}"
                class="pg06-tab {{ request('status') === 'approved' ? 'active' : '' }}">承認済み</a>
        </div>
        <div class="pg06-tab-border"></div>
    </div>

    <div class="pg06-card">
        <div class="pg06-table-header">
            <div>状態</div>
            <div>名前</div>
            <div>対象日時</div>
            <div>申請理由</div>
            <div>申請日時</div>
            <div>詳細</div>
        </div>

        @forelse ($requests as $request)
        <div class="pg06-table-row">
            <div>{{ $request->status_label }}</div>
            <div>{{ str_replace([' ', '　'], '', $request->user->name) }}</div>
            <div>{{ \Carbon\Carbon::parse($request->attendance->date)->format('Y/m/d') }}</div>
            <div>{{ $request->memo }}</div>
            <div>{{ \Carbon\Carbon::parse($request->created_at)->format('Y/m/d') }}</div>
            <div><a href="{{ route('stamp_correction_request.show_detail', $request->id) }}">詳細</a></div>
        </div>
        @empty
        <div class="pg06-table-row">
            <div class="no-request-message">対象はありません。</div>
        </div>
        @endforelse
    </div>
</div>
@endsection
@extends('layouts.admin_app')

@section('title', '申請一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/pg12_admin_stamp_correction_list.css') }}">
@endsection

@section('content')
<div class="admin-request-wrapper">
    <!-- タイトル -->
    <div class="admin-request-title-box">
        <div class="admin-request-title-line"></div>
        <h2 class="admin-request-title">申請一覧</h2>
    </div>

    <!-- カード外のタブ（背景グレーのまま） -->
    <div class="admin-request-tab-wrapper">
        <div class="admin-request-tab-row">
            <a href="{{ route('stamp_correction_request.list', ['status' => 'pending']) }}"
            class="admin-request-tab {{ request('status', 'pending') === 'pending' ? 'active' : '' }}">
                承認待ち
            </a>
            <a href="{{ route('stamp_correction_request.list', ['status' => 'approved']) }}"
            class="admin-request-tab {{ request('status') === 'approved' ? 'active' : '' }}">
                承認済み
            </a>
        </div>
        <div class="admin-request-tab-border"></div>
    </div>

    <!-- 白背景カード：ヘッダー＋データ -->
    <div class="admin-request-card">

        <!-- テーブルヘッダー -->
        <div class="admin-request-table-header">
            <div>状態</div>
            <div>名前</div>
            <div>対象日時</div>
            <div>申請理由</div>
            <div>申請日時</div>
            <div>詳細</div>
        </div>

        <!-- データ行 -->
        @forelse ($requests as $request)
            <div class="admin-request-table-row">
                <div>{{ $request->status_label }}</div>
                <div>{{ str_replace([' ', '　'], '', $request->user->name) }}</div>
                <div>{{ \Carbon\Carbon::parse($request->attendance->date)->format('Y/m/d') }}</div>
                <div>{{ $request->memo }}</div>
                <div>{{ \Carbon\Carbon::parse($request->created_at)->format('Y/m/d') }}</div>
                <div><a href="{{ route('admin.stamp_correction_request.detail', ['attendance_correct_request' => $request->id]) }}">詳細</a></div>
            </div>
        @empty
            <div class="admin-request-table-row">
                <div class="no-request-message" style="grid-column: 1 / -1;">対象はありません</div>
            </div>
        @endforelse
    </div>
</div>
@endsection

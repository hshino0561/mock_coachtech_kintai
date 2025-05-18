@extends('layouts.app')

@section('title', '申請一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/pg06_stamp_correction_list.css') }}">
@endsection

@section('content')
<div class="request-list-container">
    <h2 class="page-title">申請一覧</h2>

    <!-- タブ -->
    <div class="tabs">
        <a href="?status=pending" class="{{ request('status', 'pending') === 'pending' ? 'active' : '' }}">承認待ち</a>
        <a href="?status=approved" class="{{ request('status') === 'approved' ? 'active' : '' }}">承認済み</a>
    </div>

    <hr class="tab-divider">

    <div class="request-table-wrapper">
        <table class="request-table">
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $request)
                <tr>
                    <td>{{ $request->status_label }}</td>
                    <td>{{ $request->user->name }}</td>
                    <td>{{ $request->attendance_date ? $request->attendance_date->format('Y/m/d') : '未登録' }}</td>
                    <td>{{ $request->reason }}</td>
                    <td>{{ $request->created_at->format('Y/m/d') }}</td>
                    <td><a href="{{ route('stamp_correction_request.detail', $request->id) }}">詳細</a></td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">申請はありません。</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

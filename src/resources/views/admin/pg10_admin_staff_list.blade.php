@extends('layouts.admin_app')

@section('title', 'スタッフ一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/pg10_admin_staff_list.css') }}">
@endsection

@section('content')
<div class="staff-page">

    {{-- タイトルのみの専用ラッパー --}}
    <div class="staff-title-wrapper">
        <div class="staff-title-area">
            <div class="title-bar"></div>
            <h2 class="staff-title">スタッフ一覧</h2>
        </div>
    </div>

    <div class="staff-card">
        <div class="staff-header">
            <div class="staff-header-name">名前</div>
            <div class="staff-header-email">メールアドレス</div>
            <div class="staff-header-detail">月次勤怠</div>
        </div>
        @foreach ($staffs as $staff)
            <div class="staff-row">
                <div class="staff-name">{{ $staff->name }}</div>
                <div class="staff-email">{{ $staff->email }}</div>
                <div class="staff-detail">
                    <a href="{{ route('admin.attendance.staff', ['id' => $staff->id]) }}">詳細</a>
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection

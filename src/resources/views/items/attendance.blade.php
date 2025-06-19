@extends('layouts.user-header')

@section('title', '勤怠登録')

@section('content')

<div class="attendance-wrapper">
    <div class="attendance-status-label">
        @if ($working_status === 'off')
        <span class="status-label gray">勤務外</span>
        @elseif ($working_status === 'working')
        <span class="status-label black">出勤中</span>
        @elseif ($working_status === 'break')
        <span class="status-label gray">休憩中</span>
        @elseif ($working_status === 'done')
        <span class="status-label gray">退勤済</span>
        @endif
    </div>

    <div class="attendance-date">{{ $date }}</div>
    <div class="attendance-time">{{ $time }}</div>

    <div class="attendance-buttons">
        @if ($working_status === 'off')
        <form action="{{ route('attendances.store') }}" method="POST">
            @csrf
            <input type="hidden" name="action" value="clock_in">
            <button class="black-btn">出勤</button>
        </form>

        @elseif ($working_status === 'working')
        <form action="{{ route('attendances.store') }}" method="POST">
            @csrf
            <input type="hidden" name="action" value="clock_out">
            <button class="black-btn">退勤</button>
        </form>

        <form action="{{ route('attendances.store') }}" method="POST">
            @csrf
            <input type="hidden" name="action" value="break_in">
            <button class="white-btn">休憩入</button>
        </form>

        @elseif ($working_status === 'break')
        <form action="{{ route('attendances.store') }}" method="POST">
            @csrf
            <input type="hidden" name="action" value="break_out">
            <button class="white-btn">休憩戻</button>
        </form>

        @elseif ($working_status === 'done')
        <p class="thanks-message">お疲れ様でした。</p>
        @endif
    </div>
</div>
@endsection

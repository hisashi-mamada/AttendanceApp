@extends('layouts.user-header')

@section('title', '勤怠一覧')

@section('content')
<div class="attendance-list-wrapper">
    <h2 class="attendance-list-title"><span class="title-bar"></span>勤怠一覧</h2>


    <div class="attendance-month-navigation">
        <a href="#" class="month-nav">
            <img src="{{ asset('images/arrow.png') }}" alt="前月" class="arrow left-arrow"> 前月
        </a>
        <div class="month-center">
            <img src="{{ asset('images/calendar.png') }}" alt="カレンダー" class="calendar-icon">
            <span class="month-current">{{ \Carbon\Carbon::now()->format('Y/m') }}</span>
        </div>
        <a href="#" class="month-nav">
            翌月 <img src="{{ asset('images/arrow.png') }}" alt="翌月" class="arrow right-arrow">
        </a>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th class="th-date">日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $attendance)
            @php
            $breakTotal = $attendance->breakTimes->reduce(function ($carry, $break) {
            if ($break->break_start_time && $break->break_end_time) {
            return $carry + \Carbon\Carbon::parse($break->break_end_time)->diffInMinutes($break->break_start_time);
            }
            return $carry;
            }, 0);
            $workTotal = null;
            if ($attendance->clock_in_time && $attendance->clock_out_time) {
            $workTotal = \Carbon\Carbon::parse($attendance->clock_out_time)->diffInMinutes($attendance->clock_in_time) - $breakTotal;
            }
            @endphp
            <tr>
                <td>{{ \Carbon\Carbon::parse($attendance->date)->isoFormat('MM/DD(ddd)') }}
                </td>
                <td>{{ $attendance->clock_in_time ? \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i') : '-' }}</td>
                <td>{{ $attendance->clock_out_time ? \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i') : '-' }}</td>
                <td>{{ $breakTotal ? floor($breakTotal / 60) . ':' . str_pad($breakTotal % 60, 2, '0', STR_PAD_LEFT) : '-' }}</td>
                <td>{{ $workTotal ? floor($workTotal / 60) . ':' . str_pad($workTotal % 60, 2, '0', STR_PAD_LEFT) : '-' }}</td>
                <td>
                    <a href="{{ url('/attendance/detail/' . $attendance->id) }}">詳細</a>
                </td>

            </tr>
            @empty
            <tr>
                <td colspan="6">データがありません</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

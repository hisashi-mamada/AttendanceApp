@extends('layouts.user-header')

@section('title', '勤怠詳細画面（一般ユーザー）')

@section('content')
<div class="attendance-detail-wrapper">
    <h2 class="attendance-detail-title"><span class="title-bar"></span>勤怠詳細</h2>
    <div class="attendance-detail-box">

        <table class="attendance-detail-table">
            <tr>
                <th>名前</th>
                <td>{{ $attendance->user->name }}</td>
            </tr>

            <td colspan="2">
                <div class="custom-border-line"></div>
            </td>

            <tr>
                <th>日付</th>
                <td class="time-cell">
                    <span>{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}</span>
                    <span>{{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}</span>

                </td>
            </tr>

            <td colspan="2">
                <div class="custom-border-line"></div>
            </td>

            <tr>
                <th>出勤・退勤</th>
                <td class="time-cell">
                    <input type="text" value="{{ $attendance->clock_in_time ? \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i') : '' }}" readonly> 〜
                    <input type="text" value="{{ $attendance->clock_out_time ? \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i') : '' }}" readonly>
                </td>
            </tr>

            <td colspan="2">
                <div class="custom-border-line"></div>
            </td>

            {{-- 休憩（可変対応） --}}
            @foreach ($attendance->breakTimes as $index => $break)
            <tr>
                <th>{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</th>
                <td class="time-cell">
                    <input type="text" value="{{ $break->break_start_time ? \Carbon\Carbon::parse($break->break_start_time)->format('H:i') : '' }}" readonly> 〜
                    <input type="text" value="{{ $break->break_end_time ? \Carbon\Carbon::parse($break->break_end_time)->format('H:i') : '' }}" readonly>
                </td>
            </tr>
            @endforeach

            <td colspan="2">
                <div class="custom-border-line"></div>
            </td>

            <tr>
                <th>備考</th>
                <td>
                    <textarea readonly>{{ $attendance->remarks }}</textarea>
                </td>
            </tr>
        </table>
    </div>

    <div class="button-area">
        <button class="edit-button">修正</button>
    </div>
</div>
@endsection

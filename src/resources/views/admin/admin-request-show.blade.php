@extends('layouts.admin-header')

@section('title', '修正申請承認画面（管理者）')

@section('content')
<div class="attendance-detail-wrapper">
    <h2 class="attendance-detail-title"><span class="title-bar"></span>勤怠詳細</h2>
    <div class="attendance-detail-box">

        <table class="attendance-detail-table">
            <tr>
                <th>名前</th>
                <td>{{ $request->user->name }}</td>
            </tr>

            <td colspan="2">
                <div class="custom-border-line"></div>
            </td>

            <tr>
                <th>日付</th>
                <td class="time-cell">
                    <span>{{ $request->requested_clock_in_time->format('Y年') }}</span>
                    <span>{{ $request->requested_clock_in_time->format('n月j日') }}</span>
                </td>
            </tr>

            <td colspan="2">
                <div class="custom-border-line"></div>
            </td>

            <tr>
                <th>出勤・退勤</th>
                <td class="time-cell">
                    <input type="text" value="{{ \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i') }}" readonly> 〜
                    <input type="text" value="{{ \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i') }}" readonly>
                </td>
            </tr>

            <td colspan="2">
                <div class="custom-border-line"></div>
            </td>

            {{-- 休憩時間表示（DBから取得した値） --}}
            @if ($attendance->breakTimes->isNotEmpty())
            @foreach ($attendance->breakTimes as $index => $break)
            <tr>
                <th>{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</th>
                <td class="time-cell">
                    <input type="text" value="{{ \Carbon\Carbon::parse($break->break_start_time)->format('H:i') }}" readonly> 〜
                    <input type="text" value="{{ \Carbon\Carbon::parse($break->break_end_time)->format('H:i') }}" readonly>
                </td>
            </tr>
            @endforeach
            @endif


            <td colspan="2">
                <div class="custom-border-line"></div>
            </td>

            <tr>
                <th>備考</th>
                <td>
                    <textarea readonly>{{ $request->reason }}</textarea>
                </td>
            </tr>
        </table>
    </div>

    <div class="button-area">
        @if ($request->status === 'pending')
        <form method="POST" action="{{ route('admin.requests.approve', $request->id) }}">
            @csrf
            @method('PATCH')
            <button class="edit-button">承認</button>
        </form>
        @else
        <button class="edit-button" disabled>承認済み</button>
        @endif
    </div>
</div>
@endsection
@extends('layouts.user-header')

@section('title', '勤怠詳細画面（一般ユーザー）')

@section('content')
<div class="attendance-detail-wrapper">
    <h2 class="attendance-detail-title"><span class="title-bar"></span>勤怠詳細</h2>

    <form action="{{ route('attendance.update', ['id' => $attendance->id]) }}" method="POST">
        @csrf
        @method('PATCH')

        <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">

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
                        <input type="text" name="requested_clock_in_time"
                            value="{{ old('requested_clock_in_time', $attendance->clock_in_time ? \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i') : '') }}">
                        〜
                        <input type="text" name="requested_clock_out_time"
                            value="{{ old('requested_clock_out_time', $attendance->clock_out_time ? \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i') : '') }}">

                        @error('requested_clock_in_time')
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                        @error('requested_clock_out_time')
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                <td colspan="2">
                    <div class="custom-border-line"></div>
                </td>

                {{-- 休憩（可変対応＋追加フィールド） --}}
                @foreach ($attendance->breakTimes as $index => $break)
                <tr>
                    <th>{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</th>
                    <td class="time-cell">
                        <input type="text" name="breaks[{{ $index }}][start]"
                            value="{{ old("breaks.$index.start", $break->break_start_time ? \Carbon\Carbon::parse($break->break_start_time)->format('H:i') : '') }}">

                        〜
                        <input type="text" name="breaks[{{ $index }}][end]"
                            value="{{ old("breaks.$index.end", $break->break_end_time ? \Carbon\Carbon::parse($break->break_end_time)->format('H:i') : '') }}">

                        @error("breaks.$index.start")
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                        @error("breaks.$index.end")
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                @endforeach

                {{-- 追加1つ分の空欄フィールド --}}
                <tr>
                    <th>休憩２</th>
                    <td class="time-cell">
                        <input type="text" name="breaks[{{ count($attendance->breakTimes) }}][start]" class="time-cell">
                        〜
                        <input type="text" name="breaks[{{ count($attendance->breakTimes) }}][end]" class="time-cell">
                    </td>
                </tr>

                <td colspan="2">
                    <div class="custom-border-line"></div>
                </td>

                <tr>
                    <th>備考</th>
                    <td>
                        <textarea name="reason">{{ old('reason') }}</textarea>
                        @error('reason')
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
            </table>
        </div>

        @if (optional($attendance->correctionRequest)->status === 'pending')
        <div class="approval-error-message">
            ※承認待ちのため修正はできません。
        </div>
        @else
        <div class="button-area">
            <button type="submit" class="edit-button">修正</button>
        </div>
        @endif
    </form>
</div>
@endsection
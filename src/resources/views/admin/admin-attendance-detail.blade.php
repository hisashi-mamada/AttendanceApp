@extends('layouts.admin-header')

@section('title', '勤怠詳細画面（管理者）')

@section('content')
<div class="attendance-detail-wrapper">
    <h2 class="attendance-detail-title"><span class="title-bar"></span>勤怠詳細</h2>
    <form action="{{ route('admin.attendances.update', ['id' => $attendance->id]) }}" method="POST">
        @csrf
        @method('PATCH')

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
                        <input type="text" name="clock_in_time"
                            value="{{ old('clock_in_time', $attendance->clock_in_time ? \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i') : '') }}">
                        〜
                        <input type="text" name="clock_out_time"
                            value="{{ old('clock_out_time', $attendance->clock_out_time ? \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i') : '') }}">

                        @error('clock_in_time')
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                        @error('clock_out_time')
                        <div class="error-message">{{ $message }}</div>
                        @enderror
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
                    <th>休憩{{ count($attendance->breakTimes) + 1 }}</th>
                    <td class="time-cell">
                        <input type="text" name="breaks[{{ count($attendance->breakTimes) }}][start]">
                        〜
                        <input type="text" name="breaks[{{ count($attendance->breakTimes) }}][end]">
                    </td>
                </tr>

                <td colspan="2">
                    <div class="custom-border-line"></div>
                </td>

                <tr>
                    <th>備考</th>
                    <td>
                        <textarea name="remarks">{{ old('remarks', $attendance->remarks) }}</textarea>

                        @error('remarks')
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
            </table>
        </div>

        <div class="button-area">
            <button type="submit" class="edit-button">修正</button>
        </div>
    </form>
</div>
@endsection
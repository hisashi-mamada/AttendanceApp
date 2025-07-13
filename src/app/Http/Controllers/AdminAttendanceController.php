<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Http\Requests\AttendanceRequest;
use App\Models\BreakTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;



class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', now()->toDateString());

        $attendances = Attendance::with('user')
            ->whereDate('date', $date)
            ->get();

        $previousDate = Carbon::parse($date)->subDay()->toDateString();
        $nextDate = Carbon::parse($date)->addDay()->toDateString();

        return view('admin.admin-attendance-list', compact('date', 'attendances', 'previousDate', 'nextDate'));
    }

    public function show($id)
    {
        $attendance = Attendance::with(['user', 'breakTimes'])->findOrFail($id);

        return view('admin.admin-attendance-detail', compact('attendance'));
    }

    public function userIndex(Request $request, User $user)
    {
        $targetMonth = $request->input('month', now()->format('Y-m'));
        $startDate = Carbon::parse($targetMonth . '-01')->startOfMonth();
        $endDate = Carbon::parse($targetMonth . '-01')->endOfMonth();

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();

        return view('admin.admin_user_attendance', [
            'user' => $user,
            'attendances' => $attendances,
            'month' => $targetMonth,
        ]);
    }

    public function update(AttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $date = $attendance->date; // 2025-07-05 など

        // 勤怠の更新
        $attendance->clock_in_time = Carbon::parse($date . ' ' . $request->input('clock_in_time'));
        $attendance->clock_out_time = Carbon::parse($date . ' ' . $request->input('clock_out_time'));
        $attendance->remarks = $request->input('remarks');
        $attendance->save();

        // 既存の休憩時間を削除
        $attendance->breakTimes()->delete();

        // 新しい休憩時間を保存
        foreach ($request->input('breaks', []) as $break) {
            if (!empty($break['start']) && !empty($break['end'])) {
                $attendance->breakTimes()->create([
                    'break_start_time' => Carbon::parse($date . ' ' . $break['start']),
                    'break_end_time'   => Carbon::parse($date . ' ' . $break['end']),
                ]);
            }
        }

        return redirect()->route('admin.attendances.show', ['id' => $attendance->id])
            ->with('message', '勤怠情報を更新しました。');
    }

    public function exportCsv(Request $request, $userId)
    {
        $month = $request->input('month');
        $startDate = \Carbon\Carbon::parse($month)->startOfMonth();
        $endDate = \Carbon\Carbon::parse($month)->endOfMonth();

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $csvData = [];
        $csvData[] = ['日付', '出勤', '退勤', '休憩時間', '合計'];

        foreach ($attendances as $attendance) {
            $breakMinutes = $attendance->breakTimes->reduce(function ($carry, $break) {
                if ($break->break_start_time && $break->break_end_time) {
                    return $carry + \Carbon\Carbon::parse($break->break_end_time)->diffInMinutes($break->break_start_time);
                }
                return $carry;
            }, 0);

            $workMinutes = null;
            if ($attendance->clock_in_time && $attendance->clock_out_time) {
                $workMinutes = \Carbon\Carbon::parse($attendance->clock_out_time)->diffInMinutes($attendance->clock_in_time) - $breakMinutes;
            }

            $csvData[] = [
                $attendance->date,
                $attendance->clock_in_time ? \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i') : '-',
                $attendance->clock_out_time ? \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i') : '-',
                $breakMinutes ? floor($breakMinutes / 60) . ':' . str_pad($breakMinutes % 60, 2, '0', STR_PAD_LEFT) : '-',
                $workMinutes ? floor($workMinutes / 60) . ':' . str_pad($workMinutes % 60, 2, '0', STR_PAD_LEFT) : '-',
            ];
        }

        $filename = 'attendance_' . $userId . '_' . $month . '.csv';

        // 出力用のバッファを開く
        $handle = fopen('php://temp', 'r+');

        foreach ($csvData as $row) {
            // SJIS-winに変換して出力
            $convertedRow = array_map(fn($value) => mb_convert_encoding($value, 'SJIS-win', 'UTF-8'), $row);
            fputcsv($handle, $convertedRow);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=Shift_JIS',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}

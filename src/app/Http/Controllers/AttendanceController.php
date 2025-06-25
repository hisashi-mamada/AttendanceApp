<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{

    public function create()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $now = Carbon::now();

        // 今日の勤怠を取得
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        $status = 'off';

        if ($attendance) {
            if ($attendance->clock_out_time) {
                $status = 'done'; // 退勤済み
            } elseif ($attendance->clock_in_time) {
                // 勤怠中 → 休憩中チェック
                $latest_break = BreakTime::where('attendance_id', $attendance->id)
                    ->whereNull('break_end_time')
                    ->latest()
                    ->first();

                if ($latest_break) {
                    $status = 'break'; // 休憩中
                } else {
                    $status = 'working'; // 勤務中
                }
            }
        }

        return view('items.attendance', [
            'working_status' => $status,
            'date' => $now->isoFormat('Y年M月D日(ddd)'),
            'time' => $now->format('H:i'),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();
        $now = Carbon::now();

        // 今日の勤怠を取得 or 新規作成
        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            [
                'clock_in_time' => null,
                'clock_out_time' => null,
                'status' => '未設定' // もしくは '出勤前' など任意
            ]
        );

        switch ($request->input('action')) {
            case 'clock_in':
                if (!$attendance->clock_in_time) {
                    $attendance->clock_in_time = $now;
                    $attendance->save();
                }
                break;

            case 'clock_out':
                if (!$attendance->clock_out_time) {
                    $attendance->clock_out_time = $now;
                    $attendance->save();
                }
                break;

            case 'break_in':
                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start_time' => $now,
                ]);
                break;

            case 'break_out':
                $break = BreakTime::where('attendance_id', $attendance->id)
                    ->whereNull('break_end_time')
                    ->latest()
                    ->first();
                if ($break) {
                    $break->break_end_time = $now;
                    $break->save();
                }
                break;
        }

        return redirect()->route('attendances.index');
    }

    public function list()
    {
        $user = Auth::user();
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date')
            ->get();

        return view('items.attendance-list', [
            'attendances' => $attendances,
        ]);
    }

    public function show($id)
    {
        $attendance = Attendance::with(['user', 'breakTimes'])->findOrFail($id);
        return view('items.attendance-detail', compact('attendance'));
    }
}

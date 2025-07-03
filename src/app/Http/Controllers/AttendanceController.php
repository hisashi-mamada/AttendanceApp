<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Http\Requests\AttendanceRequest;
use App\Models\AttendanceCorrectionRequest;
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

        if (!$user) {
            return redirect()->route('login')->withErrors(['login' => 'ログインしてください']);
        }

        $month = request('month');
        $targetMonth = $month ? Carbon::parse($month . '-01') : Carbon::now();

        $startOfMonth = $targetMonth->copy()->startOfMonth();
        $endOfMonth = $targetMonth->copy()->endOfMonth();

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date')
            ->get();

        return view('items.attendance-list', [
            'attendances' => $attendances,
            'month' => $targetMonth->format('Y-m'),
        ]);
    }

    public function show($id)
    {
        $attendance = Attendance::with(['user', 'breakTimes'])->findOrFail($id);
        return view('items.attendance-detail', compact('attendance'));
    }

    public function update(AttendanceRequest $request, $id)
    {
        $attendance = Attendance::with('breakTimes')->findOrFail($id);

        $date = $attendance->date; // 勤務日
        $clockInDateTime = \Carbon\Carbon::parse($date . ' ' . $request->input('clock_in_time'));
        $clockOutDateTime = \Carbon\Carbon::parse($date . ' ' . $request->input('clock_out_time'));

        AttendanceCorrectionRequest::create([
            'user_id' => auth()->id(),
            'attendance_id' => $attendance->id,
            'requested_clock_in_time' => $clockInDateTime,
            'requested_clock_out_time' => $clockOutDateTime,
            'reason' => $request->input('remarks'),
            'status' => 'pending',
            'approved_by' => null,
        ]);
        // 休憩時間の更新（既存レコードを全削除→再登録）
        $attendance->breakTimes()->delete();

        $breaks = $request->input('breaks', []);
        foreach ($breaks as $break) {
            $start = $break['start'] ?? null;
            $end = $break['end'] ?? null;

            if (!empty($start) || !empty($end)) {
                $attendance->breakTimes()->create([
                    'break_start_time' => $start ? Carbon::parse($date . ' ' . $start) : null,
                    'break_end_time'   => $end   ? Carbon::parse($date . ' ' . $end) : null,
                ]);
            }
        }

        return redirect()->route('attendance.detail', ['id' => $attendance->id])
            ->with('message', '修正申請を受け付けました。');
    }
}

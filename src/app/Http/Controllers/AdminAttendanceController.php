<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Http\Requests\AttendanceRequest;
use App\Models\BreakTime;
use Illuminate\Support\Facades\DB;

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

    public function userIndex(User $user)
    {
        $attendances = Attendance::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->get();

        return view('admin.admin_user_attendance', compact('user', 'attendances'));
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
}

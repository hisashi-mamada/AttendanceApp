<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

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
}

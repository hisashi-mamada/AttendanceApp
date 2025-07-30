<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceCorrectionRequest;

class UserRequestController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'pending');
        $user = Auth::user();
        $requests = AttendanceCorrectionRequest::with(['attendance', 'approvalStatus'])
            ->where('user_id', $user->id)
            ->where('status', $tab)
            ->orderByDesc('created_at')
            ->get();

        if (!$user) {
            return redirect()->route('login')->withErrors(['login' => 'ログインしてください']);
        }

        if ($tab === 'pending') {
            $requests = AttendanceCorrectionRequest::with('attendance')
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->orderByDesc('created_at')
                ->get();
        } else {
            $requests = AttendanceCorrectionRequest::with('attendance')
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->orderByDesc('created_at')
                ->get();
        }

        return view('items.request-list', [
            'tab' => $tab,
            'requests' => $requests,
        ]);
    }

    public function show($id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->withErrors(['login' => 'ログインしてください']);
        }

        $request = AttendanceCorrectionRequest::with(['attendance.breakTimes'])
            ->where('user_id', $user->id)
            ->findOrFail($id);

        $attendance = $request->attendance;

        return view('items.attendance-detail', [
            'attendance' => $attendance,
            'request' => $request,
        ]);
    }
}

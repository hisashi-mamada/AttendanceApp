<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceCorrectionRequest;

class AdminRequestController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'pending'); // クエリパラメータがない場合は 'pending' をデフォルトに

        $requests = AttendanceCorrectionRequest::with('user')
            ->when($tab === 'pending', function ($query) {
                return $query->where('status', 'pending');
            })
            ->when($tab === 'approved', function ($query) {
                return $query->where('status', 'approved');
            })
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'name' => $request->user->name ?? '',
                    'target_date' => optional($request->attendance)->date ?? '',
                    'reason' => $request->reason,
                    'request_date' => $request->created_at->format('Y/m/d'),
                ];
            });

        return view('admin.admin-request-list', compact('tab', 'requests'));
    }

    public function show($id)
    {
        $request = AttendanceCorrectionRequest::with(['user', 'attendance.user', 'attendance.breakTimes', 'approver'])->findOrFail($id);
        $attendance = $request->attendance;

        return view('admin.admin-request-show', compact('request', 'attendance'));
    }

    public function approve($id)
    {
        $request = AttendanceCorrectionRequest::findOrFail($id);
        $request->status = 'approved';
        $request->approved_by = auth()->id();
        $request->save();

        return back()->with('status', '承認しました');
    }
}

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


        // 明示的にCarbon変換（もし必要なら）
        //if (!($request->requested_clock_in_time instanceof \Carbon\Carbon)) {
        //$request->requested_clock_in_time = \Carbon\Carbon::parse($request->requested_clock_in_time);
        //}
        //if (!($request->requested_clock_out_time instanceof \Carbon\Carbon)) {
        //$request->requested_clock_out_time = \Carbon\Carbon::parse($request->requested_clock_out_time);
        //}


        $attendance = $request->attendance;

        return view('admin.admin-request-show', compact('request', 'attendance'));
    }


    public function approve($id)
    {
        $request = AttendanceCorrectionRequest::findOrFail($id);
        $request->status = 'approved';
        $request->approved_by = auth()->id();
        $request->save();

        // 勤怠情報の更新は現在他の処理にて対応済み（ここでは未実装）
        // 今後必要に応じて attendance の clock_in_time / clock_out_time を更新することも検討

        return back()->with('status', '承認しました');
    }
}

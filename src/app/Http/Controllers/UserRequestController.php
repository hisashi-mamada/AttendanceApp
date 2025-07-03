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
}

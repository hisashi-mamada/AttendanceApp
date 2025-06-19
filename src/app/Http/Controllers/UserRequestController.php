<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserRequestController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'pending'); // デフォルトは「承認待ち」

        $pendingRequests = []; // DBから取得に置き換えOK
        $approvedRequests = [];

        // 例）本来はEloquentで取得
        if ($tab === 'pending') {
            $data = $pendingRequests;
        } else {
            $data = $approvedRequests;
        }

        return view('items.request-list', [
            'tab' => $tab,
            'requests' => $data,
        ]);
    }
}

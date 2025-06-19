<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // guardの指定が必要であれば 'admin' に変更
        if (Auth::attempt($credentials)) {
            return redirect()->intended('/admin/dashboard'); // 成功時の遷移先
        }

        return back()->withErrors([
            'email' => 'ログイン情報が正しくありません。',
        ])->withInput();
    }
}

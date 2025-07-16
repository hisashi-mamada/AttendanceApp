<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class FortifyLoginResponse implements LoginResponseContract
{
    public function toResponse($request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        // メール未認証なら認証画面へ
        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        // ロールでルート振り分け
        if ($user->role === 'admin') {
            return redirect()->route('admin.attendances.index');
        }

        // 一般ユーザーの場合
        return redirect()->route('attendances.index');
    }
}

<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterViewResponse;

class FortifyRegisterViewResponse implements RegisterViewResponse
{
    public function toResponse($request)
    {
        return view('items.register');
    }
}

<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract

{
    public function toResponse($request)
    {
        // 登録直後は必ずメール認証画面へ
        return redirect()->route('verification.notice');
    }
}

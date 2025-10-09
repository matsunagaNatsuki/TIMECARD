<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;


class AdminMiddleware
{

    public function handle(Request $request, Closure $next)
    {
        $email = $request->input('email');
        if ($email) {
            $role = User::where('email',$email)->value('role');
            if ($role && $role !== 'admin') {
                return back()
                    ->withErrors(['email' => '管理者のみログイン可能です。'])
                    ->withInput($request->only('email'));
            }
        }
        return $next($request);
    }
    
}



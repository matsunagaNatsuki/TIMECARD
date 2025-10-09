<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;


class AttendanceController extends Controller
{
    // 管理者用勤怠一覧画面
    public function adminList (Request $request)
    {
        $dateStr = $request->query('date', now()->toDateString());

        try {
            $date = Carbon::parse($dateStr)->startOfDay();
        } catch (\Throwable $e) {
            $date = now()->startOfDay();
        }

        $prev = $date->copy()->subDay();
        $next = $date->copy()->addDay();

        $users = User::with(['attendances' => function ($q) use ($date) {
                $q->whereDate('clock_in', $date)
                ->with('breaks');
            }
        ])->get();

        return view('admin_list', [
            'date' => $date,
            'prev' => $prev,
            'next' => $next,
            'displayDate1' => $date->format('Y年m月d日'),
            'displayDate2' => $date->format('Y/m/d'),
            'users' => $users
        ]);
    }

    // 一般ユーザー勤怠一覧画面
    public function list (Request $request)
    {
        $yearMonth = $request->input('month', now()->format('Y-m'));
        $startOfMonth = \Carbon\Carbon::parse($yearMonth)->startOfMonth();
        $endOfMonth   = \Carbon\Carbon::parse($yearMonth)->endOfMonth();

        $attendances = Attendance::with('breaks')
            ->where('user_id', Auth::id())
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy(fn($item) => $item->date instanceof \Carbon\Carbon
                ? $item->date->format('Y-m-d')
                : \Carbon\Carbon::parse($item->date)->format('Y-m-d')
            );


        return view('user_list', compact('yearMonth', 'startOfMonth', 'endOfMonth', 'attendances'));
    }
}



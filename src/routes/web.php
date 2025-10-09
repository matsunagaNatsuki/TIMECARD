<?php

use Illuminate\Support\Facades\Route;
use App\Http\Requests\EmailVerificationRequest;
use Illuminate\Http\Request;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\StampController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DetailController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Auth;


Route::get('/admin/login', function () {
    if (Auth::check() && Auth::user()->role === 'admin') {
        return redirect()->route('admin.attendances');
    }

    session()->put('url.intended', route('admin.attendances'));

    return view('auth.admin_login');
})->name('admin.login');


Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])->name('admin.login.post')
    ->middleware(['admin','guest', 'throttle:login']);

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware(['auth'])->name('verification.notice');

Route::post('/email/verification-notification', function () {
    request()->user()->sendEmailVerificationNotification();
    session()->put('resent', true);
    return back()->with('message', '確認メールを送信しました！');
})->middleware(['auth'])->name('verification.send');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    session()->forget('unauthenticated_user');
    return redirect('/attendance');
})->middleware(['auth','signed'])->name('verification.verify');


// 一般ユーザー画面
Route::middleware(['auth', 'verified'])->group(function() {
    Route::get('/attendance', [StampController::class,'stamp']);
    Route::post('/attendance',[StampController::class,'create'])->name('attendance.create');
    Route::get('/attendance/list',[AttendanceController::class,'list'])->name('attendance.list');
    Route::get('/attendance/detail/{id}',[DetailController::class,'detail'])->where('id', '.*')->name('attendance.detail');
    Route::post('/attendance/detail/{id}',[DetailController::class,'revise'])->name('attendance.revise');
    Route::post('/attendance/detail/create', [DetailController::class, 'create'])->name('detail.create');
    Route::get('/stamp_correction_request/list', [RequestController::class, 'request'])->name('attendance.request');
});

// 管理画面
Route::middleware(['auth', 'admin'])->group(function() {
    Route::get('/admin/attendances', [AttendanceController::class,'adminList'])->name('admin.attendances');
    Route::get('/admin/attendances/{id}', [DetailController::class,'adminDetail'])->name('admin.detail');
    Route::post('/admin/attendances/{id}', [DetailController::class, 'adminRevise'])->name('admin.revise');
    Route::get('/admin/users', [UsersController::class,'allUsers']);
    Route::get('/admin/users/{user}/attendances', [UsersController::class, 'userAttendance'])->name('users.attendance');
    Route::get('/admin/users/{user}/attendances/csv', [UsersController::class, 'export'])->name('export.csv');
    Route::get('/admin/requests', [RequestController::class, 'adminRequest'])->name('admin.requests');
    Route::get('/admin/requests/{id}', [RequestController::class, 'application'])->name('admin.application');
    Route::post('/admin/requests/{id}', [RequestController::class, 'approval'])->whereNumber('id')->name('admin.approval');
});







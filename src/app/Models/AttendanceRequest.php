<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;


class AttendanceRequest extends Model
{
    use HasFactory;

    protected $table ="requests";

    protected $fillable = [
        'attendance_id',
        'request_by',
        'approved_by',
        'clock_in',
        'clock_out',
        'remarks',
        'status',
    ];

    protected $casts = [
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(user::class, 'request_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function breaks(): HasMany
    {
        return $this->hasMany(RequestBreak::class,'request_id');
    }

    public function requestBreaks(): HasMany
    {
        return $this->hasMany(RequestBreak::class);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

}

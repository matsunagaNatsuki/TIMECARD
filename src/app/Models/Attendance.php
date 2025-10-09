<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\CarbonInterface;


class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'status',
        'clock_in',
        'clock_out',
        'remarks',
    ];

    protected $casts = [
        'date' => 'datetime:Y/m/d',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(BreakTime::class, 'attendance_id');
    }

    public function changeRequest(): HasOne
    {
        return $this->hasOne(AttendanceRequest::class, 'attendance_id');
    }

    public function isWorking(): bool
    {
        return $this->status === 'working';
    }

    public function isOnBreak(): bool
    {
        return $this->status === 'on_break';
    }

    public function isClockedOut(): bool
    {
        return $this->status === 'clocked_out';
    }

    public function isOffDuty(): bool
    {
        return $this->status === 'off_duty';
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'working'     => '勤務中',
            'on_break'    => '休憩中',
            'clocked_out' => '退勤済',
            'off_duty'    => '勤務外',
            default       => '勤務外',
        };
    }

    public function getBreakMinutesAttribute(): int
    {
        if (! $this->relationLoaded('breaks')) {
            $this->load('breaks');
        }

        $endRef = $this->clock_out ?? now();
        $mins = 0;

        foreach ($this->breaks as $br) {
            $start = $br->break_start;
            $end = $br->break_end ?? $endRef;
            if ($start && $end->greaterThan($start)) {
                $mins += $end->diffInMinutes($start);
            }
        }
        return $mins;
    }

    public function getBreakTimeFormattedAttribute(): string
    {
        $mins = $this->break_minutes;
        $h = intdiv($mins, 60);
        $m = $mins % 60;
        return sprintf('%02d:%02d', $h, $m);
    }

    public function getWorkMinutesAttribute(): ?int
    {
        if(!($this->clock_in instanceof CarbonInterface)) {
            return null;
        }

        $end = $this->clock_out instanceof CarbonInterface ? $this->clock_out : now();

        if ($end->lessThanOrEqualTo($this->clock_in)) {
            return 0;
        }

        $gross = $end->diffInMinutes($this->clock_in);
        $net = max(0, $gross - (int) $this->break_minutes);

        return $net;
    }


    public function getTotalWorkTimeAttribute(): ?string
    {
        $mins = $this->work_minutes;
        if ($mins === null) {
            return null;
        }

        $mins = max(0, (int) $mins);
        $h = intdiv($mins, 60);
        $m = $mins % 60;
        return sprintf('%02d:%02d', $h, $m);
    }

}
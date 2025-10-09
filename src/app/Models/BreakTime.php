<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;


class BreakTime extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\BreakFactory::new();
    }

    protected $table = "breaks";

    protected $fillable = [
        'attendance_id',
        'break_start',
        'break_end',
    ];

    protected $casts =[
        'break_start' => 'datetime',
        'break_end' => 'datetime',
    ];

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function isEnded(): bool
    {
        return !is_null($this->break_end);
    }

    public function getBreakTimeAttribute()
{
    if ($this->break_start && $this->break_end) {
        return Carbon::parse($this->break_start)
            ->diffInMinutes(Carbon::parse($this->break_end));
    }
    return 0;
}


}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class RequestBreak extends Model
{
    use HasFactory;

    protected $table = 'request_breaks';

    protected $fillable = [
        'request_id',
        'break_start',
        'break_end',
    ];

    protected $casts =[
        'break_start' => 'datetime',
        'break_end' => 'datetime',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(AttendanceRequest::class,'request_id');
    }
}

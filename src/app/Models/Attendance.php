<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
        'memo',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'break_start' => 'datetime',
        'break_end' => 'datetime',
    ];

    // ユーザーとのリレーション（1対多の逆）
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

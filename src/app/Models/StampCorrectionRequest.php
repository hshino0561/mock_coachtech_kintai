<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class StampCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'user_id',
        'attendance_date',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
        'memo',
        'status',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'break_start' => 'datetime:H:i',
        'break_end' => 'datetime:H:i',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function breakCorrectionRequests()
    {
        return $this->hasMany(BreakCorrectionRequest::class);
    }
    
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'approved' => '承認済み',
            'rejected' => '却下',
            default => '承認待ち', // nullや空欄も含む
        };
    }
}
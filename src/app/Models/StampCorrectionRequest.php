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
        'reason',
        'status',
    ];    

    public function user()
    {
        return $this->belongsTo(User::class);
    } 
    
    public function getAttendanceDateAttribute()
    {
    return optional($this->attendance)->date;
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
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
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

    // 休憩ログ（通常）
    public function breakLogs(): HasMany
    {
        return $this->hasMany(BreakLog::class, 'attendance_id');
    }    

    // 秒数を「切り捨てて」分単位に変換する関数
    private function secondsToTruncatedTime(int $seconds): string
    {
        // 秒 → 分に変換し、切り捨て
        $totalMinutes = floor($seconds / 60);
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
    
        return sprintf('%d:%02d', $hours, $minutes);
    }    

    // 合計休憩時間（秒）を計算するアクセサ
    public function getBreakDurationMinutesAttribute(): ?int
    {
        if (!$this->breakLogs || $this->breakLogs->isEmpty()) {
            return null;
        }
    
        return $this->breakLogs->reduce(function ($carry, $log) {
            if ($log->break_start && $log->break_end) {
                $start = \Carbon\Carbon::parse($log->break_start)->setSecond(0);
                $end = \Carbon\Carbon::parse($log->break_end)->setSecond(0);
                return $carry + $end->diffInMinutes($start); // ? 秒切り捨てで正確
            }
            return $carry;
        }, 0);
    }    

    public function getBreakDurationFormattedAttribute(): string
    {
        $minutes = $this->break_duration_minutes;
    
        if (is_null($minutes)) {
            return '-';
        }
    
        return sprintf('%d:%02d', floor($minutes / 60), $minutes % 60);
    }

    public function getWorkingDurationFormattedAttribute(): string
    {
        if (!$this->start_time || !$this->end_time) {
            return '-';
        }
    
        $start = strtotime($this->start_time);
        $end = strtotime($this->end_time);
        $break = $this->break_duration_seconds ?? 0;
    
        $totalSeconds = max($end - $start - $break, 0);
    
        return $this->secondsToTruncatedTime($totalSeconds);
    }    

    // 修正申請（複数ある場合）    
    public function stampCorrectionRequests()
    {
        return $this->hasMany(StampCorrectionRequest::class);
    }
}
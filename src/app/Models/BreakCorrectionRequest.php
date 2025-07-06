<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'stamp_correction_request_id',
        'break_start',
        'break_end',
    ];

    public $timestamps = true;

    protected $casts = [
        'break_start' => 'datetime',
        'break_end' => 'datetime',
    ];    
    
    public function stampCorrectionRequest()
    {
        return $this->belongsTo(StampCorrectionRequest::class);
    }
}

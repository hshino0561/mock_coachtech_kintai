<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BreakLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'break_start',
        'break_end',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

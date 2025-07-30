<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;

    protected $table = 'breaks';


    protected $fillable = [
        'attendance_id',
        'break_start_time',
        'break_end_time',
    ];

    /**
     * リレーション：この休憩時間が所属する勤怠情報
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}

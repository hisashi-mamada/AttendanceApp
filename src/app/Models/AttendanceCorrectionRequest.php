<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'reason',
        'status',
        'approved_by',
        'corrected_clock_in_time',
        'corrected_clock_out_time',
        'corrected_breaks',
        'requested_clock_in_time',
        'requested_clock_out_time',
    ];

    protected $casts = [
        'corrected_clock_in_time' => 'datetime',
        'corrected_clock_out_time' => 'datetime',
        'corrected_breaks' => 'array',
        'requested_clock_in_time' => 'datetime',
        'requested_clock_out_time' => 'datetime',
    ];

    /**
     * リレーション：申請を出したユーザー
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * リレーション：紐づく勤怠情報
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * リレーション：承認した管理者（Userテーブルを参照）
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * リレーション：この申請に紐づく管理者メモ（1対多）
     */
    public function adminMemos()
    {
        return $this->hasMany(AdminMemo::class, 'correction_request_id');
    }

    public function approvalStatus()
    {
        return $this->belongsTo(ApprovalStatus::class, 'status', 'name');
    }
}

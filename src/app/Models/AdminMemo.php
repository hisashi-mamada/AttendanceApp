<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminMemo extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'correction_request_id',
        'memo_text',
    ];

    /**
     * リレーション：このメモを作成した管理者（Userテーブルを参照）
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * リレーション：紐づく修正申請
     */
    public function correctionRequest()
    {
        return $this->belongsTo(AttendanceCorrectionRequest::class, 'correction_request_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class ApprovalStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function correctionRequests()
    {
        return $this->hasMany(AttendanceCorrectionRequest::class, 'status', 'name');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type',
        'start_date',
        'end_date',
        'days',
        'reason',
        'evidence',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
    ];

    // 关联用户（申请者）
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 关联审批人
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // 请假类型中文映射
    public function getLeaveTypeTextAttribute()
    {
        $types = [
            'annual' => '年假',
            'sick' => '病假',
            'personal' => '事假',
            'maternity' => '产假',
            'paternity' => '陪产假',
            'other' => '其他',
        ];

        return $types[$this->attributes['leave_type'] ?? ''] ?? '其他';
    }

    // 状态中文映射
    public function getStatusTextAttribute()
    {
        $statuses = [
            'pending' => '待审批',
            'approved' => '已批准',
            'rejected' => '已拒绝',
        ];

        return $statuses[$this->attributes['status'] ?? ''] ?? '未知';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'year',
        'month',
        // 收入项
        'base_salary',
        'allowances',
        'overtime_other',
        'bonus',
        'unutilised_pay_leave',
        'unpaid_leave',
        'total_earnings',
        // 扣除项
        'deductions',
        'employee_cpf',
        'cdac_mbmf_sinda',
        'total_deduction',
        // 净工资
        'net_pay',
        // 其他扣除
        'advance_loan',
        'net_pay_after_other_deduction',
        // 雇主贡献
        'employer_cpf',
        'sdl',
        // 银行信息
        'bank_name',
        'bank_account_number',
        'credit_date',
        // 审批信息
        'prepared_by',
        'approved_by',
        // 其他
        'tax',
        'total_amount',
        'status',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'base_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'overtime_other' => 'decimal:2',
        'bonus' => 'decimal:2',
        'unutilised_pay_leave' => 'decimal:2',
        'unpaid_leave' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'deductions' => 'decimal:2',
        'employee_cpf' => 'decimal:2',
        'cdac_mbmf_sinda' => 'decimal:2',
        'total_deduction' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'advance_loan' => 'decimal:2',
        'net_pay_after_other_deduction' => 'decimal:2',
        'employer_cpf' => 'decimal:2',
        'sdl' => 'decimal:2',
        'tax' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'credit_date' => 'date',
        'paid_at' => 'datetime',
    ];

    // 关联用户
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 关联准备人
    public function preparer()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    // 关联审批人
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // 状态中文映射
    public function getStatusTextAttribute()
    {
        $statuses = [
            'pending' => '待发放',
            'paid' => '已发放',
        ];

        return $statuses[$this->attributes['status'] ?? ''] ?? '未知';
    }

    // 月份名称
    public function getMonthNameAttribute()
    {
        $months = [
            1 => '一月', 2 => '二月', 3 => '三月', 4 => '四月',
            5 => '五月', 6 => '六月', 7 => '七月', 8 => '八月',
            9 => '九月', 10 => '十月', 11 => '十一月', 12 => '十二月',
        ];

        return $months[$this->attributes['month'] ?? 0] ?? '';
    }
}

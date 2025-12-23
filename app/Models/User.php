<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'company_id',
        'current_company_id',
        'commencement_date',
        'last_date',
        'sex',
        'position',
        'nric_fin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'commencement_date' => 'date',
        'last_date' => 'date',
    ];

    // 关联请假记录
    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    // 关联薪资记录
    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    // 作为审批人的请假记录
    public function approvedLeaves()
    {
        return $this->hasMany(Leave::class, 'approved_by');
    }

    // 关联主公司（保留向后兼容）
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    // 关联当前选择的公司
    public function currentCompany()
    {
        return $this->belongsTo(Company::class, 'current_company_id');
    }

    // 多对多关联：用户管理的所有公司
    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_user')
                    ->withTimestamps();
    }

    // 获取当前公司ID（优先使用current_company_id，否则使用company_id）
    public function getActiveCompanyIdAttribute()
    {
        // 如果current_company_id为空但有company_id，返回company_id
        // 这样可以确保向后兼容
        return $this->current_company_id ?? $this->company_id;
    }

    // 检查用户是否有权限访问某个公司
    public function hasAccessToCompany($companyId)
    {
        // 管理员可以访问所有公司
        if ($this->isAdmin()) {
            return true;
        }
        
        return $this->companies()->where('companies.id', $companyId)->exists() 
            || $this->company_id == $companyId;
    }

    // 角色检查方法
    public function isEmployee()
    {
        return $this->role === 'employee';
    }

    public function isHR()
    {
        return $this->role === 'hr';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    // 获取角色中文名称
    public function getRoleTextAttribute()
    {
        $roles = [
            'employee' => '员工',
            'hr' => 'HR',
            'admin' => '管理员',
        ];

        return $roles[$this->role] ?? '未知';
    }
}

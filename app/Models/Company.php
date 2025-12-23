<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
        'email',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // 关联用户（主公司关系）
    public function users()
    {
        return $this->hasMany(User::class, 'company_id');
    }

    // 多对多关联：管理该公司的所有用户
    public function managers()
    {
        return $this->belongsToMany(User::class, 'company_user')
                    ->withTimestamps();
    }

    // 关联请假记录（通过用户）
    public function leaves()
    {
        return $this->hasManyThrough(Leave::class, User::class);
    }

    // 关联薪资记录（通过用户）
    public function payrolls()
    {
        return $this->hasManyThrough(Payroll::class, User::class);
    }
}

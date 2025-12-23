<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 创建测试公司
        $company = Company::firstOrCreate(
            ['name' => '测试科技有限公司'],
            [
                'code' => 'TEST001',
                'address' => '测试地址123号',
                'phone' => '13800138000',
                'email' => 'test@company.com',
                'description' => '这是一个测试公司',
                'is_active' => true,
            ]
        );

        // 创建HR账户
        $hr = User::firstOrCreate(
            ['email' => 'hr@test.com'],
            [
                'name' => 'HR管理员',
                'password' => Hash::make('password123'),
                'role' => 'hr',
                'company_id' => $company->id,
                'current_company_id' => $company->id,
            ]
        );
        
        // 更新已存在用户的角色
        if ($hr->wasRecentlyCreated === false) {
            $hr->role = 'hr';
            $hr->save();
        }

        // 将HR添加到公司的管理列表（多对多关系）
        $hr->companies()->syncWithoutDetaching([$company->id]);

        // 创建员工账户
        $staff = User::firstOrCreate(
            ['email' => 'staff@test.com'],
            [
                'name' => '测试员工',
                'password' => Hash::make('password123'),
                'role' => 'employee',
                'company_id' => $company->id,
                'current_company_id' => $company->id,
            ]
        );
        
        // 更新已存在用户的角色
        if ($staff->wasRecentlyCreated === false) {
            $staff->role = 'employee';
            $staff->save();
        }
        
        // 创建管理员账户（用于测试）
        $admin = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => '系统管理员',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'company_id' => $company->id,
                'current_company_id' => $company->id,
            ]
        );
        
        // 更新已存在用户的角色
        if ($admin->wasRecentlyCreated === false) {
            $admin->role = 'admin';
            $admin->save();
        }
        
        // 将管理员添加到公司的管理列表
        $admin->companies()->syncWithoutDetaching([$company->id]);

        $this->command->info('测试账户创建成功！');
        $this->command->info('========================================');
        $this->command->info('管理员账户：');
        $this->command->info('  邮箱：admin@test.com');
        $this->command->info('  密码：password123');
        $this->command->info('  名称：系统管理员');
        $this->command->info('  角色：管理员');
        $this->command->info('');
        $this->command->info('HR账户：');
        $this->command->info('  邮箱：hr@test.com');
        $this->command->info('  密码：password123');
        $this->command->info('  名称：HR管理员');
        $this->command->info('  角色：HR');
        $this->command->info('');
        $this->command->info('员工账户：');
        $this->command->info('  邮箱：staff@test.com');
        $this->command->info('  密码：password123');
        $this->command->info('  名称：测试员工');
        $this->command->info('  角色：员工');
        $this->command->info('');
        $this->command->info('测试公司：测试科技有限公司');
        $this->command->info('');
        $this->command->info('提示：登录后可在页面左上角切换角色进行测试');
        $this->command->info('========================================');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\Payroll;
use App\Models\Leave;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TestEmployeeDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 获取或创建测试公司
        $company = Company::where('name', '测试科技有限公司')->first();
        
        if (!$company) {
            $company = Company::create([
                'name' => '测试科技有限公司',
                'code' => 'TEST001',
                'address' => '测试地址123号',
                'phone' => '13800138000',
                'email' => 'test@company.com',
                'description' => '这是一个测试公司',
                'is_active' => true,
            ]);
        }

        // 创建3个测试员工
        $employees = [
            [
                'name' => '张三',
                'email' => 'zhangsan@test.com',
                'password' => Hash::make('password123'),
                'role' => 'employee',
            ],
            [
                'name' => '李四',
                'email' => 'lisi@test.com',
                'password' => Hash::make('password123'),
                'role' => 'employee',
            ],
            [
                'name' => '王五',
                'email' => 'wangwu@test.com',
                'password' => Hash::make('password123'),
                'role' => 'employee',
            ],
        ];

        $baseSalaries = [5000, 6000, 7000]; // 不同的基本工资

        foreach ($employees as $index => $employeeData) {
            $employee = User::firstOrCreate(
                ['email' => $employeeData['email']],
                array_merge($employeeData, [
                    'company_id' => $company->id,
                    'current_company_id' => $company->id,
                ])
            );

            $baseSalary = $baseSalaries[$index];

            // 为每个员工创建2025年1-12月的工资记录
            for ($month = 1; $month <= 12; $month++) {
                $allowances = rand(500, 1500);
                $overtimeOther = rand(0, 2000);
                $bonus = $month % 3 == 0 ? rand(1000, 3000) : 0; // 每3个月发一次奖金
                $unpaidLeave = rand(0, 2) > 0 ? 0 : round($baseSalary / 22 * rand(1, 3), 2); // 随机无薪假期
                $employeeCpf = round($baseSalary * 0.20, 2); // CPF按20%计算
                $cdacMbmfSinda = 1.00;
                $deductions = rand(0, 500);
                $tax = round($baseSalary * 0.05, 2); // 税费按5%计算
                $advanceLoan = $month % 6 == 0 ? rand(500, 1000) : 0; // 每6个月有预付款扣除
                $employerCpf = round($baseSalary * 0.17, 2); // 雇主CPF按17%计算
                $sdl = round($baseSalary * 0.0025, 2); // SDL按0.25%计算

                // 计算总收入
                $totalEarnings = $baseSalary 
                    + $allowances 
                    + $overtimeOther 
                    + $bonus 
                    + 0 
                    - $unpaidLeave;

                // 计算总扣除
                $totalDeduction = $employeeCpf 
                    + $cdacMbmfSinda 
                    + $deductions 
                    + $tax;

                // 计算净工资
                $netPay = $totalEarnings - $totalDeduction;

                // 计算其他扣除后的净工资
                $netPayAfterOtherDeduction = $netPay - $advanceLoan;

                $payroll = Payroll::firstOrCreate(
                    [
                        'user_id' => $employee->id,
                        'year' => 2025,
                        'month' => $month,
                    ],
                    [
                        'base_salary' => $baseSalary,
                        'allowances' => $allowances,
                        'overtime_other' => $overtimeOther,
                        'bonus' => $bonus,
                        'unutilised_pay_leave' => 0,
                        'unpaid_leave' => $unpaidLeave,
                        'total_earnings' => round($totalEarnings, 2),
                        'employee_cpf' => $employeeCpf,
                        'cdac_mbmf_sinda' => $cdacMbmfSinda,
                        'deductions' => $deductions,
                        'tax' => $tax,
                        'total_deduction' => round($totalDeduction, 2),
                        'net_pay' => round($netPay, 2),
                        'advance_loan' => $advanceLoan,
                        'net_pay_after_other_deduction' => round($netPayAfterOtherDeduction, 2),
                        'employer_cpf' => $employerCpf,
                        'sdl' => $sdl,
                        'bank_name' => 'DBS BANK LTD.',
                        'bank_account_number' => '20790896' . $index . str_pad($month, 2, '0', STR_PAD_LEFT),
                        'credit_date' => Carbon::create(2025, $month, 25)->format('Y-m-d'),
                        'notes' => $month == 12 ? '年终工资' : null,
                        'prepared_by' => 1, // 假设ID为1的用户是准备人
                        'status' => $month <= Carbon::now()->month ? 'paid' : 'pending',
                        'paid_at' => $month <= Carbon::now()->month ? Carbon::create(2025, $month, 25) : null,
                        'total_amount' => round($netPayAfterOtherDeduction, 2), // 总金额等于最终发放金额
                    ]
                );
            }

            // 为每个员工创建一些请假记录
            $leaveTypes = ['annual', 'sick', 'personal', 'maternity', 'paternity', 'other'];
            
            // 创建5-8条请假记录
            $leaveCount = rand(5, 8);
            for ($i = 0; $i < $leaveCount; $i++) {
                $startDate = Carbon::create(2025, rand(1, 12), rand(1, 20));
                $days = rand(1, 5);
                $endDate = $startDate->copy()->addDays($days - 1);
                
                // 确保不超过2025年
                if ($endDate->year > 2025 || $endDate->month > 12) {
                    continue;
                }

                $leaveType = $leaveTypes[array_rand($leaveTypes)];
                $status = ['pending', 'approved', 'rejected'][rand(0, 2)];

                Leave::firstOrCreate(
                    [
                        'user_id' => $employee->id,
                        'leave_type' => $leaveType,
                        'start_date' => $startDate->format('Y-m-d'),
                        'end_date' => $endDate->format('Y-m-d'),
                    ],
                    [
                        'days' => $days,
                        'reason' => $this->getLeaveReason($leaveType),
                        'status' => $status,
                        'approved_by' => $status !== 'pending' ? 1 : null, // 假设ID为1的用户是审批人
                        'approved_at' => $status !== 'pending' ? $startDate->copy()->subDays(2) : null,
                        'rejection_reason' => $status === 'rejected' ? '不符合公司请假政策' : null,
                    ]
                );
            }

            $this->command->info("已为员工 {$employee->name} 创建了工资记录和请假记录");
        }

        $this->command->info('========================================');
        $this->command->info('测试数据创建完成！');
        $this->command->info('========================================');
        $this->command->info("已创建3个员工，每个员工包含：");
        $this->command->info("- 2025年1-12月的工资记录（共36条）");
        $this->command->info("- 5-8条请假记录（共约15-24条）");
        $this->command->info('========================================');
    }

    /**
     * 根据请假类型生成请假原因
     */
    private function getLeaveReason($leaveType)
    {
        $reasons = [
            'annual' => '年度休假，需要休息调整',
            'sick' => '身体不适，需要就医休养',
            'personal' => '处理个人事务',
            'maternity' => '产假',
            'paternity' => '陪产假',
            'other' => '其他原因需要请假',
        ];

        return $reasons[$leaveType] ?? '需要请假';
    }
}


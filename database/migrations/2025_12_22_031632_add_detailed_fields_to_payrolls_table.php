<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            // 详细的收入项（Earnings）
            $table->decimal('overtime_other', 10, 2)->default(0)->after('allowances')->comment('加班/其他');
            $table->decimal('bonus', 10, 2)->default(0)->after('overtime_other')->comment('奖金');
            $table->decimal('unutilised_pay_leave', 10, 2)->default(0)->after('bonus')->comment('未使用的带薪假期');
            $table->decimal('unpaid_leave', 10, 2)->default(0)->after('unutilised_pay_leave')->comment('无薪假期');
            $table->decimal('total_earnings', 10, 2)->default(0)->after('unpaid_leave')->comment('总收入');
            
            // 详细的扣除项（Deductions）
            $table->decimal('employee_cpf', 10, 2)->default(0)->after('deductions')->comment('员工CPF');
            $table->decimal('cdac_mbmf_sinda', 10, 2)->default(0)->after('employee_cpf')->comment('CDAC/MBMF/SINDA基金');
            $table->decimal('total_deduction', 10, 2)->default(0)->after('cdac_mbmf_sinda')->comment('总扣除');
            
            // 净工资
            $table->decimal('net_pay', 10, 2)->default(0)->after('total_deduction')->comment('净工资');
            
            // 其他扣除（Other Deduction）
            $table->decimal('advance_loan', 10, 2)->default(0)->after('net_pay')->comment('预付款/贷款');
            $table->decimal('net_pay_after_other_deduction', 10, 2)->default(0)->after('advance_loan')->comment('其他扣除后的净工资');
            
            // 雇主贡献（Employer Contributions）
            $table->decimal('employer_cpf', 10, 2)->default(0)->after('net_pay_after_other_deduction')->comment('雇主CPF');
            $table->decimal('sdl', 10, 2)->default(0)->after('employer_cpf')->comment('SDL技能发展税');
            
            // 银行信息（Bank Credit Information）
            $table->string('bank_name')->nullable()->after('sdl')->comment('银行名称');
            $table->string('bank_account_number')->nullable()->after('bank_name')->comment('银行账号');
            $table->date('credit_date')->nullable()->after('bank_account_number')->comment('入账日期');
            
            // 审批信息
            $table->foreignId('prepared_by')->nullable()->constrained('users')->onDelete('set null')->after('credit_date')->comment('准备人');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null')->after('prepared_by')->comment('审批人');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropForeign(['prepared_by']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'overtime_other',
                'bonus',
                'unutilised_pay_leave',
                'unpaid_leave',
                'total_earnings',
                'employee_cpf',
                'cdac_mbmf_sinda',
                'total_deduction',
                'net_pay',
                'advance_loan',
                'net_pay_after_other_deduction',
                'employer_cpf',
                'sdl',
                'bank_name',
                'bank_account_number',
                'credit_date',
                'prepared_by',
                'approved_by',
            ]);
        });
    }
};

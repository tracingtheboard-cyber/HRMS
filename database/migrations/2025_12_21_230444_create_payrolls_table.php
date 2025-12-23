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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->year('year');
            $table->integer('month'); // 1-12
            $table->decimal('base_salary', 10, 2)->default(0);
            $table->decimal('allowances', 10, 2)->default(0); // 津贴
            $table->decimal('deductions', 10, 2)->default(0); // 扣除
            $table->decimal('tax', 10, 2)->default(0); // 税费
            $table->decimal('total_amount', 10, 2); // 总金额
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // 确保同一员工同一月份只有一条记录
            $table->unique(['user_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payrolls');
    }
};

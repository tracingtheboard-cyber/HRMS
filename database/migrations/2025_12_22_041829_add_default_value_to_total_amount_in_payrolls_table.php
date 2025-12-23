<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 使用原生SQL为total_amount字段添加默认值
        DB::statement('ALTER TABLE `payrolls` MODIFY COLUMN `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 恢复total_amount字段为没有默认值
        DB::statement('ALTER TABLE `payrolls` MODIFY COLUMN `total_amount` DECIMAL(10,2) NOT NULL');
    }
};

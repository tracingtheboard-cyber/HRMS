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
        // 由于 Doctrine DBAL 不支持修改 ENUM 类型，我们需要使用原生 SQL
        // 注意：SQLite (测试环境) 对 ALTER TABLE 的支持有限，可能需要特殊处理
        // 但这里假设生产环境是 MySQL
        
        $connection = config('database.default');
        
        if ($connection === 'mysql') {
            DB::statement("ALTER TABLE payrolls MODIFY COLUMN status ENUM('pending', 'paid', 'locked') NOT NULL DEFAULT 'pending'");
        } else {
            // SQLite 替代方案 (如果需要支持测试环境)
            // SQLite 不强制检查 ENUM，所以这里可能不需要做任何事，或者作为 TEXT 处理
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $connection = config('database.default');
        
        if ($connection === 'mysql') {
            // 回滚时要注意，如果有 'locked' 状态的数据，这可能会导致错误
            // 这里我们先将 'locked' 状态的数据重置为 'pending'
            DB::table('payrolls')->where('status', 'locked')->update(['status' => 'pending']);
            
            DB::statement("ALTER TABLE payrolls MODIFY COLUMN status ENUM('pending', 'paid') NOT NULL DEFAULT 'pending'");
        }
    }
};

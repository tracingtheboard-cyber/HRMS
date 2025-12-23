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
        Schema::table('users', function (Blueprint $table) {
            $table->date('commencement_date')->nullable()->after('role')->comment('入职日期 (Commencement Date)');
            $table->date('last_date')->nullable()->after('commencement_date')->comment('最后日期 (Last Date)');
            $table->enum('sex', ['M', 'F'])->nullable()->after('last_date')->comment('性别 (Sex): M=男, F=女');
            $table->string('position')->nullable()->after('sex')->comment('职位 (Position)');
            $table->string('nric_fin')->nullable()->after('position')->comment('身份证/工作准证号 (NRIC/FIN)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'commencement_date',
                'last_date',
                'sex',
                'position',
                'nric_fin',
            ]);
        });
    }
};

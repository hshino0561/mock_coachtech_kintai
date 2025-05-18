<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('stamp_correction_requests', function (Blueprint $table) {
            $table->text('reason')->nullable()->after('memo'); // 備考の後に追加
        });
    }

    public function down()
    {
        Schema::table('stamp_correction_requests', function (Blueprint $table) {
            $table->dropColumn('reason');
        });
    }
};

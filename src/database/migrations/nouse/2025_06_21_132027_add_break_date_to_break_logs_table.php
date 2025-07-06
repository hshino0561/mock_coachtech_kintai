<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('break_logs', function (Blueprint $table) {
            $table->date('break_date')->after('user_id')->nullable()->index();    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('break_logs', function (Blueprint $table) {
            $table->dropColumn('break_date');    
        });
    }
};

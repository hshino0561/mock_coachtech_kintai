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
            if (!Schema::hasColumn('break_logs', 'attendance_id')) {
                $table->unsignedBigInteger('attendance_id')->after('id');
                $table->foreign('attendance_id')->references('id')->on('attendances')->onDelete('cascade');
            }
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('break_logs', function (Blueprint $table) {
            //
        });
    }
};

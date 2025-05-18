<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date')->nullable();
            $table->timestamp('start_time')->nullable();       // 出勤時刻
            $table->timestamp('end_time')->nullable();         // 退勤時刻
            $table->timestamp('break_start')->nullable();      // 休憩開始
            $table->timestamp('break_end')->nullable();        // 休憩終了
            $table->text('note')->nullable();                  // 備考
            $table->timestamps();                              // created_at, updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};

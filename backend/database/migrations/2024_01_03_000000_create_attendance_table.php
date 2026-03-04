<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->dateTime('clock_in_time');
            $table->dateTime('clock_out_time')->nullable();
            $table->date('date');
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'completed', 'incomplete'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('date');
            $table->unique(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};

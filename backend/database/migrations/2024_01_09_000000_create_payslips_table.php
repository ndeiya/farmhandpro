<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('hours_worked', 8, 2);
            $table->decimal('hourly_rate', 10, 2);
            $table->decimal('gross_pay', 10, 2);
            $table->decimal('deductions', 10, 2)->default(0);
            $table->decimal('net_pay', 10, 2);
            $table->enum('status', ['draft', 'issued', 'paid'])->default('draft');
            $table->date('issued_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('farm_id');
            $table->index('period_end');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};

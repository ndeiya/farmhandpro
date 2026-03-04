<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('crops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('variety');
            $table->decimal('acres', 10, 2);
            $table->date('planted_date');
            $table->date('expected_harvest')->nullable();
            $table->enum('status', ['planning', 'planted', 'growing', 'ready', 'harvested'])->default('planning');
            $table->decimal('yield_estimate', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('farm_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crops');
    }
};

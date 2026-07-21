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
        Schema::disableForeignKeyConstraints();

        Schema::create('meter_readings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('meter_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('meter_reader_id')
                ->constrained('users');

            $table->decimal('previous_reading', 12, 2);
            $table->decimal('current_reading', 12, 2);
            $table->decimal('consumption', 12, 2);
            $table->decimal('price_per_kwh', 10, 2);
            $table->decimal('reading_cost', 12, 2);

            $table->date('reading_date')->index();

            $table->enum('reading_method', ['manual', 'qr_scan'])->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->nullable()->index();

            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meter_readings');
    }
};

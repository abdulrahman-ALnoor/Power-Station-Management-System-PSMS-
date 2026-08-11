<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('meter_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('customer_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('created_by')
                ->constrained('users');

            $table->foreignId('assigned_engineer_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('request_type', [
                'new_connection',
                'maintenance',
                'disconnection',
            ]);

            $table->enum('priority', [
                'low',
                'medium',
                'high',
                'emergency',
            ])->nullable()->index();

            $table->enum('status', [
                'pending',
                'assigned',
                'in_progress',
                'completed',
                'cancelled',
            ])->nullable()->index()->default('pending');

            $table->text('description')->nullable();

            $table->timestamp('completed_at')->nullable();

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
        Schema::dropIfExists('service_requests');
    }
};

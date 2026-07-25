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

        Schema::create('meters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('meter_number', 100)->unique()->index();
            $table->string('qr_code', 255)->unique()->index();
            $table->date('installation_date')->nullable();
            $table->text('installation_location')->nullable();
            $table->enum('status', ["active","disconnected","maintenance","damaged"])->index()->nullable();
            $table->foreignId('installed_by')
                ->constrained('users');
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users');
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
        Schema::dropIfExists('meters');
    }
};

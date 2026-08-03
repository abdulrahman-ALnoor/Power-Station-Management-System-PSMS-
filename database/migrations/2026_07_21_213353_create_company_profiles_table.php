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

        Schema::create('company_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('company_name', 200);
            $table->string('logo', 255)->nullable();
            $table->text('address')->nullable();
            $table->string('whatsapp_number', 30)->nullable();
            $table->string('support_number', 30)->nullable();
            $table->string('currency', 20);
            $table->decimal('price_per_kwh', 10, 2);
            $table->integer('reading_cycle_days')->nullable()->default(15);
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
        Schema::dropIfExists('company_profile');
    }
};

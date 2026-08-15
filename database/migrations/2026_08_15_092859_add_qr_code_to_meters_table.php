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
    // التحقق أولاً مما إذا كان الحقل غير موجود قبل إضافته
    if (!Schema::hasColumn('meters', 'qr_code')) {
        Schema::table('meters', function (Blueprint $table) {
            $table->string('qr_code')->nullable()->after('status'); 
        });
    }

}

public function down(): void
{
    Schema::table('meters', function (Blueprint $table) {
        $table->dropColumn('qr_code');
    });
}
};

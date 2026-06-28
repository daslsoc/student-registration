<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop the "last year" class columns (dhamma_class / sinhala_class). They
     * captured which class a child attended the previous year and are no longer
     * used — the current-year allocation lives in allocated_dhamma_class /
     * allocated_sinhala_class.
     */
    public function up(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->dropColumn(['dhamma_class', 'sinhala_class']);
        });
    }

    public function down(): void
    {
        Schema::table('children', function (Blueprint $table) {
            // Re-created as nullable — the original values can't be restored.
            $table->string('dhamma_class')->nullable();
            $table->string('sinhala_class')->nullable();
        });
    }
};

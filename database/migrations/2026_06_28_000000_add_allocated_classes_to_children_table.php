<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('children', function (Blueprint $table) {
            // This year's allocated class per subject (e.g. "Class C"). Set at
            // payment by the allocation rule, editable later by an admin. The
            // existing dhamma_class/sinhala_class stay as last-year history.
            $table->string('allocated_dhamma_class')->nullable()->after('sinhala_class');
            $table->string('allocated_sinhala_class')->nullable()->after('allocated_dhamma_class');

            // The integration delta query filters by updated_at.
            $table->index('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->dropIndex(['updated_at']);
            $table->dropColumn(['allocated_dhamma_class', 'allocated_sinhala_class']);
        });
    }
};

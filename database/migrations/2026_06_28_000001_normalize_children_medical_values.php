<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Normalise the various "nothing to report" spellings in the children's
     * allergies / special_needs columns to a single canonical "None", so the
     * Allergies & Medical filter and the registration forms stay consistent.
     *
     * Matches the WHOLE trimmed value only (case-insensitive), so a genuine
     * entry that merely contains "no" (e.g. "no nuts") is left untouched.
     * NULL / blank values are intentionally left as-is.
     */
    public function up(): void
    {
        $tokens = ['nil', 'no', 'na', 'n/a', 'none', 'not applicable', 'non'];
        $placeholders = implode(',', array_fill(0, count($tokens), '?'));

        foreach (['allergies', 'special_needs'] as $column) {
            DB::table('children')
                ->whereRaw("LOWER(TRIM({$column})) IN ({$placeholders})", $tokens)
                ->update([$column => 'None']);
        }
    }

    public function down(): void
    {
        // One-way data normalisation — the original varied spellings can't be
        // reconstructed, so there is nothing to roll back.
    }
};

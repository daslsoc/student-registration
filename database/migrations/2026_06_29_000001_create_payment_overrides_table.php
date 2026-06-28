<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Audit trail for manual payment-status overrides (cash / eftpos at the
     * desk, or corrections). Every override writes one immutable row here:
     * who did it, to whom, the before/after status, method, amount, and a note.
     * The admin's name is denormalised so the history survives a user being
     * renamed or deleted.
     */
    public function up(): void
    {
        Schema::create('payment_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('parents')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('performed_by')->nullable();   // admin name, denormalised
            $table->string('action');                     // marked_paid | reverted
            $table->string('method')->nullable();         // cash | eftpos
            $table->decimal('amount', 8, 2)->nullable();
            $table->string('previous_status')->nullable();
            $table->string('new_status')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_overrides');
    }
};

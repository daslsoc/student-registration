<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration for creating the parents table,
 * storing parent/guardian details.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->string('parent1_first_name');
            $table->string('parent1_last_name');
            $table->string('parent1_email');
            $table->string('parent1_phone');
            $table->string('parent2_first_name')->nullable();
            $table->string('parent2_last_name')->nullable();
            $table->string('parent2_email')->nullable();
            $table->string('parent2_phone')->nullable();
            $table->string('emergency_contact_name');
            $table->string('emergency_contact_phone');
            $table->string('relationship_to_family');
            $table->string('update_token')->nullable();
            $table->dateTime('token_expires_at')->nullable();
            $table->string('payment_token', 100)->nullable();
            $table->string('registration_status')->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('parents');
    }
};

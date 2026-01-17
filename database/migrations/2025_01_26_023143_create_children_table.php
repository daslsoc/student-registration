<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration for creating children table,
 * storing child details (one-to-many with parents).
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('children', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('parents')->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender');
            $table->date('date_of_birth');
            $table->enum('residency_status', ['Temporary Resident', 'Permanent Resident', 'Citizen']);
            $table->string('day_school_name');
            $table->string('day_school_year');
            $table->string('allergies')->nullable();
            $table->string('special_needs')->nullable();
            $table->string('dhamma_class');
            $table->string('sinhala_class');
            $table->integer('student_number');
            $table->integer('year_of_first_registration')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('children');
    }
};

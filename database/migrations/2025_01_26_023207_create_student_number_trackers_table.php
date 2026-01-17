<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tracks the incrementing student_number.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('student_number_trackers', function (Blueprint $table) {
            $table->id();
            $table->integer('current_number')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_number_trackers');
    }
};

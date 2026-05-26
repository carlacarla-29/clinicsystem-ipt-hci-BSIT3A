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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('student_id'); // e.g. "2024-0001"
    $table->string('name');
    $table->string('grade_level');
    $table->string('section');
    $table->string('gender');
    $table->date('birthdate')->nullable();
    $table->timestamps();
    $table->unique(['user_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};

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
        Schema::create('visits', function (Blueprint $table) {
             $table->id();
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->text('complaint');
    $table->text('diagnosis')->nullable();
    $table->text('treatment')->nullable();
    $table->string('status')->default('pending'); // pending, treated, referred
    $table->timestamp('visited_at')->useCurrent();
    $table->foreignId('recorded_by')->constrained('users');
    $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};

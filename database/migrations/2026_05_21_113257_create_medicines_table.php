<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('unit');       // tablet, ml, sachet, capsule, piece
            $table->integer('quantity');  // current stock
            $table->timestamps();
            $table->unique(['user_id', 'name']);
        });

        Schema::create('visit_medicines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete(); // FIX: added cascadeOnDelete so medicines can be deleted
            $table->integer('quantity_given');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // FIX: must drop pivot table FIRST before medicines (foreign key order)
        Schema::dropIfExists('visit_medicines');
        Schema::dropIfExists('medicines');
    }
};

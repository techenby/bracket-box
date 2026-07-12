<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('matchups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bracket_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('round');
            $table->unsignedInteger('position');
            $table->foreignId('contestant_one_id')->nullable()->constrained('contestants')->nullOnDelete();
            $table->foreignId('contestant_two_id')->nullable()->constrained('contestants')->nullOnDelete();
            $table->foreignId('winner_id')->nullable()->constrained('contestants')->nullOnDelete();
            $table->boolean('decided_by_coin_flip')->default(false);
            $table->timestamp('opens_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            $table->timestamps();

            $table->unique(['bracket_id', 'round', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matchups');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matchup_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contestant_id')->constrained()->cascadeOnDelete();
            $table->string('voter_hash', 64);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['matchup_id', 'voter_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};

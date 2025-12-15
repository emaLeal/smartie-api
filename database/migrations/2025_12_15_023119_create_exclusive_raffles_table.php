<?php

use App\Models\Participant;
use App\Models\Raffles;
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
        Schema::create('exclusive_raffles', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Participant::class);
            $table->foreignIdFor(Raffles::class);
            $table->unique(['participant_id', 'raffles_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exclusive_raffles');
    }
};

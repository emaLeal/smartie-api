<?php

use App\Models\Events;
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
        Schema::create('raffles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->boolean('is_played')->default(false);
            $table->string('price');
            $table->string('price_photo');
            $table->boolean('has_questions');
            $table->foreignId('winner_id')->constrained(
                table: 'participants',
                indexName: 'winner_id_in_raffle'
            );
            $table->string('winner_name')->nullable();
            $table->foreignIdFor(Events::class);
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raffles');
    }
};

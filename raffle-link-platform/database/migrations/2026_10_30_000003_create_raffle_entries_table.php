<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raffle_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('raffle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('raffle_link_id')->constrained('raffle_links')->cascadeOnDelete();
            $table->string('full_name', 120);
            $table->string('phone', 16);
            $table->timestamps();

            // The database, not PHP, is the source of truth for duplicate control.
            $table->unique(['raffle_id', 'phone'], 'raffle_entries_raffle_phone_unique');
            $table->index(['raffle_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raffle_entries');
    }
};

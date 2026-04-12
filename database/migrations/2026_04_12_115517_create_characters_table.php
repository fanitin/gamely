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
        Schema::create('characters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('igdb_id')->unique();
            $table->string('name')->index();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('country_name')->nullable();
            $table->json('akas')->nullable();
            $table->unsignedBigInteger('mug_shot_igdb_id')->nullable();
            $table->string('mug_shot_url')->nullable();
            $table->foreignId('gender_id')->nullable()->constrained('character_genders')->nullOnDelete();
            $table->foreignId('species_id')->nullable()->constrained('character_species')->nullOnDelete();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('characters');
    }
};

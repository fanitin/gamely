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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('igdb_id')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->integer('release_year')->nullable();
            $table->decimal('rating', 6, 2)->nullable();
            $table->integer('rating_count')->nullable();
            $table->decimal('total_rating', 6, 2)->nullable();
            $table->integer('total_rating_count')->nullable();
            $table->unsignedBigInteger('cover_igdb_id')->nullable();
            $table->string('cover_url')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};

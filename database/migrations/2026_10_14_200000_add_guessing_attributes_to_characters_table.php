<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('character_genders', function (Blueprint $table) {
            $table->unsignedBigInteger('igdb_id')->nullable()->change();
        });

        Schema::table('character_species', function (Blueprint $table) {
            $table->unsignedBigInteger('igdb_id')->nullable()->change();
        });

        Schema::table('characters', function (Blueprint $table) {
            $table->unsignedBigInteger('igdb_id')->nullable()->change();

            $table->string('full_name')->nullable()->after('name');
            $table->unsignedSmallInteger('debut_year')->nullable()->after('species_id');

            $table->string('role', 32)->nullable()->after('debut_year');
            $table->string('alignment', 32)->nullable()->after('role');
            $table->string('hair_color', 32)->nullable()->after('alignment');
            $table->string('eye_color', 32)->nullable()->after('hair_color');
            $table->string('weapon_type', 32)->nullable()->after('eye_color');
            $table->string('combat_range', 32)->nullable()->after('weapon_type');
            $table->string('age_group', 32)->nullable()->after('combat_range');
            $table->string('status', 32)->nullable()->after('age_group');
            $table->string('primary_color', 32)->nullable()->after('status');
            $table->string('build', 32)->nullable()->after('primary_color');
            $table->string('setting', 32)->nullable()->after('build');
            $table->string('origin_medium', 32)->nullable()->after('setting');
            $table->string('headwear', 32)->nullable()->after('origin_medium');

            $table->unsignedSmallInteger('age')->nullable()->after('headwear');
            $table->unsignedSmallInteger('height_cm')->nullable()->after('age');
            $table->unsignedSmallInteger('weight_kg')->nullable()->after('height_cm');

            $table->string('occupation')->nullable()->after('weight_kg');
            $table->string('affiliation')->nullable()->after('occupation');
            $table->string('origin')->nullable()->after('affiliation');
            $table->string('signature_item')->nullable()->after('origin');

            $table->boolean('is_playable')->nullable()->after('signature_item');
            $table->boolean('is_silent')->nullable()->after('is_playable');
            $table->boolean('is_mascot')->nullable()->after('is_silent');

            $table->json('abilities')->nullable()->after('is_mascot');
            $table->string('voice_actor')->nullable()->after('abilities');
            $table->string('creator')->nullable()->after('voice_actor');
            $table->string('birthday', 32)->nullable()->after('creator');
            $table->text('catchphrase')->nullable()->after('birthday');
            $table->string('image_url', 1024)->nullable()->after('catchphrase');
            $table->string('source_url', 1024)->nullable()->after('image_url');

            $table->index('role');
            $table->index('hair_color');
            $table->index('weapon_type');
        });
    }

    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['hair_color']);
            $table->dropIndex(['weapon_type']);

            $table->dropColumn([
                'full_name', 'debut_year', 'role', 'alignment', 'hair_color', 'eye_color', 'weapon_type',
                'combat_range', 'age_group', 'status', 'primary_color', 'build', 'setting', 'origin_medium',
                'headwear', 'age', 'height_cm', 'weight_kg', 'occupation', 'affiliation', 'origin',
                'signature_item', 'is_playable', 'is_silent', 'is_mascot', 'abilities', 'voice_actor',
                'creator', 'birthday', 'catchphrase', 'image_url', 'source_url',
            ]);
        });
    }
};

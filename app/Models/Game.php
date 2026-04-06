<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Game extends Model
{
    protected $fillable = [
        'igdb_id',
        'name',
        'slug',
        'summary',
        'release_year',
        'rating',
        'rating_count',
        'collection_id',
        'cover_igdb_id',
        'cover_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'release_year' => 'integer',
            'rating' => 'decimal:2',
            'rating_count' => 'integer',
        ];
    }

    public function collection(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function similarGames(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_similar_game', 'game_id', 'similar_game_id');
    }

    public function franchises(): BelongsToMany
    {
        return $this->belongsToMany(Franchise::class, 'game_franchise');
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    public function platforms(): BelongsToMany
    {
        return $this->belongsToMany(Platform::class);
    }

    public function themes(): BelongsToMany
    {
        return $this->belongsToMany(Theme::class);
    }

    public function gameModes(): BelongsToMany
    {
        return $this->belongsToMany(GameMode::class);
    }

    public function playerPerspectives(): BelongsToMany
    {
        return $this->belongsToMany(PlayerPerspective::class);
    }
}

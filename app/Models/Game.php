<?php

namespace App\Models;

use App\Enums\GameType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    protected $fillable = [
        'igdb_id',
        'name',
        'slug',
        'summary',
        'storyline',
        'release_date',
        'rating',
        'rating_count',
        'game_type',
        'cover_igdb_id',
        'cover_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'release_date' => 'date',
            'rating' => 'decimal:2',
            'rating_count' => 'integer',
            'game_type' => GameType::class,
        ];
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'game_collection');
    }

    public function similarGames(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_similar_game', 'game_id', 'similar_game_id');
    }

    public function franchises(): BelongsToMany
    {
        return $this->belongsToMany(Franchise::class, 'game_franchise');
    }

    public function developers(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'game_developer');
    }

    public function publishers(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'game_publisher');
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

    public function artworks(): HasMany
    {
        return $this->hasMany(GameArtwork::class);
    }

    public function screenshots(): HasMany
    {
        return $this->hasMany(GameScreenshot::class);
    }
}

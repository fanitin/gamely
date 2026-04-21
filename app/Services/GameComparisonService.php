<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Character;

class GameComparisonService
{
    public function compareGames(Game $guessed, Game $target): array
    {
        return [
            'release_year' => $this->compareReleaseYear($guessed->release_date, $target->release_date),
            'rating' => $this->compareRating($guessed->rating, $target->rating),
            'genres' => $this->compareGenres($guessed, $target),
            'platforms' => $this->comparePlatforms($guessed, $target),
            'developers' => $this->compareDevelopers($guessed, $target),
            'publishers' => $this->comparePublishers($guessed, $target),
        ];
    }

    public function compareCharacters(Character $guessed, Character $target): array
    {
        return [
            'gender' => $this->compareGender($guessed, $target),
            'species' => $this->compareSpecies($guessed, $target),
            'games' => $this->compareCharacterGames($guessed, $target),
        ];
    }

    private function compareReleaseYear(?string $guessedDate, ?string $targetDate): array
    {
        if (!$guessedDate || !$targetDate) {
            return [
                'result' => 'wrong',
                'value' => null,
            ];
        }

        $guessYear = (int) substr($guessedDate, 0, 4);
        $targetYear = (int) substr($targetDate, 0, 4);

        if ($guessYear === $targetYear) {
            return [
                'result' => 'exact',
                'value' => $targetYear,
            ];
        }

        $diff = abs($guessYear - $targetYear);
        $arrow = $guessYear < $targetYear ? 'up' : 'down';

        if ($diff <= 3) {
            return [
                'result' => 'close',
                'value' => $guessYear,
                'arrow' => $arrow,
            ];
        }

        return [
            'result' => 'wrong',
            'value' => $guessYear,
            'arrow' => $arrow,
        ];
    }

    private function compareRating(?float $guessed, ?float $target): array
    {
        if ($guessed === null || $target === null) {
            return [
                'result' => 'wrong',
                'value' => null,
            ];
        }

        $diff = abs($guessed - $target);

        if ($diff <= 0.5) {
            return [
                'result' => 'exact',
                'value' => round($guessed, 1),
            ];
        }

        $arrow = $guessed < $target ? 'up' : 'down';

        if ($diff <= 1.5) {
            return [
                'result' => 'close',
                'value' => round($guessed, 1),
                'arrow' => $arrow,
            ];
        }

        return [
            'result' => 'wrong',
            'value' => round($guessed, 1),
            'arrow' => $arrow,
        ];
    }

    private function compareGenres(Game $guessed, Game $target): array
    {
        $guessedIds = $guessed->genres->pluck('id')->toArray();
        $targetIds = $target->genres->pluck('id')->toArray();

        if (empty($guessedIds) || empty($targetIds)) {
            return ['result' => 'wrong'];
        }

        sort($guessedIds);
        sort($targetIds);

        if ($guessedIds === $targetIds) {
            return ['result' => 'exact'];
        }

        $intersection = array_intersect($guessedIds, $targetIds);
        if (!empty($intersection)) {
            return ['result' => 'close'];
        }

        return ['result' => 'wrong'];
    }

    private function comparePlatforms(Game $guessed, Game $target): array
    {
        $guessedIds = $guessed->platforms->pluck('id')->toArray();
        $targetIds = $target->platforms->pluck('id')->toArray();

        if (empty($guessedIds) || empty($targetIds)) {
            return ['result' => 'wrong'];
        }

        sort($guessedIds);
        sort($targetIds);

        if ($guessedIds === $targetIds) {
            return ['result' => 'exact'];
        }

        $intersection = array_intersect($guessedIds, $targetIds);
        if (!empty($intersection)) {
            return ['result' => 'close'];
        }

        return ['result' => 'wrong'];
    }

    private function compareDevelopers(Game $guessed, Game $target): array
    {
        $guessedIds = $guessed->developers->pluck('id')->toArray();
        $targetIds = $target->developers->pluck('id')->toArray();

        if (empty($guessedIds) || empty($targetIds)) {
            return ['result' => 'wrong'];
        }

        $intersection = array_intersect($guessedIds, $targetIds);
        if (!empty($intersection)) {
            return ['result' => 'exact'];
        }

        return ['result' => 'wrong'];
    }

    private function comparePublishers(Game $guessed, Game $target): array
    {
        $guessedIds = $guessed->publishers->pluck('id')->toArray();
        $targetIds = $target->publishers->pluck('id')->toArray();

        if (empty($guessedIds) || empty($targetIds)) {
            return ['result' => 'wrong'];
        }

        $intersection = array_intersect($guessedIds, $targetIds);
        if (!empty($intersection)) {
            return ['result' => 'exact'];
        }

        return ['result' => 'wrong'];
    }

    private function compareGender(Character $guessed, Character $target): array
    {
        if ($guessed->gender_id === null && $target->gender_id === null) {
            return ['result' => 'exact'];
        }

        if ($guessed->gender_id === null || $target->gender_id === null) {
            return ['result' => 'wrong'];
        }

        if ($guessed->gender_id === $target->gender_id) {
            return ['result' => 'exact'];
        }

        return ['result' => 'wrong'];
    }

    private function compareSpecies(Character $guessed, Character $target): array
    {
        if ($guessed->species_id === null && $target->species_id === null) {
            return ['result' => 'exact'];
        }

        if ($guessed->species_id === null || $target->species_id === null) {
            return ['result' => 'wrong'];
        }

        if ($guessed->species_id === $target->species_id) {
            return ['result' => 'exact'];
        }

        return ['result' => 'wrong'];
    }

    private function compareCharacterGames(Character $guessed, Character $target): array
    {
        $guessedIds = $guessed->games->pluck('id')->toArray();
        $targetIds = $target->games->pluck('id')->toArray();

        if (empty($guessedIds) || empty($targetIds)) {
            return ['result' => 'wrong'];
        }

        $intersection = array_intersect($guessedIds, $targetIds);
        if (!empty($intersection)) {
            return ['result' => 'exact'];
        }

        return ['result' => 'wrong'];
    }
}

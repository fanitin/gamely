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
            'developers_publishers' => $this->compareDevelopersAndPublishers($guessed, $target),
            'franchises_collections' => $this->compareFranchisesAndCollections($guessed, $target),
            'game_modes' => $this->compareGameModes($guessed, $target),
            'player_perspectives' => $this->comparePlayerPerspectives($guessed, $target),
        ];
    }

    public function compareCharacters(Character $guessed, Character $target): array
    {
        return [
            'gender' => $this->compareGender($guessed, $target),
            'species' => $this->compareSpecies($guessed, $target),
            'first_appearance_year' => $this->compareFirstAppearanceYear($guessed, $target),
            'franchises' => $this->compareCharacterFranchises($guessed, $target),
            'collections' => $this->compareCharacterCollections($guessed, $target),
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

    private function compareDevelopersAndPublishers(Game $guessed, Game $target): array
    {
        $guessedDevs = $guessed->developers->pluck('id')->toArray();
        $targetDevs = $target->developers->pluck('id')->toArray();

        $guessedPubs = $guessed->publishers->pluck('id')->toArray();
        $targetPubs = $target->publishers->pluck('id')->toArray();

        sort($guessedDevs);
        sort($targetDevs);
        sort($guessedPubs);
        sort($targetPubs);

        if ($guessedDevs === $targetDevs && $guessedPubs === $targetPubs) {
            return ['result' => 'exact'];
        }

        $devIntersection = array_intersect($guessedDevs, $targetDevs);
        $pubIntersection = array_intersect($guessedPubs, $targetPubs);

        if (!empty($devIntersection) || !empty($pubIntersection)) {
            return ['result' => 'close'];
        }

        return ['result' => 'wrong'];
    }

    private function compareFranchisesAndCollections(Game $guessed, Game $target): array
    {
        $guessedFranchises = $guessed->franchises->pluck('id')->toArray();
        $targetFranchises = $target->franchises->pluck('id')->toArray();

        $guessedCollections = $guessed->collections->pluck('id')->toArray();
        $targetCollections = $target->collections->pluck('id')->toArray();

        sort($guessedFranchises);
        sort($targetFranchises);
        sort($guessedCollections);
        sort($targetCollections);

        if ($guessedFranchises === $targetFranchises && $guessedCollections === $targetCollections) {
            return ['result' => 'exact'];
        }

        $franchiseIntersection = array_intersect($guessedFranchises, $targetFranchises);
        $collectionIntersection = array_intersect($guessedCollections, $targetCollections);


        if (!empty($franchiseIntersection) || !empty($collectionIntersection)) {
            return ['result' => 'close'];
        }

        return ['result' => 'wrong'];
    }

    private function compareGameModes(Game $guessed, Game $target): array
    {
        $guessedModes = $guessed->gameModes->pluck('id')->toArray();
        $targetModes = $target->gameModes->pluck('id')->toArray();

        if (empty($guessedModes) || empty($targetModes)) {
            return ['result' => 'wrong'];
        }

        sort($guessedModes);
        sort($targetModes);

        if ($guessedModes === $targetModes) {
            return ['result' => 'exact'];
        }

        $intersection = array_intersect($guessedModes, $targetModes);
        if (!empty($intersection)) {
            return ['result' => 'close'];
        }

        return ['result' => 'wrong'];
    }

    private function comparePlayerPerspectives(Game $guessed, Game $target): array
    {
        $guessedPerspectives = $guessed->playerPerspectives->pluck('id')->toArray();
        $targetPerspectives = $target->playerPerspectives->pluck('id')->toArray();

        if (empty($guessedPerspectives) || empty($targetPerspectives)) {
            return ['result' => 'wrong'];
        }

        sort($guessedPerspectives);
        sort($targetPerspectives);

        if ($guessedPerspectives === $targetPerspectives) {
            return ['result' => 'exact'];
        }

        $intersection = array_intersect($guessedPerspectives, $targetPerspectives);
        if (!empty($intersection)) {
            return ['result' => 'close'];
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

    private function compareFirstAppearanceYear(Character $guessed, Character $target): array
    {
        $guessedYear = $guessed->firstAppearanceYear();
        $targetYear = $target->firstAppearanceYear();

        if ($guessedYear === null || $targetYear === null) {
            return [
                'result' => 'wrong',
                'value' => $guessedYear,
            ];
        }

        if ($guessedYear === $targetYear) {
            return [
                'result' => 'exact',
                'value' => $guessedYear,
            ];
        }

        $diff = abs($guessedYear - $targetYear);
        $arrow = $guessedYear < $targetYear ? 'up' : 'down';

        if ($diff <= 3) {
            return [
                'result' => 'close',
                'value' => $guessedYear,
                'arrow' => $arrow,
            ];
        }

        return [
            'result' => 'wrong',
            'value' => $guessedYear,
            'arrow' => $arrow,
        ];
    }

    private function compareCharacterFranchises(Character $guessed, Character $target): array
    {
        $guessedFranchises = $guessed->franchises()->pluck('id')->toArray();
        $targetFranchises = $target->franchises()->pluck('id')->toArray();

        if (empty($guessedFranchises) && empty($targetFranchises)) {
            return ['result' => 'exact'];
        }

        if (empty($guessedFranchises) || empty($targetFranchises)) {
            return ['result' => 'wrong'];
        }

        sort($guessedFranchises);
        sort($targetFranchises);

        if ($guessedFranchises === $targetFranchises) {
            return ['result' => 'exact'];
        }

        $intersection = array_intersect($guessedFranchises, $targetFranchises);
        if (!empty($intersection)) {
            return ['result' => 'close'];
        }

        return ['result' => 'wrong'];
    }

    private function compareCharacterCollections(Character $guessed, Character $target): array
    {
        $guessedCollections = $guessed->collections()->pluck('id')->toArray();
        $targetCollections = $target->collections()->pluck('id')->toArray();

        if (empty($guessedCollections) && empty($targetCollections)) {
            return ['result' => 'exact'];
        }

        if (empty($guessedCollections) || empty($targetCollections)) {
            return ['result' => 'wrong'];
        }

        sort($guessedCollections);
        sort($targetCollections);

        if ($guessedCollections === $targetCollections) {
            return ['result' => 'exact'];
        }

        $intersection = array_intersect($guessedCollections, $targetCollections);
        if (!empty($intersection)) {
            return ['result' => 'close'];
        }

        return ['result' => 'wrong'];
    }
}

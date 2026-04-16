<?php

namespace App\Services;

class GuessService
{
    public function makeGuess(int $entityId, string $mode)
    {
        if(empty($entityId) || empty($mode)) {
            return false;
        }


    }

    private function guessGame(int $gameId)
    {

    }
}

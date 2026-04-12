<?php

namespace App\Console\Commands\IGDB;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('import:igdb-all')]
#[Description('Execute all IGDB import commands in the correct order')]
class ImportAll extends Command
{
    public function handle(): int
    {
        $commands = [
            ['signature' => 'import:igdb-platform-families'],
            ['signature' => 'import:igdb-platforms'],
            ['signature' => 'import:igdb-genres'],
            ['signature' => 'import:igdb-game-modes'],
            ['signature' => 'import:igdb-player-perspectives'],
            ['signature' => 'import:igdb-themes'],
            ['signature' => 'import:igdb-collections'],
            ['signature' => 'import:igdb-franchises'],
            ['signature' => 'import:igdb-character-genders'],
            ['signature' => 'import:igdb-character-species'],
            ['signature' => 'import:igdb-games'],
            ['signature' => 'import:igdb-characters'],
            ['signature' => 'import:igdb-similar-games'],
            ['signature' => 'import:covers', 'arguments' => ['--limit' => 10]],
            ['signature' => 'import:artworks', 'arguments' => ['--limit' => 10]],
            ['signature' => 'import:screenshots', 'arguments' => ['--limit' => 10]],
            ['signature' => 'import:character-mug-shots', 'arguments' => ['--limit' => 10]],
        ];

        $this->info('Starting full IGDB import process...');

        foreach ($commands as $command) {
            $signature = $command['signature'];
            $arguments = $command['arguments'] ?? [];

            $this->info("Running: {$signature}");

            $exitCode = $this->call($signature, $arguments);

            if ($exitCode !== 0) {
                $this->error("\nCommand {$signature} failed with exit code {$exitCode}. Aborting.");

                return static::FAILURE;
            }

            $this->line('');
        }

        $this->info('Full IGDB import completed successfully!');

        return static::SUCCESS;
    }
}

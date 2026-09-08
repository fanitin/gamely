<?php

namespace App\Console\Commands\IGDB;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('import:igdb-all {--limit= : Limit the number of records processed by each media import}')]
#[Description('Execute all IGDB import commands in the correct order')]
class ImportAll extends Command
{
    public function handle(): int
    {
        $mediaArguments = $this->option('limit')
            ? ['--limit' => (int) $this->option('limit')]
            : [];

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
            ['signature' => 'import:covers', 'arguments' => $mediaArguments],
            ['signature' => 'import:artworks', 'arguments' => $mediaArguments],
            ['signature' => 'import:screenshots', 'arguments' => $mediaArguments],
            ['signature' => 'import:character-mug-shots', 'arguments' => $mediaArguments],
            ['signature' => 'games:recalculate-meta'],
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

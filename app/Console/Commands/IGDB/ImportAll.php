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
            'import:igdb-platform-families',
            'import:igdb-platforms',
            'import:igdb-genres',
            'import:igdb-game-modes',
            'import:igdb-player-perspectives',
            'import:igdb-themes',
            'import:igdb-collections',
            'import:igdb-franchises',
            'import:igdb-games',
            'import:igdb-similar-games',
            'import:covers',
        ];

        $this->info('Starting full IGDB import process...');

        foreach ($commands as $command) {
            $this->info("Running: {$command}");

            $exitCode = $this->call($command);

            if ($exitCode !== 0) {
                $this->error("\nCommand {$command} failed with exit code {$exitCode}. Aborting.");

                return static::FAILURE;
            }

            $this->line('');
        }

        $this->info('Full IGDB import completed successfully!');

        return static::SUCCESS;
    }
}

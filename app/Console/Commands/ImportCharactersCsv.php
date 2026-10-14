<?php

namespace App\Console\Commands;

use App\Models\Character;
use App\Models\Game;
use App\Services\CharacterCsvService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('characters:import {file : Path to the CSV file} {--dry-run : Validate the file and report what would change without writing}')]
#[Description('Import or update characters and their game links from a CSV file')]
class ImportCharactersCsv extends Command
{
    private const MAX_WARNINGS_SHOWN = 50;

    public function __construct(private CharacterCsvService $csv)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $path = $this->argument('file');
        $dryRun = (bool) $this->option('dry-run');

        if (! is_readable($path)) {
            $this->error("File not found or not readable: {$path}");

            return self::FAILURE;
        }

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);

        if (! $header) {
            $this->error('CSV file is empty.');

            return self::FAILURE;
        }

        $header = array_map(fn ($h) => mb_strtolower(trim((string) $h, " \t\n\r\0\x0B\xEF\xBB\xBF")), $header);
        $missing = array_diff(CharacterCsvService::REQUIRED_COLUMNS, $header);

        if ($missing !== []) {
            $this->error('Missing required columns: '.implode(', ', $missing));

            return self::FAILURE;
        }

        $unknown = array_diff($header, $this->csv->headers());

        if ($unknown !== []) {
            $this->warn('Ignoring unknown columns: '.implode(', ', $unknown));
        }

        $gameMap = Game::pluck('id', 'igdb_id')->toArray();
        $stats = ['created' => 0, 'updated' => 0, 'unchanged' => 0, 'skipped' => 0, 'errors' => 0];
        $warnings = [];
        $line = 1;

        $this->info(($dryRun ? '[DRY RUN] ' : '')."Importing characters from {$path}...");

        while (($values = fgetcsv($handle)) !== false) {
            $line++;

            if ($values === [null] || count(array_filter($values, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $row = array_combine($header, array_pad(array_slice($values, 0, count($header)), count($header), null));

            try {
                $parsed = $this->csv->parseRow($row);

                foreach ($parsed['warnings'] as $warning) {
                    $warnings[] = "Line {$line}: {$warning}";
                }

                $gameIds = [];
                foreach ($parsed['game_igdb_ids'] as $igdbId) {
                    if (isset($gameMap[$igdbId])) {
                        $gameIds[] = $gameMap[$igdbId];
                    } else {
                        $warnings[] = "Line {$line}: game igdb_id {$igdbId} not found locally";
                    }
                }

                if ($gameIds === []) {
                    $warnings[] = "Line {$line}: no known games, row skipped";
                    $stats['skipped']++;

                    continue;
                }

                if ($parsed['slug'] === null || empty($parsed['attributes']['name'])) {
                    $warnings[] = "Line {$line}: slug or name missing, row skipped";
                    $stats['skipped']++;

                    continue;
                }

                $character = $this->findExisting($parsed);
                $attributes = $parsed['attributes'];

                if ($parsed['igdb_id'] !== null) {
                    $attributes['igdb_id'] = $parsed['igdb_id'];
                }

                if ($character === null) {
                    $attributes['slug'] = $parsed['slug'];
                    $stats['created']++;

                    if (! $dryRun) {
                        $character = Character::create($attributes);
                        $character->games()->syncWithoutDetaching($gameIds);
                    }

                    continue;
                }

                $character->fill($attributes);
                $changed = $character->isDirty();

                if (! $dryRun) {
                    $character->save();
                    $attached = $character->games()->syncWithoutDetaching($gameIds);
                    $changed = $changed || ! empty($attached['attached']);
                }

                $stats[$changed ? 'updated' : 'unchanged']++;
            } catch (Throwable $e) {
                $stats['errors']++;
                $warnings[] = "Line {$line}: ".$e->getMessage();
            }
        }

        fclose($handle);

        foreach (array_slice($warnings, 0, self::MAX_WARNINGS_SHOWN) as $warning) {
            $this->warn($warning);
        }

        if (count($warnings) > self::MAX_WARNINGS_SHOWN) {
            $this->warn('... and '.(count($warnings) - self::MAX_WARNINGS_SHOWN).' more warnings');
        }

        $this->newLine();
        $this->info(sprintf(
            '%sCreated: %d | Updated: %d | Unchanged: %d | Skipped: %d | Errors: %d | Warnings: %d',
            $dryRun ? '[DRY RUN] ' : '',
            $stats['created'], $stats['updated'], $stats['unchanged'], $stats['skipped'], $stats['errors'], count($warnings)
        ));

        return $stats['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function findExisting(array $parsed): ?Character
    {
        if ($parsed['id'] !== null) {
            $character = Character::find($parsed['id']);

            if ($character) {
                return $character;
            }
        }

        if ($parsed['igdb_id'] !== null) {
            $character = Character::where('igdb_id', $parsed['igdb_id'])->first();

            if ($character) {
                return $character;
            }
        }

        return Character::where('slug', $parsed['slug'])->first();
    }
}

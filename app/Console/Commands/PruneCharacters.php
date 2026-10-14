<?php

namespace App\Console\Commands;

use App\Models\Character;
use App\Models\DailyChallenge;
use App\Services\CharacterQualityService;
use App\Services\MediaService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('characters:prune
    {--dry-run : Report what would be deleted without deleting}
    {--tier=* : Popularity tiers of games that keep a character (default: legendary, popular)}')]
#[Description('Delete junk characters (credits, numbered NPCs, generic roles) and characters without popular games, including their R2 mug shots')]
class PruneCharacters extends Command
{
    private const EXAMPLES_PER_REASON = 15;

    public function __construct(
        private CharacterQualityService $quality,
        private MediaService $media,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $tiers = $this->quality->resolveTiers((array) $this->option('tier'));

        if ($tiers === null) {
            $this->error('Unknown tier. Allowed: legendary, popular, notable, niche');

            return self::FAILURE;
        }

        $protectedIds = DailyChallenge::whereNotNull('character_id')->pluck('character_id')->flip();
        $stats = ['kept' => 0, 'junk' => 0, 'unpopular' => 0, 'media_deleted' => 0, 'errors' => 0];
        $examples = ['junk' => [], 'unpopular' => []];

        $this->info(($dryRun ? '[DRY RUN] ' : '').'Pruning characters, keeping tiers: '.implode(', ', array_map(fn ($t) => $t->value, $tiers)));

        Character::query()
            ->with('games:games.id,popularity_tier')
            ->chunkById(500, function ($characters) use ($dryRun, $tiers, $protectedIds, &$stats, &$examples) {
                foreach ($characters as $character) {
                    $reason = $this->pruneReason($character, $tiers, $protectedIds);

                    if ($reason === null) {
                        $stats['kept']++;

                        continue;
                    }

                    $stats[$reason]++;

                    if (count($examples[$reason]) < self::EXAMPLES_PER_REASON) {
                        $examples[$reason][] = $character->name;
                    }

                    if ($dryRun) {
                        continue;
                    }

                    try {
                        if ($character->getRawOriginal('mug_shot_url')) {
                            $this->media->deleteCharacterMugShot($character->getRawOriginal('mug_shot_url'));
                            $stats['media_deleted']++;
                        }

                        $character->delete();
                    } catch (Throwable $e) {
                        $stats['errors']++;
                        $this->error("\n[Error] Character #{$character->id} {$character->name}: ".$e->getMessage());
                    }
                }
            });

        foreach ($examples as $reason => $names) {
            if ($names !== []) {
                $this->line(ucfirst($reason).' examples: '.implode(' | ', $names));
            }
        }

        $this->newLine();
        $this->info(sprintf(
            '%sKept: %d | Junk: %d | Unpopular: %d | Mug shots deleted: %d | Errors: %d',
            $dryRun ? '[DRY RUN] ' : '',
            $stats['kept'], $stats['junk'], $stats['unpopular'], $stats['media_deleted'], $stats['errors']
        ));

        return $stats['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function pruneReason(Character $character, array $tiers, $protectedIds): ?string
    {
        if ($protectedIds->has($character->id) || $character->mug_shot_igdb_id !== null) {
            return null;
        }

        if ($this->quality->isJunk($character->name)) {
            return 'junk';
        }

        if (! $this->quality->hasPopularGame($character->games, $tiers)) {
            return 'unpopular';
        }

        return null;
    }
}

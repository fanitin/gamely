<?php

namespace App\Services;

use App\Enums\PopularityTier;

class CharacterQualityService
{
    public const DEFAULT_TIERS = [PopularityTier::LEGENDARY, PopularityTier::POPULAR];

    private const CREDITS_PATTERN = '/\b(voice|voices|voice[- ]?overs?|narrator|narration|announcer|talent|actor|actress|actors|cast|vocals?|chorus|choir|singers?|performer|stunts?|mocap|motion capture|extras?|ensemble|uncredited|dialogue|dialog|dubbing)\b/u';

    private const INDEXED_PATTERN = '/\s(#\d{1,3}|0\d{1,2})$/u';

    private const TRAILING_NUMBER_PATTERN = '/^(.*?)\s\d{1,3}$/u';

    private const QUALIFIERS = [
        'male', 'female', 'young', 'old', 'little', 'big', 'tall', 'short', 'fat', 'thin', 'additional', 'generic',
        'unnamed', 'random', 'misc', 'various', 'the', 'a', 'an', 'of', 'in', 'at', 'on', 'and', 'with', 'to',
        'mr', 'mrs', 'ms', 'miss', 'sir', 'adult', 'elderly', 'teen', 'teenage', 'angry', 'drunk', 'scared',
        'injured', 'wounded', 'dead', 'dying', 'armed', 'masked', 'hooded', 'local', 'rich', 'poor',
        'english', 'american', 'british', 'french', 'german', 'spanish', 'russian', 'japanese', 'chinese',
        'korean', 'italian', 'polish', 'portuguese', 'brazilian', 'mexican', 'arabic', 'arab', 'hindi', 'indian',
        'african', 'asian', 'european', 'latino', 'irish', 'scottish', 'australian', 'canadian', 'swedish',
        'dutch', 'turkish', 'greek', 'vietnamese', 'thai', 'cuban', 'jamaican', 'tibetan', 'afghan', 'iraqi',
        'accented', 'ped', 'peds', 'pc', 'npc', 'npcs', 'in', 'game', 'ingame', 'multiplayer', 'campaign',
    ];

    private const GENERIC_ROLES = [
        'soldier', 'soldiers', 'solder', 'guard', 'guards', 'civilian', 'civilians', 'pedestrian', 'pedestrians',
        'citizen', 'citizens', 'villager', 'villagers', 'townsperson', 'townsfolk', 'resident', 'residents',
        'inhabitant', 'inhabitants', 'tourist', 'tourists', 'shopper', 'shoppers', 'customer', 'customers',
        'crowd', 'audience', 'spectator', 'spectators', 'passenger', 'passengers', 'driver', 'drivers',
        'unknown', 'player', 'players', 'character', 'characters', 'person', 'people', 'man', 'woman', 'men',
        'women', 'boy', 'boys', 'girl', 'girls', 'guy', 'guys', 'kid', 'kids', 'child', 'children', 'baby',
        'lady', 'gentleman', 'thug', 'thugs', 'goon', 'goons', 'henchman', 'henchmen', 'minion', 'minions',
        'grunt', 'grunts', 'trooper', 'troopers', 'marine', 'marines', 'infantry', 'militia', 'rebel', 'rebels',
        'insurgent', 'insurgents', 'terrorist', 'terrorists', 'cultist', 'cultists', 'raider', 'raiders',
        'bandit', 'bandits', 'mercenary', 'mercenaries', 'gangster', 'gangsters', 'gang', 'mob', 'enemy',
        'enemies', 'hostage', 'hostages', 'prisoner', 'prisoners', 'inmate', 'inmates', 'convict', 'victim',
        'victims', 'corpse', 'body', 'survivor', 'survivors', 'merchant', 'merchants', 'vendor', 'vendors',
        'shopkeeper', 'shopkeep', 'trader', 'traders', 'bartender', 'barkeep', 'barman', 'barmaid', 'waiter',
        'waitress', 'clerk', 'receptionist', 'cashier', 'reporter', 'reporters', 'newscaster', 'anchorman',
        'anchorwoman', 'broadcaster', 'journalist', 'host', 'dj', 'radio', 'tv', 'dispatcher', 'operator',
        'computer', 'pilot', 'pilots', 'officer', 'officers', 'cop', 'cops', 'police', 'policeman',
        'policewoman', 'security', 'swat', 'nurse', 'nurses', 'doctor', 'doctors', 'medic', 'scientist',
        'scientists', 'engineer', 'mechanic', 'technician', 'worker', 'workers', 'employee', 'employees',
        'staff', 'farmer', 'farmers', 'peasant', 'peasants', 'priest', 'monk', 'nun', 'student', 'students',
        'teacher', 'professor', 'bodyguard', 'servant', 'servants', 'maid', 'butler', 'slave', 'slaves',
        'bouncer', 'stripper', 'prostitute', 'hooker', 'dancer', 'dancers', 'musician', 'drummer', 'guitarist',
        'commentator', 'tutorial', 'fan', 'fans', 'member', 'members', 'team', 'squad', 'unit', 'group',
        'horde', 'pack', 'hobo', 'homeless', 'beggar', 'junkie', 'dealer', 'patient', 'patients', 'sniper',
        'scout', 'spy', 'agent', 'agents', 'detective', 'inspector', 'chef', 'cook', 'baker', 'lawyer',
        'judge', 'mayor', 'elder', 'leader', 'manager', 'owner', 'secretary', 'assistant', 'courier',
        'messenger', 'pirate', 'pirates', 'outlaw', 'outlaws', 'thief', 'thieves', 'burglar', 'archer',
        'archers', 'knight', 'knights', 'warrior', 'warriors', 'fighter', 'fighters', 'zombie', 'zombies',
        'skeleton', 'skeletons', 'creature', 'creatures', 'monster', 'monsters',
    ];

    public function isJunk(string $name): bool
    {
        return $this->junkReason($name) !== null;
    }

    public function junkReason(string $name): ?string
    {
        $normalized = mb_strtolower(trim($name));

        if (mb_strlen($normalized) < 2 || ! preg_match('/\p{L}/u', $normalized)) {
            return 'empty';
        }

        if (preg_match(self::CREDITS_PATTERN, $normalized)) {
            return 'credits';
        }

        if (preg_match(self::INDEXED_PATTERN, $normalized)) {
            return 'indexed';
        }

        if (preg_match(self::TRAILING_NUMBER_PATTERN, $normalized, $matches) && $this->isGenericPhrase($matches[1])) {
            return 'indexed';
        }

        if ($this->isGenericPhrase($normalized)) {
            return 'generic';
        }

        return null;
    }

    private function isGenericPhrase(string $phrase): bool
    {
        $tokens = preg_split('/[^\p{L}\p{N}]+/u', $phrase, -1, PREG_SPLIT_NO_EMPTY);
        $meaningful = array_filter(array_diff($tokens, self::QUALIFIERS), fn ($token) => mb_strlen($token) > 1);

        return $meaningful === [] || array_diff($meaningful, self::GENERIC_ROLES) === [];
    }

    public function hasPopularGame(iterable $games, ?array $tiers = null): bool
    {
        $values = array_map(fn (PopularityTier $tier) => $tier->value, $tiers ?? self::DEFAULT_TIERS);

        foreach ($games as $game) {
            $tier = $game->popularity_tier instanceof PopularityTier ? $game->popularity_tier->value : $game->popularity_tier;

            if (in_array($tier, $values, true)) {
                return true;
            }
        }

        return false;
    }

    public function resolveTiers(array $inputs): ?array
    {
        if ($inputs === []) {
            return self::DEFAULT_TIERS;
        }

        $tiers = [];

        foreach ($inputs as $input) {
            $tier = PopularityTier::tryFrom(mb_strtolower(trim($input)));

            if (! $tier) {
                return null;
            }

            $tiers[] = $tier;
        }

        return $tiers;
    }
}

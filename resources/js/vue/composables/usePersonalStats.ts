import { useLocalStorage } from "@vueuse/core";

export type PersonalMode = "classic" | "game_screenshots" | "character";

type DailyAttempts = Record<string, Partial<Record<PersonalMode, number>>>;

export interface PersonalStatsModel {
    wins_total: number;
    attempts_sum: number;
    first_try_wins: number;
    current_streak_days: number;
    best_streak_days: number;
    last_win_date: string | null;
    wins_by_mode: Record<PersonalMode, number>;
    daily_attempts: DailyAttempts;
}

const STORAGE_KEY = "gamely_personal_stats";
const LEGACY_KEYS = ["classic_stats", "screenshots_stats", "character_stats"] as const;

const defaultStats = (): PersonalStatsModel => ({
    wins_total: 0,
    attempts_sum: 0,
    first_try_wins: 0,
    current_streak_days: 0,
    best_streak_days: 0,
    last_win_date: null,
    wins_by_mode: {
        classic: 0,
        game_screenshots: 0,
        character: 0,
    },
    daily_attempts: {},
});

const dateDiffInDays = (a: string, b: string): number => {
    const aDate = new Date(a);
    const bDate = new Date(b);
    return Math.round((aDate.getTime() - bDate.getTime()) / 86400000);
};

export function usePersonalStats() {
    const stats = useLocalStorage<PersonalStatsModel>(STORAGE_KEY, defaultStats());

    const migrateLegacyIfNeeded = () => {
        if (stats.value.wins_total > 0) {
            return;
        }

        let hasLegacy = false;
        for (const key of LEGACY_KEYS) {
            if (localStorage.getItem(key)) {
                hasLegacy = true;
                break;
            }
        }

        if (!hasLegacy) {
            return;
        }

        const modeMap: Record<(typeof LEGACY_KEYS)[number], PersonalMode> = {
            classic_stats: "classic",
            screenshots_stats: "game_screenshots",
            character_stats: "character",
        };

        const merged = defaultStats();
        for (const key of LEGACY_KEYS) {
            const raw = localStorage.getItem(key);
            if (!raw) continue;
            const parsed = JSON.parse(raw) as { wins?: number; distribution?: Record<string, number> };
            const mode = modeMap[key];
            const modeWins = parsed.wins ?? 0;
            merged.wins_total += modeWins;
            merged.wins_by_mode[mode] += modeWins;

            if (parsed.distribution) {
                Object.entries(parsed.distribution).forEach(([attempts, count]) => {
                    merged.attempts_sum += Number(attempts) * Number(count);
                    if (Number(attempts) === 1) {
                        merged.first_try_wins += Number(count);
                    }
                });
            }
        }

        stats.value = merged;
    };

    const recordWin = (mode: PersonalMode, attempts: number, date: string) => {
        const current = stats.value;
        const previousDayDiff = current.last_win_date ? dateDiffInDays(date, current.last_win_date) : null;
        const alreadyWonToday = current.last_win_date === date;

        current.wins_total += 1;
        current.attempts_sum += attempts;
        if (attempts === 1) {
            current.first_try_wins += 1;
        }
        current.wins_by_mode[mode] += 1;

        current.daily_attempts[date] = {
            ...(current.daily_attempts[date] ?? {}),
            [mode]: attempts,
        };

        if (!alreadyWonToday) {
            current.current_streak_days = previousDayDiff === 1 ? current.current_streak_days + 1 : 1;
            current.best_streak_days = Math.max(current.best_streak_days, current.current_streak_days);
            current.last_win_date = date;
        }

        stats.value = { ...current };
    };

    const getViewModel = () => {
        const current = stats.value;
        return {
            ...current,
            average_attempts: current.wins_total > 0 ? Number((current.attempts_sum / current.wins_total).toFixed(2)) : 0,
            first_try_rate: current.wins_total > 0 ? Number(((current.first_try_wins / current.wins_total) * 100).toFixed(2)) : 0,
        };
    };

    migrateLegacyIfNeeded();

    return {
        stats,
        recordWin,
        getViewModel,
    };
}

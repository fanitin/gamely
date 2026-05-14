import { useLocalStorage } from "@vueuse/core";
import { getLocalDateKey } from "@/vue/utils/date";

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

const parseDateKey = (date: string): Date | null => {
    const [year, month, day] = date.split("-").map(Number);
    if (!year || !month || !day) {
        return null;
    }
    return new Date(Date.UTC(year, month - 1, day));
};

const formatDateKey = (date: Date): string => {
    const year = date.getUTCFullYear();
    const month = String(date.getUTCMonth() + 1).padStart(2, "0");
    const day = String(date.getUTCDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
};

const dateDiffInDays = (a: string, b: string): number => {
    const aDate = parseDateKey(a);
    const bDate = parseDateKey(b);
    if (!aDate || !bDate) {
        return 0;
    }
    return Math.round((aDate.getTime() - bDate.getTime()) / 86400000);
};

const hasWinInDay = (day: Partial<Record<PersonalMode, number>> | undefined): boolean =>
    Object.values(day ?? {}).some((attempts) => Number(attempts) > 0);

const getWinDates = (dailyAttempts: DailyAttempts): string[] =>
    Object.keys(dailyAttempts)
        .filter((date) => hasWinInDay(dailyAttempts[date]))
        .sort((left, right) => left.localeCompare(right));

const calculateCurrentStreakDays = (dailyAttempts: DailyAttempts, todayKey: string): number => {
    const today = parseDateKey(todayKey);
    if (!today) {
        return 0;
    }

    let streak = 0;
    const cursor = new Date(today.getTime());

    while (true) {
        const dateKey = formatDateKey(cursor);
        if (!hasWinInDay(dailyAttempts[dateKey])) {
            break;
        }

        streak += 1;
        cursor.setUTCDate(cursor.getUTCDate() - 1);
    }

    return streak;
};

const calculateBestStreakDays = (dailyAttempts: DailyAttempts): number => {
    const dates = getWinDates(dailyAttempts);
    if (dates.length === 0) {
        return 0;
    }

    let best = 1;
    let current = 1;

    for (let index = 1; index < dates.length; index += 1) {
        const diff = dateDiffInDays(dates[index], dates[index - 1]);
        if (diff === 1) {
            current += 1;
            best = Math.max(best, current);
            continue;
        }
        current = 1;
    }

    return best;
};

export function usePersonalStats() {
    const stats = useLocalStorage<PersonalStatsModel>(STORAGE_KEY, defaultStats());

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

    const refreshDerivedStats = () => {
        const current = stats.value;
        const winDates = getWinDates(current.daily_attempts);
        const lastWinDate = winDates.length > 0 ? winDates[winDates.length - 1] : null;
        const currentStreakDays = calculateCurrentStreakDays(current.daily_attempts, getLocalDateKey());
        const bestStreakDays = Math.max(current.best_streak_days, calculateBestStreakDays(current.daily_attempts));

        const shouldUpdate =
            current.last_win_date !== lastWinDate ||
            current.current_streak_days !== currentStreakDays ||
            current.best_streak_days !== bestStreakDays;

        if (!shouldUpdate) {
            return;
        }

        stats.value = {
            ...current,
            last_win_date: lastWinDate,
            current_streak_days: currentStreakDays,
            best_streak_days: bestStreakDays,
        };
    };

    const getViewModel = () => {
        refreshDerivedStats();
        const current = stats.value;

        return {
            ...current,
            average_attempts: current.wins_total > 0 ? Number((current.attempts_sum / current.wins_total).toFixed(2)) : 0,
            first_try_rate: current.wins_total > 0 ? Number(((current.first_try_wins / current.wins_total) * 100).toFixed(2)) : 0,
        };
    };

    return {
        stats,
        recordWin,
        refreshDerivedStats,
        getViewModel,
    };
}

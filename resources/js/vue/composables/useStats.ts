import axios from "axios";
import { route } from "ziggy-js";

export type ModeValue = "classic" | "game_screenshots" | "character";

export interface ModeDistributionResponse {
    mode: ModeValue;
    date: string;
    total_players: number;
    average: number | null;
    bins: Array<{ attempts: number; players: number }>;
}

export interface SolvedTodayResponse {
    date: string;
    classic_players: number;
    screenshots_players: number;
    character_players: number;
    total_players: number;
}

export interface DailyTrendResponse {
    from: string;
    to: string;
    points: Array<{
        date: string;
        classic_avg_attempts: number | null;
        screenshots_avg_attempts: number | null;
        character_avg_attempts: number | null;
        aggregate_avg_attempts: number | null;
    }>;
}

export function useStats() {
    const getModeDistribution = async (mode: ModeValue, date?: string): Promise<ModeDistributionResponse> => {
        const response = await axios.get(route("api.stats.mode-distribution", { mode }), {
            params: date ? { date } : {},
        });
        return response.data;
    };

    const getDailyTrend = async (from?: string, to?: string): Promise<DailyTrendResponse> => {
        const response = await axios.get(route("api.stats.daily-trend"), {
            params: {
                ...(from ? { from } : {}),
                ...(to ? { to } : {}),
            },
        });
        return response.data;
    };

    const getSolvedToday = async (): Promise<SolvedTodayResponse> => {
        const response = await axios.get(route("api.stats.solved-today"));
        return response.data;
    };

    return {
        getModeDistribution,
        getDailyTrend,
        getSolvedToday,
    };
}

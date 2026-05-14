import { reactive, readonly } from "vue";

export type ToastType = "success" | "info" | "warning" | "error";

export interface ToastOptions {
    id?: string;
    type?: ToastType;
    title?: string;
    message: string;
    duration?: number;
    dedupeKey?: string;
    cooldownMs?: number;
}

export interface ToastItem {
    id: string;
    type: ToastType;
    title: string | null;
    message: string;
    duration: number;
}

const state = reactive({
    toasts: [] as ToastItem[],
});

const dedupeRegistry = new Map<string, number>();

const buildToastId = (): string => {
    return `${Date.now()}-${Math.random().toString(36).slice(2, 10)}`;
};

export const dismissToast = (id: string): void => {
    const index = state.toasts.findIndex((toast) => toast.id === id);
    if (index !== -1) {
        state.toasts.splice(index, 1);
    }
};

export const showToast = (options: ToastOptions): string => {
    const now = Date.now();
    const dedupeKey = options.dedupeKey ?? options.message;
    const cooldownMs = options.cooldownMs ?? 2500;
    const previousShownAt = dedupeRegistry.get(dedupeKey);

    if (previousShownAt && now - previousShownAt < cooldownMs) {
        return "";
    }

    dedupeRegistry.set(dedupeKey, now);

    const id = options.id ?? buildToastId();
    const duration = Math.max(options.duration ?? 4000, 1500);

    state.toasts.push({
        id,
        type: options.type ?? "info",
        title: options.title ?? null,
        message: options.message,
        duration,
    });

    window.setTimeout(() => {
        dismissToast(id);
    }, duration);

    return id;
};

export const clearToasts = (): void => {
    state.toasts.splice(0, state.toasts.length);
};

export function useToast() {
    return {
        toasts: readonly(state.toasts),
        showToast,
        dismissToast,
        clearToasts,
    };
}

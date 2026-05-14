import axios from 'axios';
import { showToast } from "@/vue/composables/useToast";

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const toNumber = (value) => {
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : null;
};

const getErrorMessage = (response, fallback) => {
    if (!response) {
        return fallback;
    }

    const payload = response.data;

    if (payload && typeof payload === "object") {
        if (typeof payload.error === "string" && payload.error.trim() !== "") {
            return payload.error;
        }

        if (typeof payload.message === "string" && payload.message.trim() !== "") {
            return payload.message;
        }
    }

    return fallback;
};

window.axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (!error.response) {
            showToast({
                type: "error",
                title: "Network error",
                message: "Unable to reach the server. Please check your connection.",
                dedupeKey: "network-error",
                cooldownMs: 5000,
            });

            return Promise.reject(error);
        }

        const { response, config } = error;
        const status = response.status;

        if (status === 429) {
            const message = getErrorMessage(response, "Too many requests. Please retry later.");
            const retryAfter =
                toNumber(response.data?.retry_after) ??
                toNumber(response.headers?.["retry-after"]);
            const retryPart = retryAfter && retryAfter > 0 ? ` Retry in ${retryAfter}s.` : "";

            showToast({
                type: "warning",
                title: "Rate limit",
                message: `${message}${retryPart}`,
                dedupeKey: `rate-limit:${config?.url ?? "unknown"}`,
                cooldownMs: 2000,
                duration: 5000,
            });

            return Promise.reject(error);
        }

        if (status >= 500) {
            showToast({
                type: "error",
                title: "Server error",
                message: getErrorMessage(response, "Server error. Please try again later."),
                dedupeKey: `server-error:${status}`,
                cooldownMs: 3000,
            });
        }

        return Promise.reject(error);
    },
);

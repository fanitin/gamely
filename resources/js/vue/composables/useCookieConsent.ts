import { reactive, computed, readonly } from "vue";

export type ConsentCategory = "necessary" | "analytics" | "ads";

export interface ConsentRecord {
    version: number;
    necessary: true;
    analytics: boolean;
    ads: boolean;
    updatedAt: string;
    locale: string;
}

const STORAGE_KEY = "gamely_cookie_consent";
const CONSENT_VERSION = 1;

const GA_MEASUREMENT_ID = import.meta.env.VITE_GA_MEASUREMENT_ID as
    | string
    | undefined;
const ADSENSE_CLIENT_ID = import.meta.env.VITE_ADSENSE_CLIENT_ID as
    | string
    | undefined;

declare global {
    interface Window {
        dataLayer: unknown[];
        gtag?: (...args: unknown[]) => void;
        adsbygoogle?: unknown[];
    }
}

function gtag(...args: unknown[]): void {
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push(args);
}

interface ConsentState {
    record: ConsentRecord | null;
    bannerOpen: boolean;
    modalOpen: boolean;
    loaded: { ga: boolean; adsense: boolean };
}

function readStored(): ConsentRecord | null {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        if (!raw) return null;
        const parsed = JSON.parse(raw) as ConsentRecord;
        if (!parsed || parsed.version !== CONSENT_VERSION) return null;
        return parsed;
    } catch {
        return null;
    }
}

const initial = readStored();

const state = reactive<ConsentState>({
    record: initial,
    bannerOpen: initial == null,
    modalOpen: false,
    loaded: { ga: false, adsense: false },
});

type ConsentValue = "granted" | "denied";

function toConsentMode(record: ConsentRecord | null) {
    const analytics: ConsentValue = record?.analytics ? "granted" : "denied";
    const ads: ConsentValue = record?.ads ? "granted" : "denied";
    return {
        analytics_storage: analytics,
        ad_storage: ads,
        ad_user_data: ads,
        ad_personalization: ads,
    };
}

function pushConsentUpdate(record: ConsentRecord | null): void {
    gtag("consent", "update", toConsentMode(record));
}

function loadScriptOnce(
    src: string,
    attrs: Record<string, string> = {},
): void {
    if (document.querySelector(`script[src="${src}"]`)) return;
    const s = document.createElement("script");
    s.src = src;
    s.async = true;
    Object.entries(attrs).forEach(([k, v]) => s.setAttribute(k, v));
    document.head.appendChild(s);
}

function loadAnalytics(): void {
    if (state.loaded.ga || !GA_MEASUREMENT_ID) return;
    loadScriptOnce(
        `https://www.googletagmanager.com/gtag/js?id=${GA_MEASUREMENT_ID}`,
    );
    gtag("js", new Date());
    gtag("config", GA_MEASUREMENT_ID, { anonymize_ip: true });
    state.loaded.ga = true;
}

function loadAds(): void {
    if (state.loaded.adsense || !ADSENSE_CLIENT_ID) return;
    loadScriptOnce(
        `https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=${ADSENSE_CLIENT_ID}`,
        { crossorigin: "anonymous" },
    );
    state.loaded.adsense = true;
}

function apply(record: ConsentRecord | null): void {
    pushConsentUpdate(record);
    if (record?.analytics) loadAnalytics();
    if (record?.ads) loadAds();
}

function persist(analytics: boolean, ads: boolean, locale: string): void {
    const record: ConsentRecord = {
        version: CONSENT_VERSION,
        necessary: true,
        analytics,
        ads,
        updatedAt: new Date().toISOString(),
        locale,
    };
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(record));
    } catch {
    }
    state.record = record;
    state.bannerOpen = false;
    state.modalOpen = false;
    apply(record);
}

export function initConsent(): void {
    if (state.record) apply(state.record);
}

export function acceptAll(locale: string): void {
    persist(true, true, locale);
}

export function rejectNonEssential(locale: string): void {
    persist(false, false, locale);
}

export function savePreferences(
    prefs: { analytics: boolean; ads: boolean },
    locale: string,
): void {
    persist(prefs.analytics, prefs.ads, locale);
}

export function openPreferences(): void {
    state.modalOpen = true;
}

export function closePreferences(): void {
    state.modalOpen = false;
}

export function getDraft(): { analytics: boolean; ads: boolean } {
    if (state.record) {
        return { analytics: state.record.analytics, ads: state.record.ads };
    }
    return { analytics: true, ads: true };
}

export function hasGlobalPrivacyControl(): boolean {
    const nav = navigator as Navigator & { globalPrivacyControl?: boolean };
    return nav.globalPrivacyControl === true || nav.doNotTrack === "1";
}

export function resetConsent(): void {
    try {
        localStorage.removeItem(STORAGE_KEY);
    } catch {
    }
    state.record = null;
    state.loaded = { ga: false, adsense: false };
    state.modalOpen = false;
    state.bannerOpen = true;
}

export function useCookieConsent() {
    return {
        record: computed(() => state.record),
        bannerOpen: computed(() => state.bannerOpen),
        modalOpen: computed(() => state.modalOpen),
        adsGranted: computed(() => state.record?.ads === true),
        analyticsGranted: computed(() => state.record?.analytics === true),
        loaded: readonly(state.loaded),
        consentMode: computed(() => toConsentMode(state.record)),
        initConsent,
        acceptAll,
        rejectNonEssential,
        savePreferences,
        openPreferences,
        closePreferences,
        getDraft,
        hasGlobalPrivacyControl,
        resetConsent,
    };
}

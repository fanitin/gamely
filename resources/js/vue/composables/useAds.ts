import { useCookieConsent } from "@/vue/composables/useCookieConsent";

const CLIENT_ID = import.meta.env.VITE_ADSENSE_CLIENT_ID as string | undefined;

export function useAds() {
    const { adsGranted } = useCookieConsent();

    const showAd = (): void => {
        if (!CLIENT_ID || !adsGranted.value) return;
        (window.adsbygoogle = window.adsbygoogle || []).push({});
    };

    return {
        clientId: CLIENT_ID,
        enabled: adsGranted,
        isDev: import.meta.env.DEV,
        showAd,
    };
}

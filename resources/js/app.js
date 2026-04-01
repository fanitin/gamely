import "./bootstrap";
import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { createI18n } from "vue-i18n";
import en from "./vue/locales/en.json";
import ru from "./vue/locales/ru.json";
import ua from "./vue/locales/ua.json";

function getDefaultLocale() {
    const saved = localStorage.getItem("gw_locale");
    if (saved) return saved;
    
    // Check browser language, SSR safe
    if (typeof navigator !== 'undefined') {
        const browserLang = navigator.language.split('-')[0];
        if (['en', 'ru', 'ua'].includes(browserLang)) {
            return browserLang;
        }
    }
    return 'en';
}

const i18n = createI18n({
    legacy: false, // ensure composition API mode is strictly on
    locale: getDefaultLocale(),
    fallbackLocale: "en",
    messages: { en, ru, ua },
});

createInertiaApp({
    resolve: (name) =>
        resolvePageComponent(
            `./vue/pages/${name}.vue`,
            import.meta.glob("./vue/pages/**/*.vue"),
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(i18n)
            .mount(el);
    },
});

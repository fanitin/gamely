<script setup lang="ts">
import { useI18n } from "vue-i18n";

defineProps<{
    count: number;
}>();

const { t } = useI18n();
</script>

<template>
    <div class="mt-3 text-center text-sm text-muted/90 tracking-wide">
        {{ t("game.solved_today", { count }) }}
    </div>
</template>

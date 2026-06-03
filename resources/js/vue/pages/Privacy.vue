<script setup lang="ts">
import LegalDocument from "@/vue/components/legal/LegalDocument.vue";

defineProps<{
    meta: { version: string; effective_date: string; last_updated: string };
}>();
</script>

<template>
    <LegalDocument ns="legal.privacy" :meta="meta" />
</template>
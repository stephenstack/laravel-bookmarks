<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const settings = computed(() => page.props.site_settings as any);
const siteTitle = computed(() => settings.value?.title || 'Laravel Starter Kit');
</script>

<template>
    <div v-if="settings?.logo_light || settings?.logo_dark" class="flex items-center justify-center h-full w-full">
        <!-- Light Mode Logo -->
        <img 
            v-if="settings.logo_light"
            :src="settings.logo_light"
            :alt="siteTitle"
            class="h-full w-auto object-contain transition-opacity dark:hidden"
        />
        <!-- Dark Mode Logo -->
        <img 
            v-if="settings.logo_dark"
            :src="settings.logo_dark"
            :alt="siteTitle"
            class="h-full w-auto object-contain transition-opacity hidden dark:block"
        />
        <!-- Fallback: If only Light exists, show it in Dark mode too -->
        <img 
            v-if="settings.logo_light && !settings.logo_dark"
            :src="settings.logo_light"
            :alt="siteTitle"
            class="h-full w-auto object-contain transition-opacity hidden dark:block"
        />
    </div>

    <div v-else class="flex items-center justify-center">
        <div class="flex items-center justify-center h-10 px-4 bg-muted/50 rounded-md border-2 border-dashed border-muted-foreground/20 text-muted-foreground font-black tracking-widest select-none uppercase text-xs">
            LOGO
        </div>
    </div>
</template>

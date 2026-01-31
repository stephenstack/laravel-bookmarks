<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

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

    <div v-else class="flex items-center gap-2.5 group">
        <div class="size-9 rounded-lg bg-gradient-to-br from-primary/20 to-primary/5 flex items-center justify-center border border-primary/20 shrink-0 shadow-sm group-hover:shadow-md transition-shadow">
            <AppLogoIcon class="size-5.5 text-primary drop-shadow-sm" />
        </div>
        <span class="font-bold tracking-tight text-[15px] bg-clip-text text-transparent bg-gradient-to-r from-foreground to-foreground/80 truncate max-w-[140px]">{{ siteTitle }}</span>
    </div>
</template>

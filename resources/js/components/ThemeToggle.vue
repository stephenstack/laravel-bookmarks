<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { Moon, Sun } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

const { appearance, updateAppearance, resolvedAppearance } = useAppearance();

const toggleTheme = () => {
    updateAppearance(resolvedAppearance.value === 'dark' ? 'light' : 'dark');
};
</script>

<template>
    <Button
        variant="ghost"
        size="icon"
        class="h-9 w-9"
        @click="toggleTheme"
    >
        <div class="relative flex items-center justify-center">
            <Sun class="size-5 transition-all" :class="resolvedAppearance === 'dark' ? 'rotate-90 scale-0' : 'rotate-0 scale-100'" />
            <Moon class="absolute size-5 transition-all" :class="resolvedAppearance === 'dark' ? 'rotate-0 scale-100' : '-rotate-90 scale-0'" />
        </div>
        <span class="sr-only">Toggle theme</span>
    </Button>
</template>

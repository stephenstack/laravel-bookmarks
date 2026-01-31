<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Clock } from 'lucide-vue-next';
import StyledClock from '@/components/StyledClock.vue';
import type { ClockStyle } from '@/components/StyledClock.vue';

const props = defineProps<{
    format?: '12h' | '24h';
    variant?: ClockStyle;
}>();

const emit = defineEmits<{
    'update:format': [format: '12h' | '24h'];
}>();

const currentDate = ref('');
let intervalId: number | null = null;

const updateDate = () => {
    const now = new Date();
    currentDate.value = now.toLocaleDateString('en-US', {
        weekday: 'short',
        month: 'short',
        day: 'numeric'
    });
};

const toggleFormat = () => {
    emit('update:format', props.format === '12h' ? '24h' : '12h');
};

onMounted(() => {
    updateDate();
    // Update date every minute to be safe
    intervalId = window.setInterval(updateDate, 60000);
});

onUnmounted(() => {
    if (intervalId !== null) {
        clearInterval(intervalId);
    }
});
</script>

<template>
    <div 
        class="flex items-center gap-4 rounded-xl border bg-card p-4 cursor-pointer hover:bg-accent/50 transition-colors h-[88px]"
        @click="toggleFormat"
    >
        <div class="flex size-10 items-center justify-center rounded-lg bg-indigo-500/10 text-indigo-500 shrink-0">
            <Clock class="size-5" />
        </div>
        
        <div class="flex-1 flex flex-col justify-center min-w-0">
            <!-- The Styled Clock Component -->
            <StyledClock 
                :variant="variant" 
                :format="format" 
                size="sm"
                :class="variant === 'digital-led' || variant === 'retro' ? 'justify-start' : 'justify-start'"
            />
            
            <p class="text-sm text-muted-foreground mt-1 truncate">
                {{ currentDate }}
            </p>
        </div>
        
        <div class="text-xs text-muted-foreground shrink-0 self-start mt-1">
            {{ format === '12h' ? '12h' : '24h' }}
        </div>
    </div>
</template>

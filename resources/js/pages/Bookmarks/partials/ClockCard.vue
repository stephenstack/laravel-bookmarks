<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Clock } from 'lucide-vue-next';

const props = defineProps<{
    format?: '12h' | '24h';
}>();

const emit = defineEmits<{
    'update:format': [format: '12h' | '24h'];
}>();

const currentTime = ref('');
const currentDate = ref('');
let intervalId: number | null = null;

const updateTime = () => {
    const now = new Date();
    
    if (props.format === '12h') {
        currentTime.value = now.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    } else {
        currentTime.value = now.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        });
    }
    
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
    updateTime();
    intervalId = window.setInterval(updateTime, 1000);
});

onUnmounted(() => {
    if (intervalId !== null) {
        clearInterval(intervalId);
    }
});
</script>

<template>
    <div 
        class="flex items-center gap-4 rounded-xl border bg-card p-4 cursor-pointer hover:bg-accent/50 transition-colors"
        @click="toggleFormat"
    >
        <div class="flex size-10 items-center justify-center rounded-lg bg-indigo-500/10 text-indigo-500">
            <Clock class="size-5" />
        </div>
        <div class="flex-1">
            <p class="text-2xl font-bold">{{ currentTime }}</p>
            <p class="text-sm text-muted-foreground">{{ currentDate }}</p>
        </div>
        <div class="text-xs text-muted-foreground">
            {{ format === '12h' ? '12h' : '24h' }}
        </div>
    </div>
</template>

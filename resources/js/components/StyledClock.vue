<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { cn } from '@/lib/utils';

// --- Types ---

export type ClockStyle = 
    | 'digital-led'   // 7-segment LED look
    | 'minimal'       // Clean sans-serif
    | 'retro'         // Monospace CRT
    | 'bold'          // High contrast
    | 'soft';         // Rounded, calm

export interface ClockProps {
    variant?: ClockStyle;
    format?: '12h' | '24h';
    showSeconds?: boolean;
    blinkColons?: boolean;
    size?: 'sm' | 'md' | 'lg';
    class?: string;
}

// --- Props & Default Values ---

const props = withDefaults(defineProps<ClockProps>(), {
    variant: 'minimal',
    format: '12h',
    showSeconds: true,
    blinkColons: true,
    size: 'md',
});

// --- State ---

const time = ref(new Date());
let timer: number | null = null;

// --- Time Logic ---

const updateTime = () => {
    time.value = new Date();
};

onMounted(() => {
    updateTime();
    // Sync with the second
    const timeout = 1000 - new Date().getMilliseconds();
    setTimeout(() => {
        updateTime();
        timer = window.setInterval(updateTime, 1000);
    }, timeout);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});

// --- Formatting Helpers ---

const pad = (num: number) => num.toString().padStart(2, '0');

const hours = computed(() => {
    let h = time.value.getHours();
    if (props.format === '12h') {
        h = h % 12 || 12;
    }
    return pad(h);
});

const minutes = computed(() => pad(time.value.getMinutes()));
const seconds = computed(() => pad(time.value.getSeconds()));
const ampm = computed(() => (time.value.getHours() >= 12 ? 'PM' : 'AM'));

// --- Styling Logic ---

// Size mappings
// Size mappings
const sizeClasses = {
    sm: 'text-xl',
    md: 'text-3xl',
    lg: 'text-5xl',
};

// Style Definitions
const variantStyles = computed(() => {
    const base = 'flex items-baseline justify-center select-none leading-none transition-all duration-300';
    
    switch (props.variant) {
        case 'digital-led':
            return {
                container: cn(base, 'font-mono text-red-500 bg-black/90 p-2 rounded border border-red-900 shadow-[0_0_10px_rgba(239,68,68,0.3)]'),
                digits: 'tracking-[0.1em] drop-shadow-[0_0_4px_rgba(239,68,68,0.8)]',
                separator: props.blinkColons ? 'animate-pulse' : '',
                sub: 'text-[0.6rem] text-red-500/70 ml-1'
            };
        case 'retro':
            return {
                container: cn(base, 'font-mono text-green-400 bg-stone-900 border border-green-800/50 p-1.5 rounded shadow-inner'),
                digits: 'drop-shadow-[0_0_2px_rgba(74,222,128,0.5)]',
                separator: props.blinkColons ? 'animate-pulse opacity-80' : 'opacity-80',
                sub: 'text-xs text-green-600 ml-1'
            };
        case 'bold':
            return {
                container: cn(base, 'font-black text-foreground bg-background border-2 border-foreground p-1'),
                digits: 'tracking-tight',
                separator: 'mx-0.5',
                sub: 'text-xs font-bold ml-1'
            };
        case 'soft':
            return {
                container: cn(base, 'font-sans font-medium text-indigo-200 bg-indigo-950/40 rounded-lg backdrop-blur-sm p-2 ring-1 ring-white/10'),
                digits: 'tracking-wide drop-shadow-sm',
                separator: 'mx-0.5 opacity-60',
                sub: 'text-[0.65rem] text-indigo-300/80 uppercase tracking-widest ml-1'
            };
        case 'minimal':
        default:
            return {
                container: cn(base, 'font-sans font-light text-foreground/90'),
                digits: 'tracking-tight',
                separator: 'mx-0.5 opacity-50',
                sub: 'text-sm font-normal text-muted-foreground ml-1.5'
            };
    }
});

// Import Google Font for LED style if needed (handled via CSS usually, but we'll use a system mono fallback for now)
</script>

<template>
    <div :class="cn(variantStyles.container, props.class)" :data-variant="variant">
        <!-- Hours -->
        <span :class="cn(sizeClasses[size], variantStyles.digits)">
            {{ hours }}
        </span>

        <!-- Separator -->
        <span :class="cn(sizeClasses[size], variantStyles.separator, variantStyles.digits)">:</span>

        <!-- Minutes -->
        <span :class="cn(sizeClasses[size], variantStyles.digits)">
            {{ minutes }}
        </span>

        <!-- Optional Seconds -->
        <template v-if="showSeconds">
            <span :class="cn(sizeClasses[size], variantStyles.separator, variantStyles.digits)">:</span>
            <span :class="cn(sizeClasses[size], 'opacity-80', variantStyles.digits)">
                {{ seconds }}
            </span>
        </template>

        <!-- AM/PM for 12h Format -->
        <span v-if="format === '12h'" :class="variantStyles.sub">
            {{ ampm }}
        </span>
    </div>
</template>

<style scoped>
/* Optional: Import a specific digital font if connected to the internet, 
   otherwise rely on 'font-mono' which uses the system monospace stack. 
*/
@import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Share+Tech+Mono&family=VT323&display=swap');

[data-variant="digital-led"] {
    font-family: 'Orbitron', 'Segoe UI', monospace;
}

[data-variant="retro"] {
    font-family: 'VT323', 'Courier New', monospace;
}
</style>

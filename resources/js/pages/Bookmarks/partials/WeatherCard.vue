<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { Cloud, CloudRain, CloudSnow, Sun, Wind } from 'lucide-vue-next';

const props = defineProps<{
    city?: string;
    latitude?: number;
    longitude?: number;
}>();

interface WeatherData {
    temperature: number;
    weatherCode: number;
    windSpeed: number;
    timestamp: number;
}

const weatherData = ref<WeatherData | null>(null);
const loading = ref(false);
const error = ref('');

const CACHE_DURATION = 15 * 60 * 1000; // 15 minutes in milliseconds

const weatherIcon = computed(() => {
    if (!weatherData.value) return Sun;
    const code = weatherData.value.weatherCode;
    
    // WMO Weather interpretation codes
    if (code === 0) return Sun; // Clear sky
    if (code >= 1 && code <= 3) return Cloud; // Partly cloudy
    if (code >= 51 && code <= 67) return CloudRain; // Rain
    if (code >= 71 && code <= 77) return CloudSnow; // Snow
    return Wind; // Default
});

const weatherDescription = computed(() => {
    if (!weatherData.value) return 'Loading...';
    const code = weatherData.value.weatherCode;
    
    if (code === 0) return 'Clear';
    if (code >= 1 && code <= 3) return 'Cloudy';
    if (code >= 51 && code <= 67) return 'Rainy';
    if (code >= 71 && code <= 77) return 'Snowy';
    return 'Windy';
});

const getCacheKey = () => {
    return `weather_${props.latitude}_${props.longitude}`;
};

const getCachedData = (): WeatherData | null => {
    try {
        const cached = localStorage.getItem(getCacheKey());
        if (!cached) return null;
        
        const data = JSON.parse(cached) as WeatherData;
        const now = Date.now();
        
        if (now - data.timestamp < CACHE_DURATION) {
            return data;
        }
        
        // Cache expired
        localStorage.removeItem(getCacheKey());
        return null;
    } catch {
        return null;
    }
};

const setCachedData = (data: Omit<WeatherData, 'timestamp'>) => {
    try {
        const cacheData: WeatherData = {
            ...data,
            timestamp: Date.now(),
        };
        localStorage.setItem(getCacheKey(), JSON.stringify(cacheData));
    } catch (e) {
        console.error('Failed to cache weather data:', e);
    }
};

const fetchWeather = async () => {
    if (!props.latitude || !props.longitude) {
        error.value = 'Location not set';
        return;
    }
    
    // Check cache first
    const cached = getCachedData();
    if (cached) {
        weatherData.value = cached;
        return;
    }
    
    loading.value = true;
    error.value = '';
    
    try {
        const response = await fetch(
            `https://api.open-meteo.com/v1/forecast?latitude=${props.latitude}&longitude=${props.longitude}&current=temperature_2m,weather_code,wind_speed_10m&temperature_unit=celsius`
        );
        
        if (!response.ok) throw new Error('Failed to fetch weather');
        
        const data = await response.json();
        
        const weatherInfo = {
            temperature: Math.round(data.current.temperature_2m),
            weatherCode: data.current.weather_code,
            windSpeed: Math.round(data.current.wind_speed_10m),
        };
        
        weatherData.value = { ...weatherInfo, timestamp: Date.now() };
        setCachedData(weatherInfo);
    } catch (e) {
        error.value = 'Failed to load weather';
        console.error('Weather fetch error:', e);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchWeather();
});
</script>

<template>
    <div class="flex items-center gap-4 rounded-xl border bg-card p-4 cursor-pointer hover:bg-accent/50 transition-colors">
        <div class="flex size-10 items-center justify-center rounded-lg bg-sky-500/10 text-sky-500">
            <component :is="weatherIcon" class="size-5" />
        </div>
        <div class="flex-1">
            <p v-if="loading" class="text-2xl font-bold">--°</p>
            <p v-else-if="error" class="text-2xl font-bold text-destructive">!</p>
            <p v-else class="text-2xl font-bold">{{ weatherData?.temperature }}°C</p>
            <p class="text-sm text-muted-foreground">
                {{ city || 'Weather' }}
            </p>
        </div>
        <div v-if="weatherData && !loading && !error" class="flex flex-col items-end gap-0.5 text-xs text-muted-foreground">
            <div class="flex items-center gap-1">
                <Wind class="size-3" />
                <span>{{ weatherData.windSpeed }} km/h</span>
            </div>
            <div>{{ weatherDescription }}</div>
        </div>
    </div>
</template>

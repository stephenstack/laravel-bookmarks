<script setup lang="ts">
import { useBookmarksStore } from '@/composables/useBookmarksStore';
import { Bookmark, FolderOpen, Star, Tag, Settings2, CloudSun, Clock, Archive, Tags } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import WeatherCard from './WeatherCard.vue';
import ClockCard from './ClockCard.vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Checkbox } from '@/components/ui/checkbox';

const { state: store, setFilterType } = useBookmarksStore();

interface DashboardCard {
    id: string;
    label: string;
    icon: any;
    color: string;
    enabled: boolean;
    clickable: boolean;
    href?: string;
    action?: () => void;
}

interface WeatherConfig {
    enabled: boolean;
    city: string;
    latitude: number;
    longitude: number;
}

interface ClockConfig {
    enabled: boolean;
    format: '12h' | '24h';
}

const isConfigOpen = ref(false);

// Card definitions (never serialized)
const cardDefinitions: DashboardCard[] = [
    {
        id: 'total',
        label: 'Total Bookmarks',
        icon: Bookmark,
        color: 'bg-blue-500/10 text-blue-500',
        enabled: true,
        clickable: true,
        href: '/bookmarks/all',
    },
    {
        id: 'favorites',
        label: 'Favorites',
        icon: Star,
        color: 'bg-amber-500/10 text-amber-500',
        enabled: true,
        clickable: true,
        href: '/bookmarks/favorites',
    },
    {
        id: 'collections',
        label: 'Collections',
        icon: FolderOpen,
        color: 'bg-violet-500/10 text-violet-500',
        enabled: true,
        clickable: false,
    },
    {
        id: 'tags',
        label: 'Tags Used',
        icon: Tag,
        color: 'bg-emerald-500/10 text-emerald-500',
        enabled: true,
        clickable: false,
    },
];

interface StoredConfig {
    enabledCards: string[];
    weather: WeatherConfig;
    clock: ClockConfig;
}

// Load saved configuration from localStorage
const loadConfig = (): { cards: DashboardCard[]; weather: WeatherConfig; clock: ClockConfig } => {
    
    try {
        const saved = localStorage.getItem('dashboard_cards_config');
        
        if (saved) {
            const stored: StoredConfig = JSON.parse(saved);
            
            // Merge stored enabled state with card definitions
            const cards = cardDefinitions.map(card => ({
                ...card,
                enabled: stored.enabledCards.includes(card.id),
            }));
            
            
            return {
                cards,
                weather: stored.weather,
                clock: stored.clock || { enabled: false, format: '12h' },
            };
        }
    } catch (e) {
        console.error('Failed to load dashboard config:', e);
    }
    
    
    // Default configuration
    return {
        cards: [...cardDefinitions],
        weather: {
            enabled: false,
            city: 'London',
            latitude: 51.5074,
            longitude: -0.1278,
        },
        clock: {
            enabled: false,
            format: '12h',
        },
    };
};

const config = ref(loadConfig());


const saveConfig = () => {
    
    try {
        const stored: StoredConfig = {
            enabledCards: config.value.cards.filter(c => c.enabled).map(c => c.id),
            weather: config.value.weather,
            clock: config.value.clock,
        };
        
        localStorage.setItem('dashboard_cards_config', JSON.stringify(stored));
    } catch (e) {
        console.error('Failed to save dashboard config:', e);
    }
};

const visibleCards = computed(() => config.value.cards.filter(c => c.enabled));

const getCardValue = (cardId: string) => {
    const now = new Date();
    const sevenDaysAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
    
    switch (cardId) {
        case 'total':
            return store.bookmarks.filter(b => !b.status || b.status === 'active').length;
        case 'favorites':
            return store.bookmarks.filter(b => b.is_favorite && (!b.status || b.status === 'active')).length;
        case 'collections':
            return store.collections.length;
        case 'tags':
            return store.tags.length;
        case 'recent':
            return store.bookmarks.filter(b => {
                if (!b.created_at) return false;
                const createdAt = new Date(b.created_at);
                return createdAt >= sevenDaysAgo && (!b.status || b.status === 'active');
            }).length;
        case 'archived':
            return store.bookmarks.filter(b => b.status === 'archived').length;
        case 'untagged':
            return store.bookmarks.filter(b => (!b.tags || b.tags.length === 0) && (!b.status || b.status === 'active')).length;
        default:
            return 0;
    }
};

const handleCardClick = (card: DashboardCard) => {
    if (!card.clickable) return;
    if (card.action) {
        card.action();
    }
};

const openConfig = () => {
    isConfigOpen.value = true;
};

const saveAndClose = () => {
    saveConfig();
    isConfigOpen.value = false;
};

// Predefined cities for quick selection
const popularCities = [
    { name: 'London', lat: 51.5074, lon: -0.1278 },
    { name: 'New York', lat: 40.7128, lon: -74.0060 },
    { name: 'Tokyo', lat: 35.6762, lon: 139.6503 },
    { name: 'Paris', lat: 48.8566, lon: 2.3522 },
    { name: 'Sydney', lat: -33.8688, lon: 151.2093 },
    { name: 'Dubai', lat: 25.2048, lon: 55.2708 },
];

const selectCity = (city: typeof popularCities[0]) => {
    config.value.weather.city = city.name;
    config.value.weather.latitude = city.lat;
    config.value.weather.longitude = city.lon;
};
</script>

<template>
    <div class="relative">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-muted-foreground">DASHBOARD</h2>
            <Button
                variant="ghost"
                size="icon"
                class="h-6 w-6"
                @click="openConfig"
            >
                <Settings2 class="size-3.5" />
            </Button>
        </div>
        
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4" :key="visibleCards.length">
            <!-- Regular Cards -->
            <component
                v-for="card in visibleCards"
                :key="card.id"
                :is="card.clickable && card.href ? Link : 'div'"
                :href="card.href"
                :class="[
                    'flex items-center gap-4 rounded-xl border bg-card p-4 transition-colors',
                    card.clickable ? 'cursor-pointer hover:bg-accent/50' : ''
                ]"
                @click="handleCardClick(card)"
            >
                <div
                    class="flex size-10 items-center justify-center rounded-lg"
                    :class="card.color"
                >
                    <component :is="card.icon" class="size-5" />
                </div>
                <div>
                    <p class="text-2xl font-bold">{{ getCardValue(card.id) }}</p>
                    <p class="text-sm text-muted-foreground">{{ card.label }}</p>
                </div>
            </component>
            
            <!-- Weather Card -->
            <a
                v-if="config.weather.enabled"
                href="https://open-meteo.com"
                target="_blank"
                rel="noopener noreferrer"
            >
                <WeatherCard
                    :city="config.weather.city"
                    :latitude="config.weather.latitude"
                    :longitude="config.weather.longitude"
                />
            </a>
        </div>
        
        <!-- Configuration Dialog -->
        <Dialog v-model:open="isConfigOpen">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Dashboard Cards</DialogTitle>
                    <DialogDescription>
                        Customize which cards appear on your dashboard
                    </DialogDescription>
                </DialogHeader>
                
                <div class="space-y-6">
                    <!-- Card Selection -->
                    <div>
                        <h3 class="mb-3 text-sm font-semibold">Visible Cards</h3>
                        <div class="space-y-2">
                            <div
                                v-for="card in config.cards"
                                :key="card.id"
                                class="flex items-center space-x-2"
                            >
                                <input
                                    type="checkbox"
                                    :id="card.id"
                                    v-model="card.enabled"
                                    @change="() => {
                                        console.log(`Native checkbox changed for ${card.id}:`, card.enabled);
                                        console.log('All cards enabled states:', config.cards.map(c => ({ id: c.id, enabled: c.enabled })));
                                    }"
                                    class="h-4 w-4 rounded border-gray-300"
                                />
                                <label
                                    :for="card.id"
                                    class="flex items-center gap-2 text-sm font-medium leading-none cursor-pointer"
                                >
                                    <component :is="card.icon" class="size-4" />
                                    {{ card.label }}
                                    <span v-if="card.clickable" class="text-xs text-muted-foreground">(clickable)</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Weather Configuration -->
                    <div>
                        <div class="mb-3 flex items-center space-x-2">
                            <input
                                type="checkbox"
                                id="weather-enabled"
                                v-model="config.weather.enabled"
                                class="h-4 w-4 rounded border-gray-300"
                            />
                            <label
                                for="weather-enabled"
                                class="flex items-center gap-2 text-sm font-semibold cursor-pointer"
                            >
                                <CloudSun class="size-4" />
                                Weather Card
                            </label>
                        </div>
                        
                        <div v-if="config.weather.enabled" class="space-y-3 pl-6">
                            <div class="space-y-2">
                                <Label>Quick Select</Label>
                                <div class="flex flex-wrap gap-2">
                                    <Button
                                        v-for="city in popularCities"
                                        :key="city.name"
                                        variant="outline"
                                        size="sm"
                                        @click="selectCity(city)"
                                        :class="config.weather.city === city.name ? 'border-primary' : ''"
                                    >
                                        {{ city.name }}
                                    </Button>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-3 gap-3">
                                <div class="space-y-2">
                                    <Label for="city">City Name</Label>
                                    <Input
                                        id="city"
                                        v-model="config.weather.city"
                                        placeholder="London"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label for="latitude">Latitude</Label>
                                    <Input
                                        id="latitude"
                                        v-model.number="config.weather.latitude"
                                        type="number"
                                        step="0.0001"
                                        placeholder="51.5074"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label for="longitude">Longitude</Label>
                                    <Input
                                        id="longitude"
                                        v-model.number="config.weather.longitude"
                                        type="number"
                                        step="0.0001"
                                        placeholder="-0.1278"
                                    />
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                <p class="text-xs text-muted-foreground">
                                    Weather data is cached for 15 minutes. Click the weather card to visit Open-Meteo.
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Need coordinates? 
                                    <a 
                                        href="https://www.latlong.net/" 
                                        target="_blank" 
                                        rel="noopener noreferrer"
                                        class="text-primary hover:underline"
                                    >
                                        Find lat/long on LatLong.net
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <DialogFooter>
                    <Button variant="outline" @click="isConfigOpen = false">
                        Cancel
                    </Button>
                    <Button @click="saveAndClose">
                        Save Changes
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

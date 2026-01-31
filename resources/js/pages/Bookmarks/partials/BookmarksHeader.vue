<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Separator } from '@/components/ui/separator';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { useBookmarksStore } from '@/composables/useBookmarksStore';
import type { SortBy, FilterType } from '@/types/bookmarks'; // Import types
import { cn } from '@/lib/utils';
import {
    ArrowUpDown,
    Check,
    Github,
    LayoutGrid,
    List,
    Plus,
    Search,
    SlidersHorizontal,
} from 'lucide-vue-next';
import { computed } from 'vue';
import ThemeToggle from '@/components/ThemeToggle.vue';

const props = withDefaults(defineProps<{
    title?: string;
}>(), {
    title: 'Bookmarks',
});

const { state: store, setViewMode, setSearchQuery, setSortBy, setFilterType, openBookmarkModal } = useBookmarksStore();

const sortOptions = [
  { value: 'date-newest', label: 'Date Added (Newest)' },
  { value: 'date-oldest', label: 'Date Added (Oldest)' },
  { value: 'alpha-az', label: 'Alphabetical (A-Z)' },
  { value: 'alpha-za', label: 'Alphabetical (Z-A)' },
  { value: 'custom', label: 'Custom' },
] as const;

const filterOptions = [
  { value: 'all', label: 'All Bookmarks' },
  { value: 'favorites', label: 'Favorites Only' },
  { value: 'with-tags', label: 'With Tags' },
  { value: 'without-tags', label: 'Without Tags' },
] as const;

const currentSort = computed(() => sortOptions.find((opt) => opt.value === store.sortBy));
const currentFilter = computed(() => filterOptions.find((opt) => opt.value === store.filterType));

// Manual v-model for input just to be safe if component api differs
const handleSearchInput = (e: Event) => {
    const target = e.target as HTMLInputElement;
    setSearchQuery(target.value);
};
</script>

<template>
    <header class="w-full border-b">
        <div class="flex h-14 items-center justify-between px-4">
            <div class="flex items-center gap-3">
                <SidebarTrigger />
                <Separator orientation="vertical" class="h-5" />
                <h1 class="hidden text-base font-semibold sm:block">{{ title }}</h1>
            </div>

            <div class="flex items-center gap-2">
                <!-- <div class="relative hidden md:block">
                    <Search
                        class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        placeholder="Search..."
                        :model-value="store.searchQuery"
                        @input="handleSearchInput"
                        class="h-9 w-64 pl-9"
                    />
                </div> -->

                <div class="flex items-center rounded-md border p-0.5">
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-7 w-7 rounded-sm"
                        :class="{'bg-muted': store.viewMode === 'grid'}"
                        @click="setViewMode('grid')"
                    >
                        <LayoutGrid class="size-4" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-7 w-7 rounded-sm"
                        :class="{'bg-muted': store.viewMode === 'list'}"
                        @click="setViewMode('list')"
                    >
                        <List class="size-4" />
                    </Button>
                </div>

                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline" size="sm" class="hidden sm:flex">
                            <ArrowUpDown class="size-4" />
                            <span class="hidden lg:inline">{{ currentSort?.label.split(' ')[0] }}</span>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-48">
                        <DropdownMenuLabel class="text-xs text-muted-foreground">
                            Sort by
                        </DropdownMenuLabel>
                        <DropdownMenuItem
                            v-for="option in sortOptions"
                            :key="option.value"
                            @click="setSortBy(option.value as SortBy)"
                            class="flex items-center justify-between"
                        >
                            {{ option.label }}
                            <Check v-if="store.sortBy === option.value" class="size-4" />
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>

                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                         <Button
                            variant="outline"
                            size="sm"
                            class="hidden sm:flex"
                            :class="{'border-primary text-primary': store.filterType !== 'all'}"
                        >
                            <SlidersHorizontal class="size-4" />
                            <span class="hidden lg:inline">
                                {{ store.filterType !== 'all' ? currentFilter?.label : 'Filter' }}
                            </span>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-48">
                         <DropdownMenuLabel class="text-xs text-muted-foreground">
                            Filter by
                        </DropdownMenuLabel>
                        <DropdownMenuItem
                            v-for="option in filterOptions"
                            :key="option.value"
                            @click="setFilterType(option.value as FilterType)"
                            class="flex items-center justify-between"
                        >
                            {{ option.label }}
                            <Check v-if="store.filterType === option.value" class="size-4" />
                        </DropdownMenuItem>
                         <template v-if="store.filterType !== 'all'">
                             <DropdownMenuSeparator />
                             <DropdownMenuItem
                                @click="setFilterType('all')"
                                class="text-muted-foreground"
                            >
                                Clear filter
                            </DropdownMenuItem>
                         </template>
                    </DropdownMenuContent>
                </DropdownMenu>

                <Button size="sm" class="hidden sm:flex" @click="openBookmarkModal()">
                    <Plus class="size-4" />
                    Add Bookmark
                </Button>

                <Separator orientation="vertical" class="hidden h-5 sm:block" />

                <ThemeToggle />

                <Button variant="ghost" size="icon" as-child>
                    <a
                        href="https://github.com/ln-dev7/square-ui"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <Github class="size-5" />
                    </a>
                </Button>
            </div>
        </div>
    </header>
</template>

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
import { useBookmarksStore } from '@/composables/useBookmarksStore';
import { 
    X, 
    MoreVertical, 
    Pencil, 
    Trash2, 
    Calendar, 
    Type, 
    GripVertical, 
    ChevronDown,
    ArrowUpAz,
    ArrowDownAz,
    Clock,
    Layout,
    Image as ImageIcon
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { VueDraggable } from 'vue-draggable-plus';
import BookmarkCard from './BookmarkCard.vue';
import StatsCards from './StatsCards.vue';

const {
    state: store,
    getFilteredBookmarks,
    toggleTag,
    setFilterType,
    setSortBy,
    deleteCollection,
    openCollectionModal,
    openPageSettings,
    reorderBookmarks,
} = useBookmarksStore();

const filteredBookmarks = computed(() => getFilteredBookmarks());

const pageTitle = computed(() => {
    if (store.pageMode === 'archive') return 'Archive';
    if (store.pageMode === 'trash') return 'Trash';
    return store.collections.find((c) => String(c.id) === store.selectedCollection)?.name || 'All Bookmarks';
});

const activeTagsData = computed(() =>
    store.tags.filter((t) => store.selectedTags.includes(t.id)),
);

const hasActiveFilters = computed(
    () =>
        store.selectedTags.length > 0 ||
        store.filterType !== 'all' ||
        store.sortBy !== 'date-newest',
);

const backgroundSettings = computed(() => {
    let pageKey = 'all';
    if (store.filterType === 'favorites') pageKey = 'favorites';
    else if (store.pageMode === 'archive') pageKey = 'archive';
    else if (store.pageMode === 'trash') pageKey = 'trash';
    else if (store.selectedCollection !== 'all') pageKey = String(store.selectedCollection);

    // Try to get from user preferences first (for system pages or overrides)
    const prefSettings = store.userPreferences.backgrounds?.[pageKey];
    if (prefSettings?.background_image) return prefSettings;

    // Fallback to collection object for real collections
    const collection = store.collections.find((c) => String(c.id) === store.selectedCollection);
    return collection || {};
});

const backgroundStyle = computed(() => {
    const settings = backgroundSettings.value;
    const image = settings.background_image;
    const opacity = settings.background_opacity ?? 100;

    if (!image) return {};

    return {
        backgroundImage: `url("${image}")`,
        opacity: opacity / 100,
    };
});

const currentCollectionId = computed(() => {
    if (store.pageMode === 'index' && store.selectedCollection !== 'all') {
        return store.selectedCollection;
    }
    return null;
});

const currentCollectionObject = computed(() => {
    if (!currentCollectionId.value) return null;
    return store.collections.find(c => String(c.id) === currentCollectionId.value);
});

const handleEdit = () => {
    if (currentCollectionObject.value) {
        openCollectionModal(currentCollectionObject.value);
    }
};

const handleDelete = () => {
    if (currentCollectionId.value) {
        deleteCollection(currentCollectionId.value);
    }
};

const sortOptions = [
    { value: 'date-newest', label: 'Newest', icon: Clock },
    { value: 'date-oldest', label: 'Oldest', icon: Calendar },
    { value: 'alpha-az', label: 'A-Z', icon: ArrowUpAz },
    { value: 'alpha-za', label: 'Z-A', icon: ArrowDownAz },
    { value: 'custom', label: 'Custom', icon: Layout },
] as const;

// We need a local copy for draggable to work correctly with v-model
const draggableBookmarks = ref([...filteredBookmarks.value]);

watch(filteredBookmarks, (newList) => {
    // Only update if not currently dragging
    if (!isDragging.value) {
        draggableBookmarks.value = [...newList];
    }
}, { deep: true });

const isDragging = ref(false);

const onDragStart = () => {
    isDragging.value = true;
};

const onDragEnd = () => {
    if (store.sortBy !== 'custom') {
        setSortBy('custom');
    }
    reorderBookmarks(draggableBookmarks.value);
    
    // Set dragging to false after a short delay to allow computed/watchers to settle
    setTimeout(() => {
        isDragging.value = false;
    }, 100);
};
</script>

<template>
    <div class="flex-1 w-full overflow-auto relative group/content">
        <!-- Background Layer -->
        <div 
            v-if="backgroundSettings.background_image"
            class="absolute inset-0 pointer-events-none z-0 overflow-hidden"
        >
            <div 
                class="absolute inset-0 bg-cover bg-center bg-no-repeat transition-all duration-700 ease-in-out"
                :style="backgroundStyle"
            ></div>
            <!-- Subtle overlay to ensure readability if needed -->
            <div class="absolute inset-0 bg-background/10 backdrop-blur-[2px]"></div>
        </div>

        <div class="relative z-10 p-4 md:p-6 space-y-6">
            <StatsCards />

            <div class="space-y-4">
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                >
                    <div class="flex items-center gap-3">
                        <div>
                            <h2 class="text-lg font-semibold">
                                {{ pageTitle }}
                            </h2>
                            <p class="text-sm text-muted-foreground mt-0.5">
                                {{ filteredBookmarks.length }} bookmark{{
                                    filteredBookmarks.length !== 1 ? 's' : ''
                                }}
                                {{ hasActiveFilters ? ' (filtered)' : '' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <div
                            v-if="activeTagsData.length > 0 || store.filterType !== 'all'"
                            class="flex flex-wrap items-center gap-2"
                        >
                            <span
                                v-for="tag in activeTagsData"
                                :key="tag.id"
                                class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-primary text-primary-foreground"
                            >
                                {{ tag.name }}
                                <button
                                    @click="toggleTag(tag.id)"
                                    class="hover:bg-primary-foreground/20 rounded-full p-0.5"
                                >
                                    <X class="size-3" />
                                </button>
                            </span>
                        </div>

                        <DropdownMenu v-if="currentCollectionId || store.selectedCollection === 'all'">
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" size="icon" class="h-8 w-8">
                                    <MoreVertical class="size-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-56">
                                <DropdownMenuLabel>{{ store.selectedCollection === 'all' ? 'Page Settings' : 'Collection Settings' }}</DropdownMenuLabel>
                                <DropdownMenuSeparator />
                                
                                <DropdownMenuItem v-if="store.selectedCollection === 'all'" @click="openPageSettings" class="cursor-pointer">
                                     <ImageIcon class="mr-2 size-4" />
                                     Background Settings
                                </DropdownMenuItem>
                                
                                <template v-else>
                                    <DropdownMenuItem @click="handleEdit" class="cursor-pointer">
                                        <Pencil class="mr-2 size-4" />
                                        Edit Collection
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator v-if="!currentCollectionObject?.is_system" />
                                    <DropdownMenuItem 
                                        v-if="!currentCollectionObject?.is_system"
                                        @click="handleDelete" 
                                        class="text-red-600 focus:bg-red-50 focus:text-red-600 cursor-pointer"
                                    >
                                        <Trash2 class="mr-2 size-4" />
                                        Delete Collection
                                    </DropdownMenuItem>
                                </template>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>

                <!-- Sort Filter Bar -->
                <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
                    <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider mr-2 whitespace-nowrap">Sort by:</span>
                    <button
                        v-for="option in sortOptions"
                        :key="option.value"
                        @click="setSortBy(option.value)"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium transition-all border whitespace-nowrap"
                        :class="[
                            store.sortBy === option.value
                                ? 'bg-primary border-primary text-primary-foreground shadow-sm'
                                : 'bg-card border-border hover:border-primary/50 text-muted-foreground'
                        ]"
                    >
                        <component :is="option.icon" class="size-3.5" />
                        {{ option.label }}
                    </button>
                </div>

                <div 
                    v-if="store.viewMode === 'grid'"
                    class="relative min-h-[400px] rounded-xl"
                    :class="[
                        isDragging ? 'dragging-active p-4 -m-4' : 'transition-all duration-500'
                    ]"
                >
                    <VueDraggable
                        v-model="draggableBookmarks"
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"
                        @start="onDragStart"
                        @end="onDragEnd"
                        handle=".drag-handle"
                        :animation="150"
                        ghostClass="drag-ghost"
                        dragClass="drag-moving"
                    >
                        <div v-for="bookmark in draggableBookmarks" :key="bookmark.id" class="relative group">
                            <div class="absolute left-2 top-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity drag-handle cursor-grab active:cursor-grabbing p-1 bg-background/80 backdrop-blur rounded shadow-sm border">
                                <GripVertical class="size-4 text-muted-foreground" />
                            </div>
                            <BookmarkCard :bookmark="bookmark" />
                        </div>
                    </VueDraggable>
                </div>
                <div 
                    v-else
                    class="relative rounded-xl"
                    :class="[
                        isDragging ? 'dragging-active p-4 -m-4' : 'transition-all duration-500'
                    ]"
                >
                    <VueDraggable
                        v-model="draggableBookmarks"
                        class="flex flex-col gap-2"
                        @start="onDragStart"
                        @end="onDragEnd"
                        handle=".drag-handle"
                        :animation="200"
                        ghostClass="drag-ghost-list"
                        dragClass="drag-moving"
                    >
                        <div v-for="bookmark in draggableBookmarks" :key="bookmark.id" class="relative group flex items-center gap-2">
                             <div class="drag-handle cursor-grab active:cursor-grabbing p-1 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                <GripVertical class="size-4 text-muted-foreground" />
                            </div>
                            <BookmarkCard 
                                :bookmark="bookmark" 
                                variant="list" 
                                class="flex-1"
                            />
                        </div>
                    </VueDraggable>
                </div>

                <div
                    v-if="filteredBookmarks.length === 0"
                    class="flex flex-col items-center justify-center py-12 text-center"
                >
                    <div
                        class="size-12 rounded-full bg-muted flex items-center justify-center mb-4"
                    >
                        <svg
                            class="size-6 text-muted-foreground"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"
                            />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium mb-1">No bookmarks found</h3>
                    <p class="text-sm text-muted-foreground max-w-sm mb-4">
                        Try adjusting your search or filter to find what you're
                        looking for, or add a new bookmark.
                    </p>
                    <Button
                        v-if="hasActiveFilters"
                        variant="outline"
                        size="sm"
                        @click="setFilterType('all')"
                    >
                        Clear filters
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.dragging-active {
    background-image: radial-gradient(circle, hsl(var(--border)) 1px, transparent 1px);
    background-size: 24px 24px;
    background-color: hsla(var(--muted), 0.3);
}

.drag-ghost {
    opacity: 0.1;
    transform: scale(0.95);
}

.drag-ghost-list {
    opacity: 0.2;
    background-color: hsl(var(--muted));
}

.drag-moving {
    z-index: 50;
    box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    transform: scale(1.02) rotate(1deg);
    transition: transform 0.2s ease;
}

.scrollbar-none::-webkit-scrollbar {
    display: none;
}
.scrollbar-none {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>

<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted, watch } from 'vue';
import { useBookmarksStore } from '@/composables/useBookmarksStore';
import {
    Dialog,
    DialogContent,
} from '@/components/ui/dialog';
import { Search, ExternalLink, Bookmark as BookmarkIcon } from 'lucide-vue-next';

const { state: store, openSearch, closeSearch, toggleSearch } = useBookmarksStore();

const isOpen = computed({
    get: () => store.isSearchModalOpen,
    set: (value) => {
        if (value) openSearch();
        else closeSearch();
    }
});

const searchInput = ref('');
const selectedIndex = ref(0);

// Watch for search input changes and reset index
watch(searchInput, () => {
    selectedIndex.value = 0;
});

// Clear input when modal opens
watch(isOpen, (v) => {
    if (v) {
        searchInput.value = '';
    }
});

const results = computed(() => {
    if (!searchInput.value) return [];
    
    const query = searchInput.value.toLowerCase();
    return store.bookmarks.filter(b => 
        b.title.toLowerCase().includes(query) || 
        b.url.toLowerCase().includes(query) || 
        (b.description && b.description.toLowerCase().includes(query))
    ).slice(0, 10); // Limit to 10 results
});

const handleSelect = (bookmark: any) => {
    window.open(bookmark.url, '_blank');
    closeSearch();
    searchInput.value = '';
};

const onKeyDown = (e: KeyboardEvent) => {
    // Global shortcut Ctrl+K or Meta+K
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        toggleSearch();
        return;
    }

    if (!isOpen.value) return;

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (results.value.length > 0) {
            selectedIndex.value = (selectedIndex.value + 1) % results.value.length;
        }
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (results.value.length > 0) {
            selectedIndex.value = (selectedIndex.value - 1 + results.value.length) % results.value.length;
        }
    } else if (e.key === 'Enter') {
        if (results.value[selectedIndex.value]) {
            handleSelect(results.value[selectedIndex.value]);
        }
    } else if (e.key === 'Escape') {
        closeSearch();
    }
};

onMounted(() => {
    window.addEventListener('keydown', onKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', onKeyDown);
});
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="p-0 sm:max-w-[600px] overflow-hidden border shadow-2xl bg-background">
            <div class="flex items-center border-b px-4 h-14 bg-muted/20">
                <Search class="mr-2 h-4 w-4 shrink-0 text-muted-foreground" />
                <input
                    v-model="searchInput"
                    class="flex w-full rounded-md bg-transparent py-4 text-base outline-none placeholder:text-muted-foreground disabled:cursor-not-allowed disabled:opacity-50"
                    placeholder="Search bookmarks, collections..."
                    autofocus
                />
                <div class="px-2 py-0.5 rounded bg-muted text-[10px] font-medium text-muted-foreground flex items-center gap-1">
                   <span>ESC</span>
                </div>
            </div>
            
            <div class="max-h-[400px] overflow-y-auto p-2">
                <div v-if="results.length === 0 && searchInput" class="py-14 text-center text-sm">
                    <p class="text-muted-foreground">No results found for "{{ searchInput }}".</p>
                </div>
                <div v-if="!searchInput" class="py-14 text-center text-sm">
                    <p class="text-muted-foreground">Type to search your bookmarks...</p>
                </div>

                <div v-if="results.length > 0" class="space-y-1">
                    <div class="px-2 py-1.5 text-[10px] font-medium text-muted-foreground uppercase tracking-wider">
                        Bookmarks
                    </div>
                    <button
                        v-for="(bookmark, index) in results"
                        :key="bookmark.id"
                        @click="handleSelect(bookmark)"
                        @mouseenter="selectedIndex = index"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-left transition-all duration-200 outline-none"
                        :class="[
                            selectedIndex === index 
                                ? 'bg-primary/10 text-primary scale-[1.01] shadow-sm' 
                                : 'hover:bg-muted/50 text-foreground'
                        ]"
                    >
                        <div class="flex-shrink-0 size-10 rounded-md bg-muted/80 flex items-center justify-center border shadow-sm group-hover:bg-primary/5">
                            <img v-if="bookmark.favicon" :src="bookmark.favicon" class="size-5" />
                            <BookmarkIcon v-else class="size-4 text-muted-foreground" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-[15px] truncate">{{ bookmark.title }}</div>
                            <div class="text-xs text-blue-600/80 dark:text-blue-400/80 truncate">{{ bookmark.url }}</div>
                        </div>
                        <div v-if="selectedIndex === index" class="hidden sm:flex items-center gap-1 shrink-0 px-2 py-0.5 rounded bg-primary/20 text-[10px] font-semibold text-primary">
                            <span>ENTER</span>
                            <ExternalLink class="size-3" />
                        </div>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between border-t px-4 py-3 bg-muted/30 text-[10px] text-muted-foreground">
                <div class="flex items-center gap-4">
                    <span class="flex items-center gap-1"><kbd class="px-1.5 py-0.5 rounded bg-muted border">Enter</kbd> to select</span>
                    <span class="flex items-center gap-1"><kbd class="px-1.5 py-0.5 rounded bg-muted border">↑↓</kbd> to navigate</span>
                </div>
                <div class="flex items-center gap-1">
                    <Search class="size-3" />
                    <span>Search Bookmarks</span>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>

<style scoped>
kbd {
    font-family: inherit;
}
</style>

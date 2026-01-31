<script setup lang="ts">
import BookmarksLayout from '@/layouts/BookmarksLayout.vue';
import BookmarksHeader from '@/pages/Bookmarks/partials/BookmarksHeader.vue';
import BookmarksContent from '@/pages/Bookmarks/partials/BookmarksContent.vue';
import { Head } from '@inertiajs/vue3';
import type { Bookmark, Collection, Tag, FilterType } from '@/types/bookmarks';
import CreateBookmarkDialog from '@/pages/Bookmarks/partials/CreateBookmarkDialog.vue';
import CreateCollectionDialog from '@/pages/Bookmarks/partials/CreateCollectionDialog.vue';
import PageSettingsDialog from '@/pages/Bookmarks/partials/PageSettingsDialog.vue';
import { usePage } from '@inertiajs/vue3';
import { initializeStore, setUserPreferences, useBookmarksStore } from '@/composables/useBookmarksStore';

import { watch } from 'vue';

const page = usePage();

const props = defineProps<{
    initialBookmarks: Bookmark[];
    collections: Collection[];
    tags: Tag[];
    initialView?: string | null;
    initialCollection?: string | null;
    initialTag?: number | null;
}>();

// Initial initialization
initializeStore(props.initialBookmarks, props.collections, props.tags);
setUserPreferences(page.props.auth?.user?.preferences);

// Initial initialization handled by watcher with immediate: true

// Watch for prop changes from Inertia and update store
watch(
    () => [props.initialBookmarks, props.collections, props.tags, page.props.auth?.user?.preferences, props.initialView, props.initialCollection, props.initialTag],
    ([newBookmarks, newCollections, newTags, newPrefs, newView, newCollection, newTag]) => {
        const { setPageMode, setSelectedCollection, setFilterType, setSelectedTags } = useBookmarksStore();
        
        // Always initialize store first (using the standalone exported functions)
        initializeStore(newBookmarks as Bookmark[], newCollections as Collection[], newTags as Tag[]);
        setUserPreferences(newPrefs);
        
        // Determine target state from props passed by controller
        let targetPageMode: 'index' | 'archive' | 'trash' = 'index';
        let targetFilterType: FilterType = 'all';
        let targetCollection = 'all';
        let targetSelectedTags: number[] = [];
        
        if (newView) {
            targetCollection = 'all'; // Reset collection when viewing specific virtual views
            if (newView === 'favorites') {
                targetFilterType = 'favorites';
                targetPageMode = 'index';
            } else if (newView === 'archive') {
                targetPageMode = 'archive';
                targetFilterType = 'all';
            } else if (newView === 'trash') {
                targetPageMode = 'trash';
                targetFilterType = 'all';
            }
        } else if (newCollection) {
            targetCollection = String(newCollection);
            targetPageMode = 'index';
            targetFilterType = 'all';
        } else if (newTag) {
            targetCollection = 'all';
            targetSelectedTags = [Number(newTag)];
            targetPageMode = 'index';
            targetFilterType = 'all';
        }
        
        // Force update the store state
        setPageMode(targetPageMode);
        setFilterType(targetFilterType);
        setSelectedCollection(targetCollection);
        setSelectedTags(targetSelectedTags);
    },
    { deep: true, immediate: true }
);
</script>

<template>
    <Head title="Bookmarks" />
    <BookmarksLayout>
        <BookmarksHeader />
        <BookmarksContent />
        <CreateBookmarkDialog />
        <CreateCollectionDialog />
        <PageSettingsDialog />
    </BookmarksLayout>
</template>

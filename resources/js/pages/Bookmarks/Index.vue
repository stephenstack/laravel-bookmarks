<script setup lang="ts">
import BookmarksLayout from '@/layouts/BookmarksLayout.vue';
import BookmarksHeader from '@/pages/Bookmarks/partials/BookmarksHeader.vue';
import BookmarksContent from '@/pages/Bookmarks/partials/BookmarksContent.vue';
import { Head } from '@inertiajs/vue3';
import type { Bookmark, Collection, Tag } from '@/types/bookmarks';
import CreateBookmarkDialog from '@/pages/Bookmarks/partials/CreateBookmarkDialog.vue';
import CreateCollectionDialog from '@/pages/Bookmarks/partials/CreateCollectionDialog.vue';
import PageSettingsDialog from '@/pages/Bookmarks/partials/PageSettingsDialog.vue';
import { usePage } from '@inertiajs/vue3';
import { initializeStore, setUserPreferences, useBookmarksStore } from '@/composables/useBookmarksStore';

import { watch, onMounted } from 'vue';

const page = usePage();

const props = defineProps<{
    initialBookmarks: Bookmark[];
    collections: Collection[];
    tags: Tag[];
    initialView?: string | null;
    initialCollection?: string | null;
}>();

// Initial initialization
initializeStore(props.initialBookmarks, props.collections, props.tags);
setUserPreferences(page.props.auth?.user?.preferences);

// Set initial view/collection based on URL
onMounted(() => {
    const { setPageMode, setSelectedCollection, setFilterType } = useBookmarksStore();
    
    if (props.initialView) {
        if (props.initialView === 'favorites') {
            setFilterType('favorites');
        } else if (props.initialView === 'archive') {
            setPageMode('archive');
        } else if (props.initialView === 'trash') {
            setPageMode('trash');
        }
    } else if (props.initialCollection) {
        setSelectedCollection(props.initialCollection);
    }
});

// Watch for prop changes from Inertia and update store
watch(
    () => [props.initialBookmarks, props.collections, props.tags, page.props.auth?.user?.preferences],
    ([newBookmarks, newCollections, newTags, newPrefs]) => {
        initializeStore(newBookmarks as Bookmark[], newCollections as Collection[], newTags as Tag[]);
        setUserPreferences(newPrefs);
    },
    { deep: true }
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

import { reactive, computed } from 'vue';
import type {
    Bookmark,
    BookmarksState,
    Collection,
    Tag,
} from '@/types/bookmarks';
import { router } from '@inertiajs/vue3';

const state = reactive<BookmarksState>({
    bookmarks: [],
    collections: [], // Add
    tags: [], // Add
    archivedBookmarks: [],
    trashedBookmarks: [],
    selectedCollection: 'all',
    selectedTags: [],
    searchQuery: '',
    viewMode: 'grid',
    sortBy: 'date-newest',
    filterType: 'all',
    pageMode: 'index',
    editingBookmark: null,
    isBookmarkModalOpen: false,
    editingCollection: null,
    isCollectionModalOpen: false,
    isSearchModalOpen: false,
    isPageSettingsModalOpen: false,
    userPreferences: {},
});

// Initialize state with data from backend
export function initializeStore(
    initialBookmarks: Bookmark[],
    collections: Collection[],
    tags: Tag[],
) {
    // Separate active, archived, and trashed if backend doesn't pre-separate
    // For now, assuming initialBookmarks is the active list (index)
    // Real implementation: Controller should probably send separated lists or 'deleted_at' field is checked here
    state.bookmarks = initialBookmarks.filter((b) => !b.deleted_at);
    state.archivedBookmarks = []; // Need backend support for archive flag if different from trash
    state.trashedBookmarks = initialBookmarks.filter((b) => b.deleted_at);
    state.collections = collections;
    state.tags = tags;

    // Set initial sortBy based on global preference or current selection
    if (state.selectedCollection === 'all') {
        state.sortBy = (state.userPreferences as any).sort_all || 'date-newest';
    } else {
        const collection = state.collections.find(
            (c) => String(c.id) === state.selectedCollection,
        );
        if (collection) {
            state.sortBy = collection.sort_by || 'date-newest';
        }
    }
}

export function setUserPreferences(preferences: any) {
    state.userPreferences = preferences || {};
    if (state.selectedCollection === 'all') {
        state.sortBy = state.userPreferences.sort_all || 'date-newest';
    }
    state.viewMode = state.userPreferences.view_mode || 'grid';
}

export function useBookmarksStore() {
    const setSelectedCollection = (collectionId: string) => {
        state.selectedCollection = collectionId;

        if (collectionId === 'all') {
            state.sortBy =
                (state.userPreferences as any).sort_all || 'date-newest';
        } else {
            const collection = state.collections.find(
                (c) => String(c.id) === collectionId,
            );
            if (collection) {
                state.sortBy = collection.sort_by || 'date-newest';
            }
        }
    };

    const toggleTag = (tagId: number) => {
        if (state.selectedTags.includes(tagId)) {
            state.selectedTags = state.selectedTags.filter(
                (id) => id !== tagId,
            );
        } else {
            state.selectedTags.push(tagId);
        }
    };

    const clearTags = () => {
        state.selectedTags = [];
    };

    const setSelectedTags = (tags: number[]) => {
        state.selectedTags = tags;
    };

    const setSearchQuery = (query: string) => {
        state.searchQuery = query;
    };

    const setViewMode = (mode: BookmarksState['viewMode']) => {
        state.viewMode = mode;
        router.post(
            '/bookmarks/preferences',
            { view_mode: mode },
            { preserveScroll: true },
        );
    };

    const setSortBy = (sortBy: BookmarksState['sortBy']) => {
        state.sortBy = sortBy;

        // Persist
        if (state.selectedCollection === 'all') {
            router.post(
                '/bookmarks/preferences',
                { sort_all: sortBy },
                { preserveScroll: true },
            );
        } else {
            const collection = state.collections.find(
                (c) => String(c.id) === state.selectedCollection,
            );
            if (collection && typeof collection.id === 'number') {
                router.post(
                    `/collections/${collection.id}`,
                    { _method: 'put', sort_by: sortBy },
                    { preserveScroll: true },
                );
                collection.sort_by = sortBy;
            }
        }
    };

    const setFilterType = (filterType: BookmarksState['filterType']) => {
        state.filterType = filterType;
    };

    const toggleFavorite = (bookmarkId: number | string) => {
        const bookmark = state.bookmarks.find((b) => b.id === bookmarkId);
        if (bookmark) {
            const originalValue = bookmark.is_favorite;
            bookmark.is_favorite = !originalValue;

            router.post(
                `/bookmarks/${bookmarkId}`,
                { _method: 'put', is_favorite: bookmark.is_favorite },
                {
                    preserveScroll: true,
                    onError: () => {
                        bookmark.is_favorite = originalValue;
                    },
                },
            );
        }
    };

    const archiveBookmark = (bookmarkId: number | string) => {
        router.post(
            `/bookmarks/${bookmarkId}/archive`,
            {},
            {
                preserveScroll: true,
            },
        );
    };

    const unarchiveBookmark = (bookmarkId: number | string) => {
        router.post(
            `/bookmarks/${bookmarkId}/unarchive`,
            {},
            {
                preserveScroll: true,
            },
        );
    };

    const trashBookmark = (bookmarkId: number | string) => {
        router.post(
            `/bookmarks/${bookmarkId}/trash`,
            {},
            {
                preserveScroll: true,
            },
        );
    };

    const restoreBookmark = (bookmarkId: number | string) => {
        router.post(
            `/bookmarks/${bookmarkId}/restore`,
            {},
            {
                preserveScroll: true,
            },
        );
    };

    const deleteBookmark = (bookmarkId: number | string) => {
        router.delete(`/bookmarks/${bookmarkId}`, {
            preserveScroll: true,
        });
    };

    const deleteCollection = (collectionId: string | number) => {
        router.delete(`/collections/${collectionId}`, {
            preserveScroll: true,
            onSuccess: () => {
                if (state.selectedCollection === String(collectionId)) {
                    state.selectedCollection = 'all';
                }
            },
        });
    };

    const setPageMode = (mode: 'index' | 'archive' | 'trash') => {
        state.pageMode = mode;
    };

    const getFilteredBookmarks = () => {
        let filtered: Bookmark[];

        // Filter by status based on page mode
        if (state.pageMode === 'archive') {
            filtered = state.bookmarks.filter((b) => b.status === 'archived');
        } else if (state.pageMode === 'trash') {
            filtered = state.bookmarks.filter((b) => b.status === 'trashed');
        } else {
            // Index mode - show only active bookmarks
            filtered = state.bookmarks.filter(
                (b) => !b.status || b.status === 'active',
            );
        }

        if (state.selectedCollection !== 'all' && state.pageMode === 'index') {
            filtered = filtered.filter(
                (b) => b.collection_id == state.selectedCollection,
            );
        }

        if (state.selectedTags.length > 0) {
            filtered = filtered.filter((b) =>
                state.selectedTags.every((tagId) =>
                    b.tags.some((t) => t.id === tagId),
                ),
            );
        }

        if (state.searchQuery) {
            const query = state.searchQuery.toLowerCase();
            filtered = filtered.filter(
                (b) =>
                    b.title.toLowerCase().includes(query) ||
                    (b.description &&
                        b.description.toLowerCase().includes(query)) ||
                    b.url.toLowerCase().includes(query),
            );
        }

        if (state.filterType === 'favorites') {
            filtered = filtered.filter((b) => b.is_favorite);
        } else if (state.filterType === 'with-tags') {
            filtered = filtered.filter((b) => b.tags.length > 0);
        } else if (state.filterType === 'without-tags') {
            filtered = filtered.filter((b) => b.tags.length === 0);
        }

        switch (state.sortBy) {
            case 'date-newest':
                return filtered.sort(
                    (a, b) =>
                        new Date(b.created_at || 0).getTime() -
                        new Date(a.created_at || 0).getTime(),
                );
            case 'date-oldest':
                return filtered.sort(
                    (a, b) =>
                        new Date(a.created_at || 0).getTime() -
                        new Date(b.created_at || 0).getTime(),
                );
            case 'alpha-az':
                return filtered.sort((a, b) => a.title.localeCompare(b.title));
            case 'alpha-za':
                return filtered.sort((a, b) => b.title.localeCompare(a.title));
            case 'custom':
                return filtered.sort((a, b) => (a.order || 0) - (b.order || 0));
            default:
                return filtered;
        }
    };

    const reorderBookmarks = (newOrder: Bookmark[]) => {
        // Update local state
        newOrder.forEach((b, index) => {
            const bookmark = state.bookmarks.find(
                (item) => String(item.id) === String(b.id),
            );
            if (bookmark) {
                bookmark.order = index;
            }
        });

        // Persist to backend if they are real bookmarks (not company ones)
        const updates = newOrder
            .filter((b) => !b.is_company)
            .map((b, index) => ({ id: b.id, order: index }));

        if (updates.length > 0) {
            router.post(
                '/bookmarks/reorder',
                { updates },
                {
                    preserveScroll: true,
                    preserveState: true,
                    onSuccess: () => {
                        // Optionally refresh or just rely on local state
                    },
                },
            );
        }
    };

    const openBookmarkModal = (bookmark: Bookmark | null = null) => {
        state.editingBookmark = bookmark;
        state.isBookmarkModalOpen = true;
    };

    const closeBookmarkModal = () => {
        state.editingBookmark = null;
        state.isBookmarkModalOpen = false;
    };

    const openCollectionModal = (collection: Collection | null = null) => {
        state.editingCollection = collection;
        state.isCollectionModalOpen = true;
    };

    const closeCollectionModal = () => {
        state.editingCollection = null;
        state.isCollectionModalOpen = false;
    };

    const openSearch = () => {
        state.isSearchModalOpen = true;
    };

    const closeSearch = () => {
        state.isSearchModalOpen = false;
    };

    const toggleSearch = () => {
        state.isSearchModalOpen = !state.isSearchModalOpen;
    };

    const openPageSettings = () => {
        state.isPageSettingsModalOpen = true;
    };

    const closePageSettings = () => {
        state.isPageSettingsModalOpen = false;
    };

    const updatePageSettings = (settings: {
        background_image?: string;
        background_opacity?: number;
    }) => {
        // Determine if we should save to user preferences or collection table
        const collection = state.collections.find(
            (c) => String(c.id) === state.selectedCollection,
        );
        const isSystemPage =
            state.selectedCollection === 'all' ||
            ['archive', 'trash'].includes(state.pageMode) ||
            state.filterType === 'favorites';

        if (isSystemPage || (collection && typeof collection.id !== 'number')) {
            // Save to user preferences
            let pageKey = 'all';
            if (state.filterType === 'favorites') pageKey = 'favorites';
            else if (state.pageMode === 'archive') pageKey = 'archive';
            else if (state.pageMode === 'trash') pageKey = 'trash';
            else if (collection) pageKey = String(collection.id);

            const background_settings = {
                ...(state.userPreferences.backgrounds || {}),
                [pageKey]: settings,
            };
            router.post(
                '/bookmarks/preferences',
                { backgrounds: background_settings },
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        state.userPreferences.backgrounds = background_settings;
                    },
                },
            );
        } else if (collection && typeof collection.id === 'number') {
            // Save to collection table
            router.post(
                `/collections/${collection.id}`,
                { _method: 'put', ...settings },
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        Object.assign(collection, settings);
                    },
                },
            );
        }
    };

    return {
        state,
        setSelectedCollection,
        toggleTag,
        clearTags,
        setSelectedTags,
        setSearchQuery,
        setViewMode,
        setSortBy,
        setFilterType,
        toggleFavorite,
        archiveBookmark,
        unarchiveBookmark,
        trashBookmark,
        restoreBookmark,
        deleteBookmark,
        deleteCollection,
        getFilteredBookmarks,
        setPageMode,
        openBookmarkModal,
        closeBookmarkModal,
        openCollectionModal,
        closeCollectionModal,
        openSearch,
        closeSearch,
        toggleSearch,
        reorderBookmarks,
        openPageSettings,
        closePageSettings,
        updatePageSettings,
    };
}

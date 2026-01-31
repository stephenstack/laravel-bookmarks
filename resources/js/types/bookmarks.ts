export interface Tag {
    id: number | string;
    user_id?: number | null; // Nullable for global/system tags
    name: string;
    slug: string;
    color: string | null;
    created_at?: string;
    updated_at?: string;
}

export interface Collection {
    id: number | string; // string for "company-resources"
    user_id?: number;
    name: string;
    slug?: string;
    icon: string;
    color: string;
    order: number;
    sort_by?: SortBy;
    background_image?: string | null;
    background_opacity?: number;
    count?: number; // Added by controller
    is_system?: boolean; // Added logic for admin/company collections
    created_at?: string;
    updated_at?: string;
}

export interface Bookmark {
    id: number | string; // string for "company-123"
    user_id?: number;
    collection_id: number | string | null;
    title: string;
    url: string;
    description: string | null;
    favicon: string | null;
    image_url?: string | null;
    is_favorite: boolean;
    order?: number;
    status?: 'active' | 'archived' | 'trashed';
    is_company?: boolean; // To distinguish
    tags: Tag[]; // Transformed from pivot
    created_at?: string;
    updated_at?: string;
    deleted_at?: string | null;
}

export interface BookmarksState {
    bookmarks: Bookmark[];
    collections: Collection[];
    tags: Tag[];
    archivedBookmarks: Bookmark[];
    trashedBookmarks: Bookmark[];
    selectedCollection: string;
    selectedTags: (number | string)[]; // Supports virtual string IDs
    searchQuery: string;
    viewMode: 'grid' | 'list';
    sortBy: 'date-newest' | 'date-oldest' | 'alpha-az' | 'alpha-za' | 'custom';
    filterType: 'all' | 'favorites' | 'with-tags' | 'without-tags';
    pageMode: 'index' | 'archive' | 'trash';
    editingBookmark: Bookmark | null;
    isBookmarkModalOpen: boolean;
    editingCollection: Collection | null;
    isCollectionModalOpen: boolean;
    isSearchModalOpen: boolean;
    isPageSettingsModalOpen: boolean;
    userPreferences: Record<string, any>;
}

export type SortBy =
    | 'date-newest'
    | 'date-oldest'
    | 'alpha-az'
    | 'alpha-za'
    | 'custom';
export type FilterType = 'all' | 'favorites' | 'with-tags' | 'without-tags';

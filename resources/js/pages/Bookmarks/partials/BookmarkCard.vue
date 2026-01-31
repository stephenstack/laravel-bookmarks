<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useBookmarksStore } from '@/composables/useBookmarksStore';
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from '@/components/ui/tooltip';
import type { Bookmark } from '@/types/bookmarks';
import {
    Archive,
    Copy,
    ExternalLink,
    Heart,
    MoreHorizontal,
    Pencil,
    RotateCcw,
    Tag,
    Trash2,
} from 'lucide-vue-next';
import { getTagColorClass } from '@/lib/colors';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        bookmark: Bookmark;
        variant?: 'grid' | 'list';
    }>(),
    {
        variant: 'grid',
    },
);

const { state: store, toggleFavorite, archiveBookmark, unarchiveBookmark, trashBookmark, restoreBookmark, deleteBookmark, openBookmarkModal } = useBookmarksStore();

const bookmarkTags = computed(() => props.bookmark.tags || []);

const handleCopyUrl = () => {
    navigator.clipboard.writeText(props.bookmark.url);
};

const handleOpenUrl = () => {
    window.open(props.bookmark.url, '_blank');
};
</script>

<template>
    <div
        v-if="variant === 'list'"
        class="group flex items-center gap-4 rounded-lg border bg-card p-4 transition-colors hover:bg-accent/50"
    >
        <div
            class="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-muted"
        >
            <img
                :src="bookmark.favicon || ''"
                :alt="bookmark.title"
                width="24"
                height="24"
                class="size-6"
            />
        </div>

        <div class="min-w-0 flex-1">
            <h3 class="truncate font-medium">{{ bookmark.title }}</h3>
            <p class="truncate text-sm text-muted-foreground">
                {{ bookmark.url }}
            </p>
        </div>

        <div
            v-if="bookmarkTags.length > 0"
            class="hidden items-center gap-1 sm:flex ml-auto mr-2"
        >
            <span
                v-for="tag in bookmarkTags.slice(0, 2)"
                :key="tag.id"
                class="inline-flex items-center rounded-md px-1.5 py-0.5 text-[10px] font-medium border border-transparent"
                :class="getTagColorClass(tag.color)"
            >
                {{ tag.name }}
            </span>
            <span
                v-if="bookmarkTags.length > 2"
                class="text-[10px] text-muted-foreground"
            >
                +{{ bookmarkTags.length - 2 }}
            </span>
        </div>

        <div class="flex items-center gap-1">
            <Button
                variant="ghost"
                size="icon"
                class="h-8 w-8"
                @click="toggleFavorite(bookmark.id)"
            >
                <Heart
                    class="size-4"
                    :class="{
                        'fill-red-500 text-red-500': bookmark.is_favorite,
                    }"
                />
            </Button>
            <Button variant="ghost" size="icon" class="h-8 w-8" @click="handleOpenUrl">
                <ExternalLink class="size-4" />
            </Button>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <Button variant="ghost" size="icon" class="h-8 w-8">
                        <MoreHorizontal class="size-4" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    <DropdownMenuItem @click="handleCopyUrl">
                        <Copy class="mr-2 size-4" />
                        Copy URL
                    </DropdownMenuItem>
                    <DropdownMenuItem v-if="!bookmark.is_company" @click="openBookmarkModal(bookmark)">
                        <Pencil class="mr-2 size-4" />
                        Edit
                    </DropdownMenuItem>
                    <DropdownMenuItem v-if="!bookmark.is_company" @click="openBookmarkModal(bookmark)">
                        <Tag class="mr-2 size-4" />
                        Add Tags
                    </DropdownMenuItem>
                    <DropdownMenuSeparator v-if="!bookmark.is_company" />
                    
                    <!-- Archive/Unarchive -->
                    <DropdownMenuItem
                        v-if="!bookmark.is_company && store.pageMode !== 'trash' && (!bookmark.status || bookmark.status === 'active')"
                        @click="archiveBookmark(bookmark.id)"
                    >
                        <Archive class="mr-2 size-4" />
                        Archive
                    </DropdownMenuItem>
                    <DropdownMenuItem
                        v-if="!bookmark.is_company && store.pageMode === 'archive'"
                        @click="unarchiveBookmark(bookmark.id)"
                    >
                        <RotateCcw class="mr-2 size-4" />
                        Unarchive
                    </DropdownMenuItem>
                    
                    <!-- Trash/Restore -->
                    <DropdownMenuItem
                        v-if="!bookmark.is_company && store.pageMode !== 'trash' && store.pageMode !== 'archive'"
                        @click="trashBookmark(bookmark.id)"
                    >
                        <Trash2 class="mr-2 size-4" />
                        Move to Trash
                    </DropdownMenuItem>
                    <DropdownMenuItem
                        v-if="!bookmark.is_company && store.pageMode === 'trash'"
                        @click="restoreBookmark(bookmark.id)"
                    >
                        <RotateCcw class="mr-2 size-4" />
                        Restore
                    </DropdownMenuItem>
                    
                    <!-- Permanent Delete -->
                    <DropdownMenuItem
                        v-if="!bookmark.is_company && store.pageMode === 'trash'"
                        class="text-destructive"
                        @click="deleteBookmark(bookmark.id)"
                    >
                        <Trash2 class="mr-2 size-4" />
                        Delete Permanently
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    </div>

    <div
        v-else
        class="group relative flex flex-col overflow-hidden rounded-xl border bg-card transition-colors hover:bg-accent/30"
    >
        <div class="absolute right-3 top-3 z-10 flex items-center gap-1">
            <Button
                variant="secondary"
                size="icon"
                class="h-8 w-8 bg-background/80 backdrop-blur-sm"
                @click="toggleFavorite(bookmark.id)"
            >
                <Heart
                    class="size-4"
                    :class="{
                        'fill-red-500 text-red-500': bookmark.is_favorite,
                    }"
                />
            </Button>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <Button
                        variant="secondary"
                        size="icon"
                        class="h-8 w-8 bg-background/80 backdrop-blur-sm"
                    >
                        <MoreHorizontal class="size-4" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    <DropdownMenuItem @click="handleCopyUrl">
                        <Copy class="mr-2 size-4" />
                        Copy URL
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="handleOpenUrl">
                        <ExternalLink class="mr-2 size-4" />
                        Open in new tab
                    </DropdownMenuItem>
                    <DropdownMenuItem v-if="!bookmark.is_company" @click="openBookmarkModal(bookmark)">
                        <Pencil class="mr-2 size-4" />
                        Edit
                    </DropdownMenuItem>
                    <DropdownMenuItem v-if="!bookmark.is_company" @click="openBookmarkModal(bookmark)">
                        <Tag class="mr-2 size-4" />
                        Add Tags
                    </DropdownMenuItem>
                    <DropdownMenuSeparator v-if="!bookmark.is_company" />
                    
                    <!-- Archive/Unarchive -->
                    <DropdownMenuItem
                        v-if="!bookmark.is_company && store.pageMode !== 'trash' && (!bookmark.status || bookmark.status === 'active')"
                        @click="archiveBookmark(bookmark.id)"
                    >
                        <Archive class="mr-2 size-4" />
                        Archive
                    </DropdownMenuItem>
                    <DropdownMenuItem
                        v-if="!bookmark.is_company && store.pageMode === 'archive'"
                        @click="unarchiveBookmark(bookmark.id)"
                    >
                        <RotateCcw class="mr-2 size-4" />
                        Unarchive
                    </DropdownMenuItem>
                    
                    <!-- Trash/Restore -->
                    <DropdownMenuItem
                        v-if="!bookmark.is_company && store.pageMode !== 'trash' && store.pageMode !== 'archive'"
                        @click="trashBookmark(bookmark.id)"
                    >
                        <Trash2 class="mr-2 size-4" />
                        Move to Trash
                    </DropdownMenuItem>
                    <DropdownMenuItem
                        v-if="!bookmark.is_company && store.pageMode === 'trash'"
                        @click="restoreBookmark(bookmark.id)"
                    >
                        <RotateCcw class="mr-2 size-4" />
                        Restore
                    </DropdownMenuItem>
                    
                    <!-- Permanent Delete -->
                    <DropdownMenuItem
                        v-if="!bookmark.is_company && store.pageMode === 'trash'"
                        class="text-destructive"
                        @click="deleteBookmark(bookmark.id)"
                    >
                        <Trash2 class="mr-2 size-4" />
                        Delete Permanently
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>

        <TooltipProvider>
            <Tooltip>
                <TooltipTrigger as-child>
                    <button class="w-full cursor-pointer text-left" @click="handleOpenUrl">
                        <div
                            class="relative flex h-32 items-center justify-center overflow-hidden bg-gradient-to-br from-muted/50 to-muted group-hover:from-muted group-hover:to-muted/30 transition-all duration-500"
                        >
                            <!-- Image Background -->
                            <div 
                                v-if="bookmark.image_url"
                                class="absolute inset-0 z-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-110"
                                :style="{ backgroundImage: `url(${bookmark.image_url})` }"
                            ></div>
                            <!-- Theme Overlay -->
                            <div 
                                v-if="bookmark.image_url"
                                class="absolute inset-0 z-1 bg-black/5 dark:bg-black/20 group-hover:bg-transparent transition-colors duration-500 shadow-inner"
                            ></div>
                            
                            <div
                                class="relative z-10 flex size-12 items-center justify-center rounded-xl bg-background/90 backdrop-blur-sm shadow-lg ring-1 ring-white/20"
                            >
                                <img
                                    :src="bookmark.favicon || '/placeholder-icon.svg'"
                                    :alt="bookmark.title"
                                    width="32"
                                    height="32"
                                    class="size-8"
                                />
                            </div>
                        </div>

                        <div class="p-3 flex items-center justify-between gap-2 overflow-hidden bg-muted/20">
                            <!-- Title with Black BG -->
                            <div class="flex shrink-0 max-w-[50%]">
                                <div class="bg-zinc-950 dark:bg-zinc-950 text-white px-2.5 py-1 rounded-md shadow-lg ring-1 ring-white/10 w-full">
                                    <h3 class="truncate font-bold text-[11px] uppercase tracking-wide leading-tight">{{ bookmark.title }}</h3>
                                </div>
                            </div>
                            
                            <!-- Tags to the Right -->
                            <div v-if="bookmarkTags.length > 0" class="flex flex-wrap justify-end gap-1 min-w-0">
                                <span
                                    v-for="tag in bookmarkTags.slice(0, 2)"
                                    :key="tag.id"
                                    class="inline-flex items-center rounded-md px-1.5 py-0.5 text-[9px] font-medium border border-transparent whitespace-nowrap"
                                    :class="getTagColorClass(tag.color)"
                                >
                                    {{ tag.name }}
                                </span>
                            </div>
                        </div>
                    </button>
                </TooltipTrigger>
                <TooltipContent side="bottom" align="center" class="max-w-[300px] p-3 space-y-1">
                    <p class="font-bold text-sm">{{ bookmark.title }}</p>
                    <p class="text-xs text-muted-foreground leading-relaxed">
                        {{ bookmark.description || 'No description available.' }}
                    </p>
                </TooltipContent>
            </Tooltip>
        </TooltipProvider>
    </div>
</template>

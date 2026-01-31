<script setup lang="ts">
import NavUser from '@/components/NavUser.vue';
import {
    Avatar,
    AvatarFallback,
    AvatarImage,
} from '@/components/ui/avatar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupContent,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useBookmarksStore } from '@/composables/useBookmarksStore';
import { cn } from '@/lib/utils';
import { Link, usePage } from '@inertiajs/vue3';
import { getIconColorClass, getTagColorClass } from '@/lib/colors';
import { logout } from '@/routes';
import * as LucideIcons from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import { computed, ref } from 'vue';

const isAdmin = computed(() => usePage().props.auth?.user?.is_admin);

const { state: store, setSelectedCollection, toggleTag, clearTags, setPageMode, setFilterType, openCollectionModal, openSearch } = useBookmarksStore();

const collectionsOpen = ref(true);
const tagsOpen = ref(true);

const collections = computed(() => store.collections);
const tags = computed(() => store.tags);


const getCollectionUrl = (collection: any) => {
    if (collection.slug) {
        return `/bookmarks/collection/${collection.slug}`;
    }
    return '/dashboard';
};


const isCollectionActive = (id: string) => store.pageMode === 'index' && store.selectedCollection === id;
const isNavItemActive = (href: string) => {
    if (href === '/bookmarks/favorites') return store.filterType === 'favorites' && store.pageMode === 'index';
    if (href === '/bookmarks/archive') return store.pageMode === 'archive';
    if (href === '/bookmarks/trash') return store.pageMode === 'trash';
    return false;
};

const getCollectionIcon = (iconName: string) => {
    if (!iconName) return LucideIcons.Folder;

    // Check direct match first (PascalCase)
    if ((LucideIcons as any)[iconName]) {
        return (LucideIcons as any)[iconName];
    }

    // Attempt normalized (kebab/space -> Pascal)
    if (iconName.includes('-') || iconName.includes(' ')) {
        const pascalName = iconName
            .split(/[- ]+/)
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join('');
        
        if ((LucideIcons as any)[pascalName]) {
            return (LucideIcons as any)[pascalName];
        }
    }

    return LucideIcons.Folder;
};


</script>

<template>
    <Sidebar collapsible="offcanvas" class="border-r lg:border-r-0">
        <SidebarHeader class="p-2 pb-0">
            <!--
            <div class="flex items-center justify-between">
                <DropdownMenu>
                    <DropdownMenuTrigger
                        class="flex items-center gap-2 outline-none"
                    >
                        <div
                            class="flex size-7 items-center justify-center overflow-hidden rounded-full bg-gradient-to-br from-blue-400 via-indigo-500 to-violet-500 shadow-lg ring-1 ring-white/40"
                        />
                        <span class="font-medium text-muted-foreground">
                            Square UI
                        </span>
                        <ChevronDown class="size-3 text-muted-foreground" />
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="start" class="w-56">
                        <DropdownMenuLabel
                            class="text-xs font-medium text-muted-foreground"
                        >
                            Workspaces
                        </DropdownMenuLabel>
                        <DropdownMenuItem>
                            <div
                                class="mr-2 size-5 rounded-full bg-gradient-to-br from-blue-400 via-indigo-500 to-violet-500"
                            />
                            Square UI
                            <Check class="ml-auto size-4" />
                        </DropdownMenuItem>
                        <DropdownMenuItem>
                            <div
                                class="mr-2 size-5 rounded-full bg-gradient-to-br from-emerald-400 to-cyan-500"
                            />
                            Personal
                        </DropdownMenuItem>
                        <DropdownMenuItem>
                            <div
                                class="mr-2 size-5 rounded-full bg-gradient-to-br from-orange-400 to-rose-500"
                            />
                            Work
                        </DropdownMenuItem>

                        <DropdownMenuSeparator />

                        <DropdownMenuItem>
                            <LucideIcons.Plus class="mr-2 size-4" />
                            Create Workspace
                        </DropdownMenuItem>

                        <DropdownMenuSeparator />

                        <DropdownMenuItem>
                            <LucideIcons.User class="mr-2 size-4" />
                            Account Settings
                        </DropdownMenuItem>
                        <DropdownMenuItem>
                            <LucideIcons.Settings class="mr-2 size-4" />
                            Workspace Settings
                        </DropdownMenuItem>

                        <DropdownMenuSeparator />

                        <DropdownMenuItem as-child>
                            <Link :href="logout()" method="post" as="button" class="flex w-full items-center text-destructive">
                                <LucideIcons.LogOut class="mr-2 size-4" />
                                Log out
                            </Link>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
            -->
            <Link href="/" class="px-4 py-2">
                <AppLogo />
            </Link>
        </SidebarHeader>

        <SidebarContent class="px-5 pt-5">
            <div class="relative mb-4">
                <LucideIcons.Search
                    class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    placeholder="Search Bookmarks..."
                    class="h-9 bg-background pl-9 pr-10 cursor-pointer"
                    readonly
                    @click="openSearch"
                />
                <div
                    class="absolute right-2 top-1/2 -translate-y-1/2 rounded bg-muted px-1.5 py-0.5 text-[11px] font-medium text-muted-foreground"
                >
                    ⌘K
                </div>
            </div>

            <SidebarGroup class="p-0">
                <SidebarGroupLabel
                    class="flex items-center gap-1.5 px-0 text-[10px] font-semibold tracking-wider text-muted-foreground"
                >
                    <div class="flex flex-1 items-center gap-1.5">
                        <button
                            class="flex cursor-pointer items-center gap-1.5"
                            @click="collectionsOpen = !collectionsOpen"
                        >
                            <LucideIcons.ChevronDown
                                class="size-3.5 transition-transform"
                                :class="{ '-rotate-90': !collectionsOpen }"
                            />
                            COLLECTIONS
                        </button>
                    </div>
                    <button 
                        class="ml-auto flex size-5 items-center justify-center rounded-md hover:bg-muted"
                        @click.stop="openCollectionModal()"
                    >
                        <LucideIcons.Plus class="size-3" />
                    </button>
                </SidebarGroupLabel>
                <transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="translate-y-[-10px] opacity-0"
                    enter-to-class="translate-y-0 opacity-100"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="translate-y-0 opacity-100"
                    leave-to-class="translate-y-[-10px] opacity-0"
                >
                <SidebarGroupContent v-if="collectionsOpen">
                    <SidebarMenu class="mt-2">
                        <!-- All Bookmarks -->
                        <SidebarMenuItem>
                            <SidebarMenuButton
                                as-child
                                :is-active="store.selectedCollection === 'all' && store.pageMode === 'index'"
                                class="h-[38px]"
                            >
                                <Link href="/bookmarks/all">
                                    <LucideIcons.LayoutGrid class="size-5" />
                                    <span class="flex-1">All Bookmarks</span>
                                    <span class="text-xs text-muted-foreground">
                                        {{ store.bookmarks.filter(b => !b.status || b.status === 'active').length }}
                                    </span>
                                    <LucideIcons.ChevronRight
                                        v-if="store.selectedCollection === 'all' && store.pageMode === 'index'"
                                        class="size-4 text-muted-foreground opacity-60"
                                    />
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                        
                        <!-- User Collections -->
                        <SidebarMenuItem
                            v-for="collection in collections"
                            :key="collection.id"
                        >
                            <SidebarMenuButton
                                as-child
                                :is-active="isCollectionActive(String(collection.id))"
                                class="h-[38px]"
                            >
                                <Link :href="getCollectionUrl(collection)">
                                    <component
                                        :is="getCollectionIcon(collection.icon)"
                                        class="size-5"
                                        :class="getIconColorClass(collection.color)"
                                    />
                                    <span class="flex-1">{{ collection.name }}</span>
                                    <span class="text-xs text-muted-foreground">
                                        {{ collection.count }}
                                    </span>
                                    <LucideIcons.ChevronRight
                                        v-if="isCollectionActive(String(collection.id))"
                                        class="size-4 text-muted-foreground opacity-60"
                                    />
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarGroupContent>
                </transition>
            </SidebarGroup>

            <SidebarGroup class="p-0">
                <SidebarGroupLabel
                    class="flex items-center gap-1.5 px-0 text-[10px] font-semibold tracking-wider text-muted-foreground"
                >
                    <button
                        class="flex cursor-pointer items-center gap-1.5"
                        @click="tagsOpen = !tagsOpen"
                    >
                        <LucideIcons.ChevronDown
                            class="size-3.5 transition-transform"
                            :class="{ '-rotate-90': !tagsOpen }"
                        />
                        TAGS
                    </button>
                    <button
                        v-if="store.selectedTags.length > 0"
                        class="ml-auto text-[10px] text-muted-foreground hover:text-foreground"
                        @click.stop="clearTags"
                    >
                        Clear
                    </button>
                </SidebarGroupLabel>
                <transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="translate-y-[-10px] opacity-0"
                    enter-to-class="translate-y-0 opacity-100"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="translate-y-0 opacity-100"
                    leave-to-class="translate-y-[-10px] opacity-0"
                >
                <SidebarGroupContent v-if="tagsOpen">
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        <button
                            v-for="tag in tags"
                            :key="tag.id"
                            class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium transition-colors"
                            :class="
                                store.selectedTags.includes(tag.id)
                                    ? 'bg-primary text-primary-foreground'
                                    : [getTagColorClass(tag.color), 'hover:opacity-80 border-transparent']
                            "
                            @click="toggleTag(tag.id)"
                        >
                            <LucideIcons.Tag class="size-3" />
                            {{ tag.name }}
                        </button>
                    </div>
                </SidebarGroupContent>
                </transition>
            </SidebarGroup>

            <SidebarGroup class="p-0">
                <SidebarGroupContent>
                    <SidebarMenu>
                        <SidebarMenuItem>
                            <SidebarMenuButton
                                as-child
                                :is-active="isNavItemActive('/bookmarks/favorites')"
                                class="h-[38px]"
                            >
                                <Link href="/bookmarks/favorites">
                                    <LucideIcons.Star class="size-5" />
                                    <span>Favorites</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                        <SidebarMenuItem>
                            <SidebarMenuButton
                                as-child
                                :is-active="isNavItemActive('/bookmarks/archive')"
                                class="h-[38px]"
                            >
                                <Link href="/bookmarks/archive">
                                    <LucideIcons.Archive class="size-5" />
                                    <span>Archive</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                        <SidebarMenuItem>
                            <SidebarMenuButton
                                as-child
                                :is-active="isNavItemActive('/bookmarks/trash')"
                                class="h-[38px]"
                            >
                                <Link href="/bookmarks/trash">
                                    <LucideIcons.Trash2 class="size-5" />
                                    <span>Trash</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarGroupContent>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
</template>

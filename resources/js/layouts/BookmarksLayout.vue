<script setup lang="ts">
import { SidebarProvider } from '@/components/ui/sidebar';
import BookmarksSidebar from '@/pages/Bookmarks/partials/BookmarksSidebar.vue';
import CommandMenu from '@/components/CommandMenu.vue';
</script>

<template>
    <SidebarProvider class="bg-sidebar">
        <BookmarksSidebar />
        <div class="h-svh w-full overflow-hidden lg:p-2">
            <div
                class="flex h-full w-full flex-col items-center justify-start overflow-hidden bg-background lg:rounded-md lg:border"
            >
                <slot />
            </div>
        </div>
        <CommandMenu />
    </SidebarProvider>
</template>

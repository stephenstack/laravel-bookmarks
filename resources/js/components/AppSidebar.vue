<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Settings2, Users } from 'lucide-vue-next';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import AppLogo from './AppLogo.vue';

const page = usePage();

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Bookmarks',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    if (page.props.auth?.user?.is_admin) {
        items.push({
            title: 'Site Settings',
            href: '/admin/settings',
            icon: Settings2,
        });
        items.push({
            title: 'User Management',
            href: '/admin/users',
            icon: Users,
        });
    }

    return items;
});

const footerNavItems = computed<NavItem[]>(() => [
    {
        title: 'Github Repo',
        href: (page.props.site_settings as any)?.repo_url || 'https://github.com/stephenstack/laravel-bookmarks',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: (page.props.site_settings as any)?.repo_url || 'https://github.com/stephenstack/laravel-bookmarks',
        icon: BookOpen,
    },
]);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <Link href="/" class="px-4 py-2">
            <AppLogo />
        </Link>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>

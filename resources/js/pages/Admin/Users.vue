<script setup lang="ts">
import { computed, reactive } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';

type UserRow = {
    id: number;
    name: string;
    email: string;
    is_admin: boolean;
    created_at: string | null;
    last_login_at: string | null;
};

const props = defineProps<{
    users: UserRow[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Settings',
        href: '/admin/settings',
    },
    {
        title: 'User Management',
        href: '/admin/users',
    },
];

const page = usePage();
const currentUserId = computed(() => (page.props.auth as any)?.user?.id);

const inviteForm = useForm({
    email: '',
    name: '',
});

const passwordState = reactive<Record<number, { password: string; confirm: string; error?: string }>>({});

const ensureState = (id: number) => {
    if (!passwordState[id]) {
        passwordState[id] = { password: '', confirm: '' };
    }
    return passwordState[id];
};

const formatDate = (value: string | null) => {
    if (!value) return '—';
    const date = new Date(value);
    return Number.isNaN(date.getTime()) ? '—' : date.toLocaleString();
};

const toggleAdmin = (user: UserRow, value: boolean) => {
    router.patch(
        `/admin/users/${user.id}`,
        { is_admin: value },
        {
            preserveScroll: true,
            onError: (errors) => {
                const state = ensureState(user.id);
                state.error = errors.is_admin || 'Failed to update admin status.';
            },
        },
    );
};

const updatePassword = (user: UserRow) => {
    const state = ensureState(user.id);
    state.error = undefined;

    if (!state.password) {
        state.error = 'Password is required.';
        return;
    }

    router.patch(
        `/admin/users/${user.id}`,
        {
            password: state.password,
            password_confirmation: state.confirm,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                state.password = '';
                state.confirm = '';
            },
            onError: (errors) => {
                state.error = errors.password || errors.password_confirmation || 'Failed to update password.';
            },
        },
    );
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="User Management" />

        <div class="flex flex-col gap-6 px-6 py-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">User Management</h1>
                <p class="text-muted-foreground mt-2 max-w-2xl">
                    Manage user access, admin roles, and reset passwords.
                </p>
            </div>

            <div class="rounded-xl border bg-card p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div class="space-y-1">
                        <h2 class="text-base font-semibold">Invite a user</h2>
                        <p class="text-sm text-muted-foreground">
                            Sends a password setup email to the new user.
                        </p>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <Input
                                v-model="inviteForm.name"
                                placeholder="Name (optional)"
                                class="sm:w-48"
                            />
                            <Input
                                v-model="inviteForm.email"
                                placeholder="Email address"
                                type="email"
                                class="sm:w-56"
                            />
                        </div>
                        <Button
                            type="button"
                            :disabled="inviteForm.processing || !inviteForm.email"
                            @click="inviteForm.post('/admin/users/invite', { preserveScroll: true, onSuccess: () => inviteForm.reset() })"
                        >
                            Send invite
                        </Button>
                    </div>
                </div>
                <p v-if="inviteForm.errors.email" class="mt-2 text-xs text-destructive">
                    {{ inviteForm.errors.email }}
                </p>
                <p v-if="inviteForm.recentlySuccessful" class="mt-2 text-xs text-emerald-600">
                    Invite sent.
                </p>
            </div>

            <div class="rounded-xl border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b text-left text-muted-foreground">
                            <tr>
                                <th class="px-4 py-3 font-medium">User</th>
                                <th class="px-4 py-3 font-medium">Email</th>
                                <th class="px-4 py-3 font-medium">Created</th>
                                <th class="px-4 py-3 font-medium">Last Login</th>
                                <th class="px-4 py-3 font-medium">Admin</th>
                                <th class="px-4 py-3 font-medium">Password</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in props.users" :key="user.id" class="border-b last:border-b-0">
                                <td class="px-4 py-4">
                                    <div class="font-medium">{{ user.name }}</div>
                                    <div class="text-xs text-muted-foreground">ID {{ user.id }}</div>
                                </td>
                                <td class="px-4 py-4">{{ user.email }}</td>
                                <td class="px-4 py-4">{{ formatDate(user.created_at) }}</td>
                                <td class="px-4 py-4">{{ formatDate(user.last_login_at) }}</td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <Checkbox
                                            :checked="user.is_admin"
                                            :disabled="currentUserId === user.id && user.is_admin"
                                            @update:checked="(value: boolean) => toggleAdmin(user, value)"
                                        />
                                        <Badge v-if="user.is_admin" variant="secondary">Admin</Badge>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-col gap-2">
                                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                            <Input
                                                v-model="ensureState(user.id).password"
                                                type="password"
                                                placeholder="New password"
                                                class="sm:w-48"
                                            />
                                            <Input
                                                v-model="ensureState(user.id).confirm"
                                                type="password"
                                                placeholder="Confirm"
                                                class="sm:w-48"
                                            />
                                            <Button type="button" size="sm" @click="updatePassword(user)">
                                                Update
                                            </Button>
                                        </div>
                                        <p v-if="ensureState(user.id).error" class="text-xs text-destructive">
                                            {{ ensureState(user.id).error }}
                                        </p>
                                        <p v-if="currentUserId === user.id" class="text-xs text-muted-foreground">
                                            You cannot remove your own admin access.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="props.users.length === 0">
                                <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">
                                    No users found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

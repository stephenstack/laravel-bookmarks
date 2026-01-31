<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
import { Trash2, Plus, Save, Building, Globe, Settings as SettingsIcon, LayoutTemplate } from 'lucide-vue-next';



const props = defineProps<{
    settings: Record<string, any>;
    companyBookmarks: Array<any>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Settings',
        href: '/admin/settings',
    },
];

const activeTab = ref('general');

const settingsForm = useForm({
    site_title: props.settings.site_title || '',
    site_repo_url: props.settings.site_repo_url || 'https://github.com/stephenstack/laravel-bookmarks',
    site_logo_light: props.settings.site_logo_light || '',
    site_logo_dark: props.settings.site_logo_dark || '',
    site_favicon_light: props.settings.site_favicon_light || '',
    site_favicon_dark: props.settings.site_favicon_dark || '',
    company_collection_title: props.settings.company_collection_title || '',
    company_collection_color: props.settings.company_collection_color || '',
    company_collection_icon: props.settings.company_collection_icon || '',
    background_image: props.settings.background_image || '',
    background_opacity: props.settings.background_opacity || 100,
});

const bookmarksForm = useForm({
    bookmarks: props.companyBookmarks || [],
});

const addBookmark = () => {
    bookmarksForm.bookmarks.push({
        id: null,
        title: '',
        url: '',
        description: '',
    });
};

const removeBookmark = (index: number) => {
    bookmarksForm.bookmarks.splice(index, 1);
};

const saveSettings = () => {
    settingsForm.post('/admin/settings', {
        preserveScroll: true,
    });
};

const saveBookmarks = () => {
    bookmarksForm.post('/admin/company-bookmarks', {
        preserveScroll: true,
    });
};

watch(
    () => settingsForm.company_collection_icon,
    (newValue) => {
        if (newValue && (newValue.includes('-') || newValue.includes(' '))) {
             settingsForm.company_collection_icon = newValue
                .split(/[- ]+/)
                .map((word: string) => word.charAt(0).toUpperCase() + word.slice(1))
                .join('');
        }
    }
);

const availableColors = [
    { name: 'slate', class: 'bg-slate-500' },
    { name: 'gray', class: 'bg-gray-500' },
    { name: 'zinc', class: 'bg-zinc-500' },
    { name: 'neutral', class: 'bg-neutral-500' },
    { name: 'stone', class: 'bg-stone-500' },
    { name: 'red', class: 'bg-red-500' },
    { name: 'orange', class: 'bg-orange-500' },
    { name: 'amber', class: 'bg-amber-500' },
    { name: 'yellow', class: 'bg-yellow-500' },
    { name: 'lime', class: 'bg-lime-500' },
    { name: 'green', class: 'bg-green-500' },
    { name: 'emerald', class: 'bg-emerald-500' },
    { name: 'teal', class: 'bg-teal-500' },
    { name: 'cyan', class: 'bg-cyan-500' },
    { name: 'sky', class: 'bg-sky-500' },
    { name: 'blue', class: 'bg-blue-500' },
    { name: 'indigo', class: 'bg-indigo-500' },
    { name: 'violet', class: 'bg-violet-500' },
    { name: 'purple', class: 'bg-purple-500' },
    { name: 'fuchsia', class: 'bg-fuchsia-500' },
    { name: 'pink', class: 'bg-pink-500' },
    { name: 'rose', class: 'bg-rose-500' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Site Settings" />
        <div class="p-6 max-w-5xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Admin Settings</h1>
                    <p class="text-muted-foreground">Manage global site configuration and company resources.</p>
                </div>
            </div>

            <!-- Custom Tabs -->
            <div class="space-y-6">
                <div class="flex items-center space-x-1 border-b">
                    <button
                        @click="activeTab = 'general'"
                        :class="[
                            'px-4 py-2 text-sm font-medium transition-colors border-b-2 -mb-px hover:text-foreground',
                            activeTab === 'general' ? 'border-primary text-foreground' : 'border-transparent text-muted-foreground'
                        ]"
                    >
                        <div class="flex items-center gap-2">
                            <Globe class="size-4" />
                            General
                        </div>
                    </button>
                    <button
                        @click="activeTab = 'company'"
                        :class="[
                            'px-4 py-2 text-sm font-medium transition-colors border-b-2 -mb-px hover:text-foreground',
                            activeTab === 'company' ? 'border-primary text-foreground' : 'border-transparent text-muted-foreground'
                        ]"
                    >
                        <div class="flex items-center gap-2">
                            <Building class="size-4" />
                            Company Collection
                        </div>
                    </button>
                    <button
                        @click="activeTab = 'bookmarks'"
                        :class="[
                            'px-4 py-2 text-sm font-medium transition-colors border-b-2 -mb-px hover:text-foreground',
                            activeTab === 'bookmarks' ? 'border-primary text-foreground' : 'border-transparent text-muted-foreground'
                        ]"
                    >
                        <div class="flex items-center gap-2">
                            <LayoutTemplate class="size-4" />
                            Company Bookmarks
                        </div>
                    </button>
                </div>

                <!-- General Tab -->
                <div v-if="activeTab === 'general'" class="space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Site Identity</CardTitle>
                            <CardDescription>Configure the main branding elements of the platform.</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-2">
                                <Label>Site Title</Label>
                                <Input v-model="settingsForm.site_title" placeholder="Platform Name" />
                            </div>
                            <div class="grid gap-2">
                                <Label>Repository URL</Label>
                                <Input v-model="settingsForm.site_repo_url" placeholder="https://github.com/..." />
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="grid gap-2">
                                    <Label>Logo (Light Mode)</Label>
                                    <div v-if="typeof settingsForm.site_logo_light === 'string' && settingsForm.site_logo_light" class="mb-2">
                                        <img :src="settingsForm.site_logo_light" alt="Light Logo" class="h-12 object-contain border p-1 rounded bg-white" />
                                    </div>
                                    <Input type="file" @change="(e: any) => settingsForm.site_logo_light = e.target.files[0]" accept="image/*" />
                                    <p class="text-xs text-muted-foreground">Upload PNG, JPG, or SVG.</p>
                                </div>
                                <div class="grid gap-2">
                                    <Label>Logo (Dark Mode)</Label>
                                    <div v-if="typeof settingsForm.site_logo_dark === 'string' && settingsForm.site_logo_dark" class="mb-2">
                                        <img :src="settingsForm.site_logo_dark" alt="Dark Logo" class="h-12 object-contain border p-1 rounded bg-gray-900" />
                                    </div>
                                    <Input type="file" @change="(e: any) => settingsForm.site_logo_dark = e.target.files[0]" accept="image/*" />
                                    <p class="text-xs text-muted-foreground">Upload PNG, JPG, or SVG.</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="grid gap-2">
                                    <Label>Favicon (Light Mode)</Label>
                                    <div v-if="typeof settingsForm.site_favicon_light === 'string' && settingsForm.site_favicon_light" class="mb-2">
                                        <img :src="settingsForm.site_favicon_light" alt="Light Favicon" class="h-8 w-8 object-contain border p-1 rounded bg-white" />
                                    </div>
                                    <Input type="file" @change="(e: any) => settingsForm.site_favicon_light = e.target.files[0]" accept=".ico,.png,.svg,.jpg" />
                                </div>
                                <div class="grid gap-2">
                                    <Label>Favicon (Dark Mode)</Label>
                                    <div v-if="typeof settingsForm.site_favicon_dark === 'string' && settingsForm.site_favicon_dark" class="mb-2">
                                        <img :src="settingsForm.site_favicon_dark" alt="Dark Favicon" class="h-8 w-8 object-contain border p-1 rounded bg-gray-900" />
                                    </div>
                                    <Input type="file" @change="(e: any) => settingsForm.site_favicon_dark = e.target.files[0]" accept=".ico,.png,.svg,.jpg" />
                                </div>
                            </div>
                        </CardContent>
                        <CardFooter class="flex items-center gap-4">
                            <Button @click="saveSettings" :disabled="settingsForm.processing">
                                <Save class="mr-2 size-4" /> Save Site Identity
                            </Button>
                            <p v-if="settingsForm.recentlySuccessful" class="text-sm text-amber-600 dark:text-amber-400 font-medium">
                                Settings saved. Apply Refresh.
                            </p>
                        </CardFooter>
                    </Card>


                </div>

                <!-- Company Collection Tab -->
                <div v-if="activeTab === 'company'" class="space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Company Collection</CardTitle>
                            <CardDescription>A common shared collection for all users. Defines how the 'Company Resources' section appears.</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-2">
                                <Label>Collection Title</Label>
                                <Input v-model="settingsForm.company_collection_title" placeholder="Default: Company Resources" />
                            </div>
                            <div class="grid gap-2">
                                <Label>Collection Icon</Label>
                                <Input v-model="settingsForm.company_collection_icon" placeholder="Lucide Icon Name (e.g. Building)" />
                                <p class="text-xs text-muted-foreground">
                                    Browse available icons at <a href="https://lucide.dev/icons" target="_blank" rel="noopener noreferrer" class="underline text-primary hover:text-primary/80">lucide.dev/icons</a>
                                </p>
                            </div>
                             <div class="grid gap-2">
                                <Label>Color Theme</Label>
                                <div class="flex flex-wrap gap-2 mb-2">
                                    <button
                                        v-for="color in availableColors"
                                        :key="color.name"
                                        type="button"
                                        @click="settingsForm.company_collection_color = color.name"
                                        :class="[
                                            'size-6 rounded-full border transition-all',
                                            color.class,
                                            settingsForm.company_collection_color === color.name 
                                                ? 'ring-2 ring-primary ring-offset-2 scale-110' 
                                                : 'hover:scale-110 opacity-80 hover:opacity-100'
                                        ]"
                                        :title="color.name"
                                    />
                                </div>
                                <Input v-model="settingsForm.company_collection_color" placeholder="e.g. blue, red, emerald" />
                            </div>
                        </CardContent>

                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Appearance</CardTitle>
                            <CardDescription>Global background settings.</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                             <div class="grid gap-2">
                                <Label>Background Image</Label>
                                <div v-if="typeof settingsForm.background_image === 'string' && settingsForm.background_image" class="relative aspect-video w-full max-w-sm rounded-lg overflow-hidden border mb-2 group">
                                     <img :src="settingsForm.background_image" class="w-full h-full object-cover" />
                                     <button 
                                        type="button" 
                                        @click="settingsForm.background_image = null"
                                        class="absolute top-2 right-2 bg-destructive text-destructive-foreground p-1 px-2 rounded text-xs opacity-0 group-hover:opacity-100 transition-opacity"
                                     >
                                        Remove Image
                                     </button>
                                </div>
                                <input 
                                    type="file" 
                                    @change="(e: any) => settingsForm.background_image = e.target.files[0]" 
                                    accept="image/*"
                                    class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                />
                                <p class="text-xs text-muted-foreground mt-1">
                                    We recommend <a href="https://freepik.com" target="_blank" rel="noopener noreferrer" class="underline text-primary hover:text-primary/80">Freepik</a> for high-quality stock images. (Max 64MB)
                                    <br>
                                    <span class="text-amber-600 dark:text-amber-500">
                                        Note: If files fail to update, check your system PHP upload limits.
                                        See <a :href="(settingsForm.site_repo_url || 'https://github.com/stephenstack/laravel-bookmarks') + '#troubleshooting'" target="_blank" rel="noopener noreferrer" class="underline text-primary hover:text-primary/80">README</a>.
                                    </span>
                                </p>
                            </div>
                             <div class="grid gap-2">
                                <div class="flex items-center justify-between">
                                    <Label>Background Opacity</Label>
                                    <span class="text-sm font-medium">{{ settingsForm.background_opacity }}%</span>
                                </div>
                                <input 
                                    type="range" 
                                    min="0" 
                                    max="100" 
                                    step="1"
                                    v-model="settingsForm.background_opacity" 
                                    class="w-full h-2 bg-secondary rounded-lg appearance-none cursor-pointer accent-primary"
                                />
                                <p class="text-xs text-muted-foreground">Adjust opacity to blend the background image overlay.</p>
                            </div>
                        </CardContent>
                        <CardFooter class="flex items-center gap-4">
                            <Button @click="saveSettings" :disabled="settingsForm.processing">
                                <Save class="mr-2 size-4" /> Save Changes
                            </Button>
                             <p v-if="settingsForm.recentlySuccessful" class="text-sm text-amber-600 dark:text-amber-400 font-medium">
                                Settings saved. Apply Refresh.
                            </p>
                        </CardFooter>
                    </Card>
                </div>

                <!-- Bookmarks Tab -->
                <div v-if="activeTab === 'bookmarks'" class="space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Company Bookmarks</CardTitle>
                            <CardDescription>These bookmarks appear for EVERY user in the company collection.</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div v-if="bookmarksForm.bookmarks.length === 0" class="text-center py-8 text-muted-foreground">
                                No company bookmarks defined yet.
                            </div>
                            <div v-for="(bookmark, index) in bookmarksForm.bookmarks" :key="index" class="p-4 border rounded-lg bg-card/50 space-y-4">
                                <div class="flex items-start justify-between gap-4">
                                     <div class="grid gap-4 flex-1 md:grid-cols-2">
                                        <div class="grid gap-2">
                                            <Label>Title</Label>
                                            <Input v-model="bookmark.title" placeholder="Bookmark Title" />
                                        </div>
                                        <div class="grid gap-2">
                                            <Label>URL</Label>
                                            <Input v-model="bookmark.url" placeholder="https://example.com" />
                                        </div>
                                        <div class="col-span-2 grid gap-2">
                                            <Label>Description</Label>
                                            <Input v-model="bookmark.description" placeholder="Optional description" />
                                        </div>
                                    </div>
                                    <Button variant="destructive" size="icon" @click="removeBookmark(index)" class="shrink-0 mt-8">
                                        <Trash2 class="size-4" />
                                    </Button>
                                </div>
                            </div>
                            <Button variant="outline" @click="addBookmark" class="w-full border-dashed">
                                <Plus class="mr-2 size-4" /> Add New Bookmark
                            </Button>
                        </CardContent>
                        <CardFooter>
                             <Button @click="saveBookmarks" :disabled="bookmarksForm.processing">
                                <Save class="mr-2 size-4" /> Save Bookmarks
                            </Button>
                        </CardFooter>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
import { Trash2, Plus, Save, Building, Globe, Mail, AlertTriangle, Send, LayoutTemplate, Search, Loader2, Wand2 } from 'lucide-vue-next';
import axios from 'axios';



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

const getTabFromUrl = () => {
    if (typeof window === 'undefined') return 'general';
    const params = new URLSearchParams(window.location.search);
    return params.get('tab') || 'general';
};

const activeTab = ref(getTabFromUrl());

watch(activeTab, (newTab) => {
    const url = new URL(window.location.href);
    url.searchParams.set('tab', newTab);
    window.history.replaceState({}, '', url);
});

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
    company_tag_name: props.settings.company_tag_name || 'Company',
    company_tag_color: props.settings.company_tag_color || 'blue',
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
        favicon: '',
        image_url: '',
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

const interrogatingIndex = ref<number | null>(null);

const interrogate = async (index: number) => {
    const bookmark = bookmarksForm.bookmarks[index];
    if (!bookmark.url) return;

    interrogatingIndex.value = index;
    
    try {
        const response = await axios.post('/admin/interrogate-url', { url: bookmark.url });
        const data = response.data;
        
        bookmark.title = data.title || bookmark.title;
        bookmark.description = data.description || bookmark.description;
        bookmark.favicon = data.favicon || bookmark.favicon;
        bookmark.image_url = data.image_url || bookmark.image_url;
        
        // Auto-save the whole collection after interrogation
        saveBookmarks();
    } catch (error: any) {
        console.error('Interrogation failed:', error);
        alert(error.response?.data?.error || 'Failed to interrogate URL');
    } finally {
        interrogatingIndex.value = null;
    }
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

const testEmailAddress = ref('');
const testingEmail = ref(false);

const sendTestEmail = async () => {
    if (!testEmailAddress.value) return;
    testingEmail.value = true;
    try {
        await axios.post('/admin/test-email', { email: testEmailAddress.value });
        alert('Test email sent! Check your inbox.');
    } catch (e: any) {
        alert(e.response?.data?.error || 'Failed to send test email. Check your .env configuration.');
    } finally {
        testingEmail.value = false;
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Site Settings" />
        <div class="p-6 max-w-7xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Admin Settings</h1>
                    <p class="text-muted-foreground">Manage global site configuration and company resources.</p>
                </div>
            </div>
            <div v-if="settings.setup_required" class="bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900 rounded-xl p-4 flex gap-4 items-start animate-in fade-in slide-in-from-top-4 duration-500">
                <div class="size-10 rounded-full bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center shrink-0">
                    <AlertTriangle class="size-5 text-amber-600 dark:text-amber-400" />
                </div>
                <div>
                    <h3 class="font-bold text-amber-900 dark:text-amber-100 uppercase tracking-wider text-xs">Initial Setup Required</h3>
                    <p class="text-sm text-amber-800 dark:text-amber-200 mt-1 max-w-2xl">
                        To complete your installation, you must configure your <span class="font-bold">Company Collection</span> name and add at least one <span class="font-bold">Company Bookmark</span>.
                        Other areas of the site will remain restricted until this is complete.
                    </p>
                </div>
            </div>

            <!-- Custom Tabs -->
            <div class="space-y-6">
                <div class="flex items-center space-x-1 border-b">
                    <button
                        @click="activeTab = 'general'"
                        :class="[
                            'w-48 py-2.5 text-sm font-medium transition-all border-b-2 -mb-px hover:text-foreground flex justify-center',
                            activeTab === 'general' ? 'border-primary text-foreground bg-primary/5' : 'border-transparent text-muted-foreground'
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
                            'w-48 py-2.5 text-sm font-medium transition-all border-b-2 -mb-px hover:text-foreground flex justify-center',
                            activeTab === 'company' ? 'border-primary text-foreground bg-primary/5' : 'border-transparent text-muted-foreground'
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
                            'w-48 py-2.5 text-sm font-medium transition-all border-b-2 -mb-px hover:text-foreground flex justify-center',
                            activeTab === 'bookmarks' ? 'border-primary text-foreground bg-primary/5' : 'border-transparent text-muted-foreground'
                        ]"
                    >
                        <div class="flex items-center gap-2">
                            <LayoutTemplate class="size-4" />
                            Company Bookmarks
                        </div>
                    </button>
                    <button
                        @click="activeTab = 'mail'"
                        :class="[
                            'w-48 py-2.5 text-sm font-medium transition-all border-b-2 -mb-px hover:text-foreground flex justify-center',
                            activeTab === 'mail' ? 'border-primary text-foreground bg-primary/5' : 'border-transparent text-muted-foreground'
                        ]"
                    >
                        <div class="flex items-center gap-2">
                            <Mail class="size-4" />
                            Mail Settings
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
                            <CardTitle>Company Tag</CardTitle>
                            <CardDescription>Configure the automatic tag applied to all company bookmarks.</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-2">
                                <Label>Tag Name</Label>
                                <Input v-model="settingsForm.company_tag_name" placeholder="e.g. COMPANY" />
                            </div>
                            <div class="grid gap-2">
                                <Label>Tag Color</Label>
                                <div class="flex flex-wrap gap-2 mb-2">
                                    <button
                                        v-for="color in availableColors"
                                        :key="color.name"
                                        type="button"
                                        @click="settingsForm.company_tag_color = color.name"
                                        class="size-6 rounded-full border transition-all"
                                        :class="[
                                            color.class,
                                            settingsForm.company_tag_color === color.name ? 'ring-2 ring-primary ring-offset-2' : 'hover:scale-110'
                                        ]"
                                        :title="color.name"
                                    />
                                </div>
                                <Input v-model="settingsForm.company_tag_color" placeholder="e.g. blue, red, emerald" />
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
                            <div class="flex items-center justify-between">
                                <div>
                                    <CardTitle>Company Bookmarks</CardTitle>
                                    <CardDescription>These bookmarks appear for EVERY user in the company collection.</CardDescription>
                                </div>
                                <div v-if="interrogatingIndex !== null" class="flex items-center gap-2 text-sm text-amber-600 animate-pulse">
                                    <Loader2 class="size-4 animate-spin" />
                                    Interrogating page... This may take a minute.
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div v-if="bookmarksForm.bookmarks.length === 0" class="text-center py-8 text-muted-foreground">
                                No company bookmarks defined yet.
                            </div>
                            <div v-for="(bookmark, index) in bookmarksForm.bookmarks" :key="index" class="p-4 border rounded-lg bg-card/50 space-y-4">
                                <div class="flex items-start justify-between gap-4">
                                     <div class="grid gap-4 flex-1 md:grid-cols-2 lg:grid-cols-4">
                                        <div class="grid gap-2">
                                            <Label>Title</Label>
                                            <Input v-model="bookmark.title" placeholder="Bookmark Title" />
                                        </div>
                                        <div class="grid gap-2">
                                            <Label>URL</Label>
                                            <div class="flex gap-2">
                                                <Input v-model="bookmark.url" placeholder="https://example.com" />
                                                <Button 
                                                    variant="secondary" 
                                                    size="icon" 
                                                    @click="interrogate(index)" 
                                                    :disabled="interrogatingIndex !== null"
                                                    title="Interrogate URL"
                                                >
                                                    <Wand2 v-if="interrogatingIndex !== index" class="size-4" />
                                                    <Loader2 v-else class="size-4 animate-spin" />
                                                </Button>
                                            </div>
                                        </div>
                                        <div class="grid gap-2">
                                            <Label>Favicon</Label>
                                            <div class="flex gap-2">
                                                <Input v-model="bookmark.favicon" placeholder="Icon URL" />
                                                <div v-if="bookmark.favicon" class="size-9 p-1 border rounded bg-white flex items-center justify-center">
                                                    <img :src="bookmark.favicon" class="size-full object-contain" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="grid gap-2">
                                            <Label>Image URL (OG / Screenshot)</Label>
                                            <Input v-model="bookmark.image_url" placeholder="Background/OG Image URL" />
                                        </div>
                                        <div class="md:col-span-2 lg:col-span-4 grid gap-2">
                                            <Label>Description</Label>
                                            <Input v-model="bookmark.description" placeholder="Optional description" />
                                        </div>
                                        <div v-if="bookmark.image_url" class="md:col-span-2 lg:col-span-4">
                                            <Label class="mb-1 block">Preview</Label>
                                            <div class="relative aspect-[21/9] w-full max-w-2xl rounded-lg overflow-hidden border">
                                                 <img :src="bookmark.image_url" class="w-full h-full object-cover" />
                                                 <div class="absolute inset-x-0 bottom-0 p-4 bg-gradient-to-t from-black/80 to-transparent text-white">
                                                     <div class="font-bold truncate">{{ bookmark.title }}</div>
                                                     <div class="text-xs opacity-80 truncate">{{ bookmark.description }}</div>
                                                 </div>
                                                 <button 
                                                     @click="bookmark.image_url = ''; saveBookmarks()" 
                                                     class="absolute top-2 right-2 size-8 flex items-center justify-center rounded-full bg-black/50 hover:bg-destructive text-white transition-colors backdrop-blur-sm"
                                                 >
                                                     <Trash2 class="size-4" />
                                                 </button>
                                            </div>
                                        </div>
                                    </div>
                                    <Button variant="destructive" size="icon" @click="removeBookmark(index)" class="shrink-0">
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

                <!-- Mail Tab -->
                <div v-if="activeTab === 'mail'" class="space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Mail Configuration</CardTitle>
                            <CardDescription>Configure SMTP settings for system notifications and password resets.</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div class="p-4 bg-muted/50 rounded-lg border text-sm space-y-3">
                                <h3 class="font-bold flex items-center gap-2">
                                    <Globe class="size-4 text-primary" />
                                    Environment Setup
                                </h3>
                                <p>To enable email functionality, update the following keys in your <code>.env</code> file:</p>
                                <pre class="p-3 bg-black text-emerald-400 rounded text-xs overflow-x-auto">
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="no-reply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"</pre>
                                <p class="text-muted-foreground italic">Important: Run <code>php artisan config:clear</code> after editing the .env file.</p>
                            </div>

                            <div class="space-y-4 border-t pt-6">
                                <h3 class="font-bold">Test Delivery</h3>
                                <div class="grid gap-4">
                                    <div class="grid gap-2">
                                        <Label>Recipient Email Address</Label>
                                        <div class="flex gap-2">
                                            <Input v-model="testEmailAddress" placeholder="test@example.com" type="email" />
                                            <Button @click="sendTestEmail" :disabled="testingEmail || !testEmailAddress" class="shrink-0">
                                                <Send v-if="!testingEmail" class="mr-2 size-4" />
                                                <Loader2 v-else class="mr-2 size-4 animate-spin" />
                                                Send Test
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

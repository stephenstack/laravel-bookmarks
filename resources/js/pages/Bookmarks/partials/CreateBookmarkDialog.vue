<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useBookmarksStore } from '@/composables/useBookmarksStore';
import { getTagColorClass } from '@/lib/colors';
import { router } from '@inertiajs/vue3';
import { Check, Plus, X, Wand2, Loader2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import axios from 'axios';

const { state: store, closeBookmarkModal } = useBookmarksStore();

const isOpen = computed({
    get: () => store.isBookmarkModalOpen,
    set: (value) => {
        if (!value) closeBookmarkModal();
    },
});

const isEditing = computed(() => !!store.editingBookmark);

const form = ref({
    title: '',
    url: '',
    collection_id: 'all', 
    description: '',
    favicon: '',
    image_url: '',
    tags: [] as number[],
});

const isInterrogating = ref(false);

const interrogate = async () => {
    if (!form.value.url) return;
    
    isInterrogating.value = true;
    try {
        const response = await axios.post('/bookmarks/interrogate-url', { url: form.value.url });
        const data = response.data;
        
        form.value.title = data.title || form.value.title;
        form.value.description = data.description || form.value.description;
        form.value.favicon = data.favicon || form.value.favicon;
        form.value.image_url = data.image_url || form.value.image_url;
        
        // Auto-save after interrogation if it's an existing bookmark
        // Or if the user just wants the quick add experience
        handleSubmit();
    } catch (error) {
        console.error('Interrogation failed:', error);
    } finally {
        isInterrogating.value = false;
    }
};

const isCreatingTag = ref(false);
const newTagName = ref('');
const selectedTagColor = ref('blue');

const tagColors = [
    // Row 1: Neutrals & Grayscale
    { name: 'Slate', value: 'slate', preview: 'bg-slate-500' },
    { name: 'Gray', value: 'gray', preview: 'bg-gray-500' },
    { name: 'Zinc', value: 'zinc', preview: 'bg-zinc-500' },
    { name: 'Neutral', value: 'neutral', preview: 'bg-neutral-500' },
    { name: 'Stone', value: 'stone', preview: 'bg-stone-500' },
    { name: 'Dark', value: 'dark', preview: 'bg-zinc-900' },
    
    // Row 2: Warm Hues
    { name: 'Red', value: 'red', preview: 'bg-red-500' },
    { name: 'Orange', value: 'orange', preview: 'bg-orange-500' },
    { name: 'Amber', value: 'amber', preview: 'bg-amber-500' },
    { name: 'Yellow', value: 'yellow', preview: 'bg-yellow-400' },
    { name: 'Lime', value: 'lime', preview: 'bg-lime-500' },
    { name: 'Green', value: 'green', preview: 'bg-green-500' },
    
    // Row 3: Cool Hues
    { name: 'Emerald', value: 'emerald', preview: 'bg-emerald-500' },
    { name: 'Teal', value: 'teal', preview: 'bg-teal-500' },
    { name: 'Cyan', value: 'cyan', preview: 'bg-cyan-500' },
    { name: 'Sky', value: 'sky', preview: 'bg-sky-500' },
    { name: 'Blue', value: 'blue', preview: 'bg-blue-500' },
    { name: 'Indigo', value: 'indigo', preview: 'bg-indigo-500' },
    
    // Row 4: Deep/Vibrant Hues
    { name: 'Violet', value: 'violet', preview: 'bg-violet-500' },
    { name: 'Purple', value: 'purple', preview: 'bg-purple-500' },
    { name: 'Fuchsia', value: 'fuchsia', preview: 'bg-fuchsia-500' },
    { name: 'Pink', value: 'pink', preview: 'bg-pink-500' },
    { name: 'Rose', value: 'rose', preview: 'bg-rose-500' },
    { name: 'Maroon', value: 'maroon', preview: 'bg-red-900' },
];

const collections = computed(() => store.collections);

watch(
    () => store.editingBookmark,
    (bookmark) => {
        if (bookmark) {
            form.value = {
                title: bookmark.title,
                url: bookmark.url,
                collection_id: bookmark.collection_id ? String(bookmark.collection_id) : 'all',
                description: bookmark.description || '',
                favicon: bookmark.favicon || '',
                image_url: bookmark.image_url || '',
                tags: bookmark.tags ? bookmark.tags.map(t => Number(t.id)) : [],
            };
        } else {
            form.value = {
                title: '',
                url: '',
                collection_id: 'all',
                description: '',
                favicon: '',
                image_url: '',
                tags: [],
            };
        }
    },
    { immediate: true },
);

const handleSubmit = () => {
    const data = {
        ...form.value,
        collection_id: form.value.collection_id === 'all' ? null : form.value.collection_id,
        tags: form.value.tags,
    };

    if (isEditing.value && store.editingBookmark) {
        if (store.editingBookmark.is_company) {
            closeBookmarkModal();
            return;
        }
        router.put(`/bookmarks/${store.editingBookmark.id}`, data, {
            onSuccess: () => closeBookmarkModal(),
        });
    } else {
        router.post('/bookmarks', data, {
            onSuccess: () => closeBookmarkModal(),
        });
    }
};

const handleCreateTag = () => {
    if (!newTagName.value.trim()) return;

    const tagName = newTagName.value.trim();
    router.post('/tags', {
        name: tagName,
        color: selectedTagColor.value,
    }, {
        onSuccess: () => {
            isCreatingTag.value = false;
            newTagName.value = '';
            
            // Auto-select the newly created tag after store updates
            const unwatch = watch(() => store.tags, (newTags) => {
                const createdTag = newTags.find(t => t.name === tagName);
                if (createdTag && !form.value.tags.includes(Number(createdTag.id))) {
                    form.value.tags.push(Number(createdTag.id));
                    unwatch(); // Stop watching once found
                }
            }, { deep: true });

            // Safety timeout to clean up watcher if something goes wrong
            setTimeout(unwatch, 2000);
        },
        preserveScroll: true,
    });
};

const toggleCreateTag = () => {
    isCreatingTag.value = !isCreatingTag.value;
    if (!isCreatingTag.value) {
        newTagName.value = '';
    }
};
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>{{ isEditing ? 'Edit Bookmark' : 'Add Bookmark' }}</DialogTitle>
                <DialogDescription>
                    {{ isEditing ? 'Make changes to your bookmark here.' : 'Add a new bookmark to your collection.' }}
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="handleSubmit" class="grid gap-4 py-4">
                <div class="grid gap-2">
                    <Label htmlFor="url">URL</Label>
                    <div class="flex gap-2">
                        <Input
                            id="url"
                            v-model="form.url"
                            placeholder="https://example.com"
                            required
                            :disabled="store.editingBookmark?.is_company"
                        />
                        <Button 
                            v-if="!store.editingBookmark?.is_company"
                            type="button" 
                            variant="secondary" 
                            size="icon" 
                            @click="interrogate"
                            :disabled="isInterrogating"
                        >
                            <Wand2 v-if="!isInterrogating" class="size-4" />
                            <Loader2 v-else class="size-4 animate-spin" />
                        </Button>
                    </div>
                </div>
                <div class="grid gap-2">
                    <Label htmlFor="title">Title</Label>
                    <Input
                        id="title"
                        v-model="form.title"
                        placeholder="My Awesome Bookmark"
                        required
                        :disabled="store.editingBookmark?.is_company"
                    />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label>Favicon URL</Label>
                        <Input v-model="form.favicon" placeholder="Icon URL" :disabled="store.editingBookmark?.is_company" />
                    </div>
                    <div class="grid gap-2">
                        <Label>Image URL</Label>
                        <Input v-model="form.image_url" placeholder="Background/OG Image" :disabled="store.editingBookmark?.is_company" />
                    </div>
                </div>
                <div v-if="form.image_url" class="relative aspect-video w-full rounded-lg overflow-hidden border">
                     <img :src="form.image_url" class="w-full h-full object-cover" />
                     <div class="absolute inset-x-0 bottom-0 p-3 bg-gradient-to-t from-black/80 to-transparent text-white">
                         <div class="font-bold text-sm truncate">{{ form.title }}</div>
                         <div class="text-[10px] opacity-80 truncate">{{ form.description }}</div>
                     </div>
                     <button 
                         v-if="!store.editingBookmark?.is_company"
                         type="button"
                         @click="form.image_url = ''; handleSubmit()" 
                         class="absolute top-2 right-2 size-7 flex items-center justify-center rounded-full bg-black/50 hover:bg-destructive text-white transition-colors backdrop-blur-sm shadow-md"
                         title="Remove Image"
                     >
                         <Trash2 class="size-3.5" />
                     </button>
                </div>
                <div class="grid gap-2">
                    <Label htmlFor="collection">Collection</Label>
                    <select
                        id="collection"
                        v-model="form.collection_id"
                        class="flex h-9 w-full items-center justify-between rounded-md border border-input bg-background text-foreground px-3 py-2 text-sm shadow-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="store.editingBookmark?.is_company"
                    >
                        <option value="all" class="bg-background text-foreground">Unorganized</option>
                        <option
                            v-for="collection in collections"
                            :key="collection.id"
                            :value="String(collection.id)"
                            class="bg-background text-foreground"
                        >
                            {{ collection.name }}
                        </option>
                    </select>
                </div>
                <div class="grid gap-2">
                    <Label htmlFor="description">Description</Label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        placeholder="Optional description..."
                        class="flex min-h-[60px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="store.editingBookmark?.is_company"
                    ></textarea>
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label>Tags</Label>
                        <button 
                            v-if="!store.editingBookmark?.is_company"
                            type="button" 
                            @click="toggleCreateTag"
                            class="text-[10px] font-medium text-primary hover:underline flex items-center gap-0.5"
                        >
                            <Plus class="size-3" />
                            {{ isCreatingTag ? 'Cancel' : 'New Tag' }}
                        </button>
                    </div>

                    <div v-if="isCreatingTag" class="space-y-3 p-3 border rounded-lg bg-muted/30 animate-in fade-in slide-in-from-top-2 duration-200">
                        <div class="space-y-1.5">
                            <Label class="text-[10px] uppercase tracking-wider opacity-60">Tag Name</Label>
                            <div class="flex gap-2">
                                <Input 
                                    v-model="newTagName" 
                                    placeholder="Enter tag name..." 
                                    class="h-8 text-xs" 
                                    @keyup.enter="handleCreateTag"
                                />
                                <Button size="sm" class="h-8 px-2" @click="handleCreateTag" :disabled="!newTagName">
                                    <Check class="size-3" />
                                </Button>
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-[10px] uppercase tracking-wider opacity-60">Color</Label>
                            <div class="grid grid-cols-6 gap-2 pt-1">
                                <button
                                     v-for="color in tagColors"
                                     :key="color.value"
                                     type="button"
                                     @click="selectedTagColor = color.value"
                                     class="size-7 rounded-full border transition-all flex items-center justify-center shadow-sm mx-auto"
                                     :class="[color.preview, selectedTagColor === color.value ? 'ring-2 ring-primary ring-offset-2' : 'hover:scale-110']"
                                 >
                                     <Check v-if="selectedTagColor === color.value" class="size-3.5 text-white" />
                                 </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 pt-1">
                        <label 
                            v-for="tag in store.tags" 
                            :key="tag.id"
                            class="flex items-center gap-1.5 px-2 py-1 rounded-md border text-xs cursor-pointer transition-colors"
                            :class="[
                                form.tags.includes(Number(tag.id))
                                    ? 'bg-primary border-primary text-primary-foreground'
                                    : [getTagColorClass(tag.color), 'hover:opacity-80'],
                                store.editingBookmark?.is_company ? 'cursor-not-allowed opacity-80' : ''
                            ]"
                        >
                            <input 
                                type="checkbox" 
                                :value="Number(tag.id)" 
                                v-model="form.tags" 
                                class="hidden"
                                :disabled="store.editingBookmark?.is_company"
                            />
                            <span>{{ tag.name }}</span>
                        </label>
                        <div v-if="store.tags.length === 0 && !isCreatingTag" class="text-xs text-muted-foreground italic px-1">
                            No tags created yet.
                        </div>
                    </div>
                </div>
            </form>
            <DialogFooter>
                <Button type="button" variant="outline" @click="closeBookmarkModal">
                    Cancel
                </Button>
                <Button type="submit" @click="handleSubmit">
                    {{ isEditing ? 'Save Changes' : 'Create Bookmark' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

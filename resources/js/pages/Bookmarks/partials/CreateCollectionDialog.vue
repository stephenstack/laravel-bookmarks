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
import { router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import * as Icons from 'lucide-vue-next';
import { Upload, X } from 'lucide-vue-next';

const { state: store, closeCollectionModal } = useBookmarksStore();

const isOpen = computed({
    get: () => store.isCollectionModalOpen,
    set: (value) => {
        if (!value) closeCollectionModal();
    },
});

const isEditing = computed(() => !!store.editingCollection);

const form = ref({
    name: '',
    icon: 'Folder',
    color: 'slate',
    background_image: '',
    background_opacity: 100,
});

const uploading = ref(false);
const uploadError = ref('');
const previewUrl = ref('');
const fileInput = ref<HTMLInputElement | null>(null);

// A curated list of Lucide icons for collections
const iconNames = [
    'Folder', 'Bookmark', 'Palette', 'Code', 'Wrench', 'BookOpen', 'Sparkles', 'Star',
    'Heart', 'Cloud', 'Camera', 'Gift', 'Globe', 'ImageIcon', 'Layers', 'Mail',
    'Monitor', 'Music', 'Phone', 'Play', 'Shield', 'ShoppingCart', 'Smartphone', 'Speaker',
    'Sun', 'Terminal', 'Video', 'Zap', 'Activity', 'Anchor', 'Award', 'Briefcase',
    'Coffee', 'Compass', 'Cpu', 'CreditCard', 'Database', 'Droplet', 'Eye', 'Flag',
    'Github', 'GraduationCap', 'Home', 'Key', 'LifeBuoy', 'Lock', 'Map', 'Mic',
    'Package', 'PenTool', 'Rocket', 'Search', 'Server', 'Settings', 'Share2', 'Tag',
    'Target', 'Thermometer', 'ThumbsUp', 'Trophy', 'Tv', 'Umbrella', 'User', 'Watch'
];

const colors = [
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

watch(
    () => store.editingCollection,
// ... (rest of watch)
    (collection) => {
        if (collection) {
            form.value = {
                name: collection.name,
                icon: collection.icon || 'Folder',
                color: collection.color || 'slate',
                background_image: collection.background_image || '',
                background_opacity: collection.background_opacity ?? 100,
            };
            previewUrl.value = collection.background_image || '';
        } else {
            form.value = {
                name: '',
                icon: 'Folder',
                color: 'slate',
                background_image: '',
                background_opacity: 100,
            };
            previewUrl.value = '';
        }
        uploadError.value = '';
    },
    { immediate: true },
);

const handleFileSelect = async (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    
    if (!file) return;
    
    uploadError.value = '';
    
    const img = new Image();
    img.onload = async () => {
        if (img.width < 1920 || img.height < 1080) {
            uploadError.value = `Image must be at least 1920x1080 pixels. Current size: ${img.width}x${img.height}`;
            if (fileInput.value) fileInput.value.value = '';
            return;
        }
        
        uploading.value = true;
        const formData = new FormData();
        formData.append('image', file);
        
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            const response = await fetch('/bookmarks/upload-image', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken || '',
                    'Accept': 'application/json',
                },
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                uploadError.value = data.error || 'Upload failed';
                if (fileInput.value) fileInput.value.value = '';
                return;
            }
            
            form.value.background_image = data.url;
            previewUrl.value = data.url;
        } catch (error) {
            uploadError.value = 'Upload failed. Please try again.';
            if (fileInput.value) fileInput.value.value = '';
        } finally {
            uploading.value = false;
        }
    };
    
    img.onerror = () => {
        uploadError.value = 'Invalid image file';
        if (fileInput.value) fileInput.value.value = '';
    };
    
    img.src = URL.createObjectURL(file);
};

const removeImage = () => {
    form.value.background_image = '';
    previewUrl.value = '';
    if (fileInput.value) fileInput.value.value = '';
};

const handleSubmit = () => {
    if (isEditing.value && store.editingCollection) {
        router.put(`/collections/${store.editingCollection.id}`, form.value, {
            onSuccess: () => closeCollectionModal(),
        });
    } else {
        router.post('/collections', form.value, {
            onSuccess: () => closeCollectionModal(),
        });
    }
};

const getIcon = (name: string) => {
    // Handle cases where icon name doesn't match exactly (e.g. 'image' vs 'ImageIcon')
    if (name === 'ImageIcon') return Icons.Image;
    return (Icons as any)[name] || Icons.HelpCircle;
};
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="sm:max-w-[500px]">
            <DialogHeader>
                <DialogTitle>{{ isEditing ? 'Edit Collection' : 'New Collection' }}</DialogTitle>
                <DialogDescription>
                    {{ isEditing ? 'Change the details of your collection.' : 'Create a new collection to organize your bookmarks.' }}
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="handleSubmit" class="grid gap-6 py-4">
                <div class="grid gap-2">
                    <Label htmlFor="name">Collection Name</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        placeholder="Work Resources, Inspiration, etc."
                        required
                        autofocus
                    />
                </div>

                <div class="grid gap-2">
                    <Label>Icon</Label>
                    <div class="grid max-h-[200px] grid-cols-8 gap-2 overflow-y-auto rounded-md border p-3">
                        <button
                            v-for="name in iconNames"
                            :key="name"
                            type="button"
                            class="flex size-10 items-center justify-center rounded-md border transition-all hover:bg-muted"
                            :class="{ 'border-primary bg-primary/10 ring-2 ring-primary': form.icon === name }"
                            @click="form.icon = name"
                            :title="name"
                        >
                             <component :is="getIcon(name)" class="size-5" />
                        </button>
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label>Color Theme</Label>
                    <div class="grid grid-cols-11 gap-2 rounded-md border p-3">
                        <button
                            v-for="color in colors"
                            :key="color.name"
                            type="button"
                            class="size-6 rounded-full border border-white/20 transition-transform hover:scale-125 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                            :class="[color.class, { 'ring-2 ring-primary ring-offset-2 scale-110': form.color === color.name }]"
                            @click="form.color = color.name"
                            :title="color.name"
                        />
                    </div>
                </div>

                <div class="grid gap-4 border-t pt-4">
                    <div class="grid gap-2">
                        <Label for="background_image">Background Image</Label>
                        
                        <!-- Image Preview -->
                        <div v-if="previewUrl" class="relative rounded-lg overflow-hidden border bg-muted aspect-video">
                            <img :src="previewUrl" alt="Background preview" class="w-full h-full object-cover" />
                            <button
                                type="button"
                                @click="removeImage"
                                class="absolute top-2 right-2 p-1.5 rounded-full bg-destructive text-destructive-foreground hover:bg-destructive/90 transition-colors"
                            >
                                <X class="size-4" />
                            </button>
                        </div>
                        
                        <!-- Upload Button -->
                        <div v-else class="relative">
                            <input
                                ref="fileInput"
                                type="file"
                                id="background_image"
                                accept="image/jpeg,image/png,image/jpg,image/webp"
                                @change="handleFileSelect"
                                class="hidden"
                            />
                            <label
                                for="background_image"
                                class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-lg cursor-pointer bg-muted/30 hover:bg-muted/50 transition-colors"
                                :class="{ 'opacity-50 cursor-not-allowed': uploading }"
                            >
                                <Upload class="size-8 mb-2 text-muted-foreground" />
                                <p class="text-sm text-muted-foreground">
                                    {{ uploading ? 'Uploading...' : 'Click to upload image' }}
                                </p>
                                <p class="text-xs text-muted-foreground mt-1">
                                    Min: 1920x1080px • Max: 5MB
                                </p>
                            </label>
                        </div>
                        
                        <!-- Error Message -->
                        <p v-if="uploadError" class="text-sm text-destructive">
                            {{ uploadError }}
                        </p>
                        
                        <!-- Recommendation -->
                        <p class="text-xs text-muted-foreground">
                            We recommend <a href="https://www.freepik.com" target="_blank" class="underline hover:text-foreground transition-colors">Freepik</a> for high-quality stock images
                        </p>
                    </div>
                    <div class="grid gap-2">
                        <div class="flex items-center justify-between">
                            <Label for="background_opacity">Background Opacity ({{ form.background_opacity }}%)</Label>
                        </div>
                        <input
                            id="background_opacity"
                            type="range"
                            v-model.number="form.background_opacity"
                            min="0"
                            max="100"
                            step="1"
                            class="w-full h-2 bg-muted rounded-lg appearance-none cursor-pointer accent-primary"
                        />
                    </div>
                </div>
            </form>
            <DialogFooter>
                <div class="flex w-full items-center justify-between">
                    <Button 
                        v-if="form.background_image" 
                        type="button" 
                        variant="outline" 
                        @click="removeImage"
                        class="text-destructive hover:text-destructive"
                    >
                        Remove Image
                    </Button>
                    <div v-else></div>
                    <div class="flex gap-2">
                        <Button type="button" variant="outline" @click="closeCollectionModal">
                            Cancel
                        </Button>
                        <Button type="submit" @click="handleSubmit">
                            {{ isEditing ? 'Save Changes' : 'Create Collection' }}
                        </Button>
                    </div>
                </div>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

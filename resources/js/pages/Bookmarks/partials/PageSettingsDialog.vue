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
import { Upload, X } from 'lucide-vue-next';

const { state: store, closePageSettings, updatePageSettings } = useBookmarksStore();

const isOpen = computed({
    get: () => store.isPageSettingsModalOpen,
    set: (value) => {
        if (!value) closePageSettings();
    },
});

const form = ref({
    background_image: '',
    background_opacity: 100,
});

const uploading = ref(false);
const uploadError = ref('');
const previewUrl = ref('');
const fileInput = ref<HTMLInputElement | null>(null);

const pageTitle = computed(() => {
    if (store.filterType === 'favorites') return 'Favorites';
    if (store.pageMode === 'archive') return 'Archive';
    if (store.pageMode === 'trash') return 'Trash';
    return 'All Bookmarks';
});

watch(
    () => [store.isPageSettingsModalOpen, store.selectedCollection, store.pageMode, store.filterType, store.userPreferences],
    () => {
        if (store.isPageSettingsModalOpen) {
            let pageKey = 'all';
            if (store.filterType === 'favorites') pageKey = 'favorites';
            else if (store.pageMode === 'archive') pageKey = 'archive';
            else if (store.pageMode === 'trash') pageKey = 'trash';

            const settings = store.userPreferences.backgrounds?.[pageKey] || {};
            form.value = {
                background_image: settings.background_image || '',
                background_opacity: settings.background_opacity ?? 100,
            };
            previewUrl.value = settings.background_image || '';
            uploadError.value = '';
        }
    },
    { immediate: true }
);

const handleFileSelect = async (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    
    if (!file) return;
    
    uploadError.value = '';
    
    // Client-side dimension check
    const img = new Image();
    img.onload = async () => {
        if (img.width < 1920 || img.height < 1080) {
            uploadError.value = `Image must be at least 1920x1080 pixels. Current size: ${img.width}x${img.height}`;
            if (fileInput.value) fileInput.value.value = '';
            return;
        }
        
        // Upload to server
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
    updatePageSettings(form.value);
    closePageSettings();
};
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Page Settings</DialogTitle>
                <DialogDescription>
                    Customize the appearance of your "{{ pageTitle }}" view.
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="handleSubmit" class="grid gap-6 py-4">
                <div class="grid gap-4">
                    <div class="grid gap-2">
                        <Label for="bg-image">Background Image</Label>
                        
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
                                id="bg-image"
                                accept="image/jpeg,image/png,image/jpg,image/webp"
                                @change="handleFileSelect"
                                class="hidden"
                            />
                            <label
                                for="bg-image"
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
                            <Label for="bg-opacity">Background Opacity ({{ form.background_opacity }}%)</Label>
                        </div>
                        <input
                            id="bg-opacity"
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
                        <Button type="button" variant="outline" @click="closePageSettings">
                            Cancel
                        </Button>
                        <Button type="submit" @click="handleSubmit">
                            Save Changes
                        </Button>
                    </div>
                </div>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

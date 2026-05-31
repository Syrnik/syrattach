<template>
    <div class="s-syrattach-upload-section">
        <div class="box uploadbox"
             :class="{ 'highlighted': isOver }"
             @dragenter.prevent="isOver = true"
             @dragleave.prevent="onDragLeave"
             @dragover.prevent="isOver = true"
             @drop.prevent="onDrop">
            <div class="upload">
                <label class="link">
                    <i class="fas fa-file-upload custom-mr-8"></i>
                    <span>{{ t('Upload or drag & drop files here') }}</span>
                    <input type="file" multiple autocomplete="off"
                           @change.prevent="onInputChange">
                </label>
            </div>
            <p class="small custom-mt-8">{{ t('Max. file size for upload:') }} {{ filesize(maxUploadSize) }}</p>
        </div>
        <div class="s-syrattach-upload-files" v-show="uploads.length">
            <h4 style="margin-top: 1rem">{{ t('Loading files') }}</h4>
            <UploadingFile
                v-for="item in uploads"
                :key="item.id"
                :file="item"
                :product-id="productId"
                :max-size="maxUploadSize"
                @upload-complete="uploadComplete($event)"
                @cancel="removeById($event)"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, inject } from 'vue';
import UploadingFile from './UploadingFile.vue';
import { useL10n } from '../composables/useL10n';
import type { UploadItem, AttachmentFile } from '../types';

const props = defineProps<{ productId: number }>();
const emit = defineEmits<{ 'add-file': [file: AttachmentFile] }>();

const { t, filesize } = useL10n();
const maxUploadSize = inject<number>('max_upload_size', 0);
const isOver = ref(false);
const uploads = ref<UploadItem[]>([]);

function addFiles(files: FileList) {
    for (let i = 0; i < files.length; i++) {
        uploads.value.push({
            id: Math.random(),
            name: files[i].name,
            size: files[i].size,
            nativeFile: files[i],
        });
    }
}

function onDragLeave(event: DragEvent) {
    if (!(event.currentTarget as Element).contains(event.relatedTarget as Node)) {
        isOver.value = false;
    }
}

function onDrop(event: DragEvent) {
    isOver.value = false;
    if (event.dataTransfer?.files) addFiles(event.dataTransfer.files);
}

function onInputChange(event: Event) {
    const input = event.target as HTMLInputElement;
    if (input.files) {
        addFiles(input.files);
        input.value = '';
    }
}

function removeById(id: number) {
    const idx = uploads.value.findIndex(f => f.id === id);
    if (idx > -1) uploads.value.splice(idx, 1);
}

function uploadComplete(event: { id: number; file: AttachmentFile }) {
    removeById(event.id);
    emit('add-file', event.file);
}
</script>

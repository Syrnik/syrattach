<template>
    <div class="s-syrattach-uploading-file" :class="{ 's-syrattach-uploading-file__error': !!errors }">
        <div class="s-syrattach-uploading-file__main">
            <div class="s-syrattach-uploading-file__name">{{ file.name }}</div>
            <div class="s-syrattach-uploading-file__progress" v-if="uploading">
                <i class="fas fa-spinner fa-spin"></i>
                <progress v-if="totalSize === null"></progress>
                <template v-else>
                    <progress :max="totalSize" :value="uploaded ?? 0"></progress>
                    <span class="s-syrattach-filesize-hint">
                        {{ filesize(totalSize) }} {{ t('of') }} {{ filesize(uploaded ?? 0) }}
                    </span>
                </template>
            </div>
            <div class="s-syrattach-uploading-file__error-text" v-if="errors && errors.length">
                {{ Array.isArray(errors) ? errors[0] : errors }}
            </div>
        </div>
        <div class="s-syrattach-uploading-file__actions">
            <button class="s-syrattach-dismiss wa-button wa-small transparent-red"
                    v-if="!!errors"
                    type="button"
                    @click.prevent="$emit('cancel', file.id)">
                <i class="fas fa-times"></i>
                {{ t('Cancel') }}
            </button>
            <template v-else>&nbsp;</template>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useL10n } from '../composables/useL10n';
import type { UploadItem, AttachmentFile } from '../types';

const props = defineProps<{
    productId: number;
    file: UploadItem;
    maxSize: number;
}>();

const emit = defineEmits<{
    'upload-complete': [{ id: number; file: AttachmentFile }];
    cancel: [id: number];
}>();

const { t, filesize } = useL10n();
const uploading = ref(false);
const uploaded = ref<number | null>(null);
const totalSize = ref<number | null>(null);
const errors = ref<string[] | null>(null);

onMounted(() => {
    if (props.maxSize && props.file.size >= props.maxSize) {
        errors.value = [t('Size of %name% exceeds maximum upload size limit').replace('%name%', props.file.name)];
        return;
    }

    uploading.value = true;
    const formData = new FormData();
    formData.append('syrattach_product_id', String(props.productId));
    formData.append('files', props.file.nativeFile);

    $.ajax({
        xhr() {
            const xhr = new XMLHttpRequest();
            xhr.upload.addEventListener('progress', (event) => {
                if (event.lengthComputable) {
                    totalSize.value = event.total;
                    uploaded.value = event.loaded;
                }
            });
            return xhr;
        },
        url: '?plugin=syrattach&module=attachments&action=upload',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        type: 'POST',
    })
        .done((r: { files?: Array<{ id?: number; error?: string }>; status?: string; error?: string }) => {
            if (r?.files?.[0]?.id) {
                emit('upload-complete', { id: props.file.id, file: r.files[0] as unknown as AttachmentFile });
            } else if (r?.files?.[0]?.error !== undefined) {
                const err = r.files[0].error ?? '';
                errors.value = [t('Upload error') + (err.length ? ': ' + err : '')];
            } else if (r?.status === 'fail') {
                errors.value = [t('Upload error') + (r.error?.length ? ': ' + r.error : '')];
            }
        })
        .fail((r: { status?: number; statusText?: string }) => {
            errors.value = ['Server error: ' + (r.status ?? '') + ' ' + (r.statusText ?? '')];
        })
        .always(() => { uploading.value = false; });
});
</script>

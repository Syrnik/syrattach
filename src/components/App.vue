<template>
    <div class="article s-product-attachments-section">
        <div v-if="productId === 0" class="s-section-header">
            <h3 style="margin-top: 1.5rem">{{ t('Attached files') }}</h3>
            <p>{{ t("You can't upload files to a new product. First, save the product.") }}</p>
        </div>
        <div v-else class="s-section-body">
            <UploadSection :product-id="productId" @add-file="onFileAdded" />
            <div class="s-attachments-wrapper">
                <h3 style="margin-top: 1.5rem">{{ t('Attached files') }}</h3>
                <div class="s-attachments-list" v-if="files.length" ref="listEl">
                    <div class="s-attachments-row" v-for="file in files" :key="file.id" :data-id="file.id">
                        <div class="s-drag-handle" :title="t('Drag to reorder')">
                            <i class="fas fa-grip-vertical"></i>
                        </div>
                        <div class="s-column s-column-file wide">
                            <div class="s-column-file-name">
                                <a :href="file.url" target="_blank"><b>{{ file.name }}</b></a>
                                <span class="s-syrattach-filesize-hint">({{ filesize(file.size) }})</span>
                            </div>
                            <DescriptionField :file-id="file.id" v-model="file.description" />
                        </div>
                        <div class="s-column s-column-actions nowrap">
                            <button class="button small outlined red" type="button" @click.prevent="confirmDelete(file.id)">
                                <i class="fas fa-trash-alt custom-mr-8"></i>{{ t('Delete') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import Sortable from 'sortablejs';
import UploadSection from './UploadSection.vue';
import DescriptionField from './DescriptionField.vue';
import { useL10n } from '../composables/useL10n';
import type { AttachmentFile } from '../types';

const props = defineProps<{
    initialFiles: AttachmentFile[];
    productId: number;
}>();

const { t, filesize } = useL10n();
const files = ref<AttachmentFile[]>(props.initialFiles);
const listEl = ref<HTMLElement | null>(null);

// watch instead of onMounted — handles v-if: ref is null when list is hidden
let sortableInstance: Sortable | null = null;
watch(listEl, (el) => {
    if (el && !sortableInstance) {
        sortableInstance = Sortable.create(el, {
            handle: '.s-drag-handle',
            animation: 150,
            onEnd({ oldIndex, newIndex }) {
                if (oldIndex == null || newIndex == null || oldIndex === newIndex) return;
                const items = [...files.value];
                const [moved] = items.splice(oldIndex, 1);
                items.splice(newIndex, 0, moved);
                files.value = items;
                saveSortOrder();
            },
        });
    } else if (!el && sortableInstance) {
        sortableInstance.destroy();
        sortableInstance = null;
    }
});

function saveSortOrder() {
    $.post('?plugin=syrattach&module=attachments&action=sort', {
        entity_type: 'product',
        entity_id: props.productId,
        order: files.value.map(f => f.id),
    });
}

function onFileAdded(file: AttachmentFile) {
    files.value.push(file);
}

function confirmDelete(id: number) {
    ($ as JQueryStatic & { wa: { confirm: (opts: Record<string, unknown>) => void } }).wa.confirm({
        title: t('File deletion confirmation'),
        text: t('Do you really want to delete this file?'),
        success_button_name: t('Delete'),
        cancel_button_name: t('Cancel'),
        onSuccess() {
            $.post('?plugin=syrattach&module=attachments&action=delete', { id })
                .done((r: { status?: string }) => {
                    if (r?.status === 'ok') {
                        const idx = files.value.findIndex(f => f.id === id);
                        if (idx > -1) files.value.splice(idx, 1);
                    }
                });
        },
    });
}

watch(files, (newFiles) => {
    const $menuItem = $('#s-syrattach-plugin-menuitem');
    const $counter = $menuItem.find('.count');
    if (newFiles.length) {
        if ($counter.length) $counter.text(newFiles.length);
        else $(`<span class="count">${newFiles.length}</span>`).appendTo($menuItem.find('a'));
    } else if ($counter.length) {
        $counter.remove();
    }
}, { deep: true });
</script>

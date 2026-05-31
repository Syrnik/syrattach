<template>
    <div class="s-column-file-description">
        <div v-if="!editMode" class="s-column-file-description__content" @click.prevent="editMode = true">
            <template v-if="localDescription && localDescription.length">{{ localDescription }}</template>
            <em v-else>{{ t('Add file description') }}</em>
        </div>
        <DescriptionEditor
            v-else
            v-model="localDescription"
            :file-id="fileId"
            @cancel-edit="editMode = false"
            @update:model-value="onSaved"
        />
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import DescriptionEditor from './DescriptionEditor.vue';
import { useL10n } from '../composables/useL10n';

const props = defineProps<{ modelValue: string; fileId: number }>();
const emit = defineEmits<{ 'update:modelValue': [val: string] }>();

const { t } = useL10n();
const editMode = ref(false);
const localDescription = ref(props.modelValue);

function onSaved(val: string) {
    localDescription.value = val;
    emit('update:modelValue', val);
    editMode.value = false;
}
</script>

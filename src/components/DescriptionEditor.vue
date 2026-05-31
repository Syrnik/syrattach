<template>
    <div class="s-column-file-description__editor">
        <div class="s-syrattach-editable-field">
            <textarea style="width: 100%" v-model="localDescription"></textarea>
        </div>
        <button class="wa-button wa-small" type="button" @click.prevent="saveDescription()" :disabled="saving">
            <i v-if="saving" class="fas fa-spinner fa-spin"></i>
            {{ t('Save') }}
        </button>
        <button class="wa-button wa-small transparent-gray" type="button" @click.prevent="$emit('cancel-edit')" :disabled="saving">
            {{ t('Cancel') }}
        </button>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useL10n } from '../composables/useL10n';

const props = defineProps<{ modelValue: string; fileId: number }>();
const emit = defineEmits<{
    'update:modelValue': [val: string];
    'cancel-edit': [];
}>();

const { t } = useL10n();
const localDescription = ref(props.modelValue);
const saving = ref(false);

function saveDescription() {
    saving.value = true;
    $.post(
        '?plugin=syrattach&module=attachments&action=descriptionsave',
        { id: props.fileId, data: { description: localDescription.value } }
    )
        .done(() => emit('update:modelValue', localDescription.value))
        .always(() => { saving.value = false; });
}
</script>

<template>
  <button
    type="button"
    @click.stop="handleClick"
    class="appearance-none inline-flex items-center text-left rounded-lg"
    :class="{
      'hover:opacity-50': withPreview,
      '!cursor-default': !withPreview,
    }"
  >
    <Badge
      class="bg-primary-50 dark:bg-primary-500 text-primary-600 dark:text-gray-900 space-x-1"
      :class="{ '!pl-2 !pr-1': editable }"
    >
      <span>{{ tag.display }}</span>
      <button
        v-if="editable"
        @click.stop="$emit('tag-removed', index)"
        type="button"
        class="opacity-50 hover:opacity-75 dark:opacity-100 dark:hover:opacity-50"
      >
        <Icon type="x" width="16" height="16" />
      </button>
    </Badge>

    <PreviewResourceModal
      v-if="withPreview"
      @close="handleClick"
      :show="shown"
      :resource-id="tag.value"
      :resource-name="resourceName"
    />
  </button>
</template>

<script setup>
import { ref } from 'vue'

const shown = ref(false)

const props = defineProps({
  resourceName: { type: String },
  index: { type: Number, required: true },
  tag: { type: Object, required: true },
  editable: { type: Boolean, default: true },
  withSubtitles: { type: Boolean, default: true },
  withPreview: { type: Boolean, default: false },
})

defineEmits(['tag-removed', 'click'])

function handleClick() {
  if (props.withPreview) {
    shown.value = !shown.value
  }
}
</script>

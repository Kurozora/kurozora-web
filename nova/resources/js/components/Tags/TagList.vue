<template>
  <div>
    <TagListItem
      v-for="(tag, index) in tags"
      @tag-removed="$emit('tag-removed')"
      :index="index"
      :tag="tag"
      :resource-name="resourceName"
      :editable="editable"
      :with-subtitles="withSubtitles"
      :with-preview="withPreview"
    />
  </div>
</template>

<script setup>
const props = defineProps({
  resourceName: { type: String },
  tags: { type: Array, default: [] },
  editable: { type: Boolean, default: true },
  withSubtitles: { type: Boolean, default: true },
  withPreview: { type: Boolean, default: false },
})

const emit = defineEmits(['tag-removed', 'click'])
</script>

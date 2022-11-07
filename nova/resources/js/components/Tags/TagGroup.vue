<template>
  <div class="flex flex-wrap gap-2">
    <TagGroupItem
      @tag-removed="$emit('tag-removed')"
      v-for="(tag, index) in limitedTags"
      :tag="tag"
      :index="index"
      :resource-name="resourceName"
      :editable="editable"
      :with-preview="withPreview"
    />

    <Badge
      v-if="shouldShowShowMoreButton"
      v-tooltip="__('Show more')"
      @click.stop="handleEtcClick"
      class="cursor-pointer bg-primary-50 dark:bg-primary-500 text-primary-600 dark:text-gray-900 space-x-1"
    >
      <Icon type="dots-horizontal" width="16" height="16" />
    </Badge>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  resourceName: { type: String },
  tags: { type: Array, default: [] },
  limit: { type: [Number, Boolean], default: false },
  editable: { type: Boolean, default: true },
  withPreview: { type: Boolean, default: false },
})

const showMoreButtonClicked = ref(false)
const shouldShowShowMoreButton = computed(
  () =>
    props.limit !== false &&
    props.tags.length > props.limit &&
    !showMoreButtonClicked.value
)

const limitedTags = computed(() => {
  if (props.limit !== false && !showMoreButtonClicked.value) {
    return props.tags.slice(0, props.limit)
  }

  return props.tags
})

function handleEtcClick() {
  showMoreButtonClicked.value = true
}
</script>

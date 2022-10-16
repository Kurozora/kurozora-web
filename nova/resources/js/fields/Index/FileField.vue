<template>
  <div :class="alignmentClass" class="flex">
    <ImageLoader
      v-if="shouldShowLoader"
      :src="imageUrl"
      :rounded="field.rounded"
      :max-width="30"
      :align="field.textAlign"
    >
      <template #missing>
        <Icon
          type="exclamation-circle"
          class="text-red-500"
          v-tooltip="__('The image could not be loaded.')"
        />
      </template>
    </ImageLoader>
    <span
      v-if="usesCustomizedDisplay && !imageUrl"
      class="break-words"
      v-tooltip="field.value"
    >
      {{ field.displayedAs }}
    </span>
    <p
      v-if="!usesCustomizedDisplay && !imageUrl"
      :class="`text-${field.textAlign}`"
      v-tooltip="field.value"
    >
      &mdash;
    </p>
  </div>
</template>

<script>
import { FieldValue } from '@/mixins'
import { computed } from 'vue'

export default {
  mixins: [FieldValue],
  props: ['viaResource', 'viaResourceId', 'resourceName', 'field'],

  data: () => ({
    loading: false,
  }),

  computed: {
    shouldShowLoader() {
      return this.imageUrl
    },

    imageUrl() {
      if (this.field.previewUrl && !this.field.thumbnailUrl) {
        return this.field.previewUrl
      }

      return this.field.thumbnailUrl
    },

    alignmentClass() {
      return {
        left: 'items-center justify-start',
        center: 'items-center justify-center',
        right: 'items-center justify-end',
      }[this.field.textAlign]
    },
  },
}
</script>

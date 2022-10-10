<template>
  <div :class="alignmentClass" class="flex">
    <ImageLoader
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
    <p v-if="!loading && !imageUrl" :class="`text-${field.textAlign}`">
      &mdash;
    </p>
  </div>
</template>

<script>
import {FieldValue} from '@/mixins'

export default {
  mixins: [FieldValue],
  props: ['viaResource', 'viaResourceId', 'resourceName', 'field'],

  data: () => ({
    loading: false,
  }),

  computed: {
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

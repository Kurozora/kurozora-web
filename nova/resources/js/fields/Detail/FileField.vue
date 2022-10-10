<template>
  <PanelItem :index="index" :field="field">
    <template #value>
      <ImageLoader
        class="min-h-8"
        v-if="shouldShowLoader"
        :src="imageUrl"
        :maxWidth="field.maxWidth"
        :rounded="field.rounded"
        align="left"
      >
        <template #missing>
          <a :href="imageUrl" class="link-default-error ml-1">
            <Icon
              type="exclamation-circle"
              class="text-red-500"
              v-tooltip="__('The image could not be loaded.')"
            />
            {{ __('The image could not be loaded.') }}
          </a>
        </template>
      </ImageLoader>

      <span v-if="fieldValue && !imageUrl" class="break-words">
        {{ fieldValue }}
      </span>

      <span v-if="!fieldValue && !imageUrl">&mdash;</span>

      <p v-if="shouldShowToolbar" class="flex items-center text-sm mt-3">
        <a
          v-if="field.downloadable"
          :dusk="field.attribute + '-download-link'"
          @keydown.enter.prevent="download"
          @click.prevent="download"
          tabindex="0"
          class="cursor-pointer text-gray-500 inline-flex items-center"
        >
          <Icon
            class="mr-2"
            type="download"
            view-box="0 0 24 24"
            width="16"
            height="16"
          />
          <span class="class mt-1">{{ __('Download') }}</span>
        </a>
      </p>
    </template>
  </PanelItem>
</template>

<script>
import {FieldValue} from '@/mixins'

export default {
  mixins: [FieldValue],

  props: ['index', 'resource', 'resourceName', 'resourceId', 'field'],

  methods: {
    /**
     * Download the linked file
     */
    download() {
      const { resourceName, resourceId } = this
      const attribute = this.field.attribute

      let link = document.createElement('a')
      link.href = `/nova-api/${resourceName}/${resourceId}/download/${attribute}`
      link.download = 'download'
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
    },
  },

  computed: {
    hasValue() {
      return Boolean(this.field.value || this.imageUrl)
    },

    shouldShowLoader() {
      return this.imageUrl
    },

    shouldShowToolbar() {
      return Boolean(this.field.downloadable && this.hasValue)
    },

    imageUrl() {
      return this.field.previewUrl || this.field.thumbnailUrl
    },

    isVaporField() {
      return this.field.component === 'vapor-file-field'
    },
  },
}
</script>

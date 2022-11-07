<template>
  <PanelItem :index="index" :field="field">
    <template #value>
      <p v-if="fieldHasValue" class="flex items-center">
        <a :href="`mailto:${field.value}`" class="link-default">
          {{ fieldValue }}
        </a>

        <CopyButton
          v-if="fieldHasValue && field.copyable && !shouldDisplayAsHtml"
          @click.prevent.stop="copy"
          v-tooltip="__('Copy to clipboard')"
          class="mx-0"
        />
      </p>
      <p v-else>&mdash;</p>
    </template>
  </PanelItem>
</template>

<script>
import { CopiesToClipboard, FieldValue } from '@/mixins'

export default {
  mixins: [CopiesToClipboard, FieldValue],

  props: ['index', 'resource', 'resourceName', 'resourceId', 'field'],

  methods: {
    copy() {
      this.copyValueToClipboard(this.field.value)
    },
  },
}
</script>

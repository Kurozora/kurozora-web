<template>
  <div :class="`text-${field.textAlign}`">
    <p v-if="fieldHasValue" class="flex items-center">
      <a
        v-if="fieldHasValue"
        @click.stop
        :href="`mailto:${field.value}`"
        class="link-default whitespace-nowrap"
      >
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
  </div>
</template>

<script>
import { FieldValue } from '@/mixins'

export default {
  mixins: [FieldValue],

  props: ['resourceName', 'field'],
}
</script>

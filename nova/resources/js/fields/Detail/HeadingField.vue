<template>
  <div
    class="-mt-2 -mx-6"
    :class="{
      'border-t border-gray-100 dark:border-gray-700': index !== 0,
    }"
  >
    <div class="w-full py-4 px-6">
      <slot name="value">
        <Heading level="3" v-if="fieldValue && !shouldDisplayAsHtml">
          {{ fieldValue }}
        </Heading>
        <div
          v-else-if="fieldValue && shouldDisplayAsHtml"
          v-html="field.value"
        ></div>
        <p v-else>&mdash;</p>
      </slot>
    </div>
  </div>
</template>

<script>
import filled from '@/util/filled'

export default {
  props: ['index', 'resource', 'resourceName', 'resourceId', 'field'],

  computed: {
    fieldValue() {
      if (!filled(this.field.value)) {
        return false
      }

      return String(this.field.value)
    },

    shouldDisplayAsHtml() {
      return this.field.asHtml
    },
  },
}
</script>

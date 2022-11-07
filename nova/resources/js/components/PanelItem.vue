<template>
  <div
    class="flex flex-col md:flex-row -mx-6 px-6 py-2 md:py-0 space-y-2 md:space-y-0"
    :dusk="field.attribute"
  >
    <div class="md:w-1/4 md:py-3">
      <slot>
        <h4 class="font-normal">
          <span>{{ label }}</span>
        </h4>
      </slot>
    </div>
    <div class="md:w-3/4 md:py-3 break-all lg:break-words">
      <slot name="value">
        <CopyButton
          v-if="fieldValue && field.copyable && !shouldDisplayAsHtml"
          @click.prevent.stop="copy"
          v-tooltip="__('Copy to clipboard')"
        >
          <span ref="theFieldValue">
            {{ fieldValue }}
          </span>
        </CopyButton>

        <p
          v-else-if="fieldValue && !field.copyable && !shouldDisplayAsHtml"
          class="flex items-center"
        >
          {{ fieldValue }}
        </p>
        <div
          v-else-if="fieldValue && !field.copyable && shouldDisplayAsHtml"
          v-html="fieldValue"
        />
        <p v-else>&mdash;</p>
      </slot>
    </div>
  </div>
</template>

<script>
import { CopiesToClipboard, FieldValue } from '@/mixins'

export default {
  mixins: [CopiesToClipboard, FieldValue],

  props: {
    index: {
      type: Number,
      required: true,
    },

    field: {
      type: Object,
      required: true,
    },

    fieldName: {
      type: String,
      default: '',
    },
  },

  methods: {
    copy() {
      this.copyValueToClipboard(this.field.value)
    },
  },

  computed: {
    label() {
      return this.fieldName || this.field.name
    },
  },
}
</script>

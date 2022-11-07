<template>
  <FieldWrapper v-if="currentField.visible">
    <!--    :class="{ 'rounded-t-lg': index === 0 }"-->
    <div
      v-if="shouldDisplayAsHtml"
      v-html="currentField.value"
      :class="classes"
    />
    <div v-else :class="classes">
      <Heading level="3">{{ currentField.value }}</Heading>
    </div>
  </FieldWrapper>
</template>

<script>
import { DependentFormField } from '@/mixins'

export default {
  mixins: [DependentFormField],

  props: {
    index: { type: Number },
    resourceName: { type: String, require: true },
    field: { type: Object, require: true },
  },

  methods: {
    /**
     * Provide a function to fills FormData when field is visible.
     */
    fillIfVisible(formData, attribute, value) {
      //
    },
  },

  computed: {
    classes: () => [
      'remove-last-margin-bottom',
      'leading-normal',
      'w-full',
      'py-4',
      'px-8',
    ],

    shouldDisplayAsHtml() {
      return this.currentField.asHtml || false
    },
  },
}
</script>

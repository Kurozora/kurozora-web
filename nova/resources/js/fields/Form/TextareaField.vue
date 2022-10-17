<template>
  <DefaultField
    :field="currentField"
    :errors="errors"
    :full-width-content="fullWidthContent"
    :show-help-text="showHelpText"
  >
    <template #field>
      <div class="space-y-1">
        <textarea
          v-bind="extraAttributes"
          class="block w-full form-control form-input form-input-bordered py-3 h-auto"
          :id="currentField.uniqueKey"
          :dusk="field.attribute"
          :value="value"
          @input="handleChange"
          :maxlength="field.enforceMaxlength ? field.maxlength : -1"
          :placeholder="placeholder"
        />

        <CharacterCounter
          v-if="field.maxlength"
          :count="value.length"
          :limit="field.maxlength"
        />
      </div>
    </template>
  </DefaultField>
</template>

<script>
import { DependentFormField, HandlesValidationErrors } from '@/mixins'

export default {
  mixins: [HandlesValidationErrors, DependentFormField],

  computed: {
    defaultAttributes() {
      return {
        rows: this.currentField.rows,
        class: this.errorClasses,
        placeholder: this.field.name,
      }
    },

    extraAttributes() {
      const attrs = this.currentField.extraAttributes

      return {
        ...this.defaultAttributes,
        ...attrs,
      }
    },
  },
}
</script>

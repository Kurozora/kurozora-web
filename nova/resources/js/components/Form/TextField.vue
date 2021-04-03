<template>
  <default-field :field="field" :errors="errors" :show-help-text="showHelpText">
    <template slot="field">
      <input
        class="w-full form-control form-input form-input-bordered"
        @input="handleChange"
        :value="value"
        :id="field.attribute"
        :dusk="field.attribute"
        v-bind="extraAttributes"
        :disabled="isReadonly"
        :list="`${field.attribute}-list`"
      />

      <datalist
        v-if="field.suggestions && field.suggestions.length > 0"
        :id="`${field.attribute}-list`"
      >
        <option
          :key="suggestion"
          v-for="suggestion in field.suggestions"
          :value="suggestion"
        />
      </datalist>
    </template>
  </default-field>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'

export default {
  mixins: [HandlesValidationErrors, FormField],

  computed: {
    defaultAttributes() {
      return {
        type: this.field.type || 'text',
        min: this.field.min,
        max: this.field.max,
        step: this.field.step,
        pattern: this.field.pattern,
        placeholder: this.field.placeholder || this.field.name,
        class: this.errorClasses,
      }
    },

    extraAttributes() {
      const attrs = this.field.extraAttributes

      return {
        // Leave the default attributes even though we can now specify
        // whatever attributes we like because the old number field still
        // uses the old field attributes
        ...this.defaultAttributes,
        ...attrs,
      }
    },
  },
}
</script>

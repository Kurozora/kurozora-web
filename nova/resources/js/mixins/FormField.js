import get from 'lodash/get'
import isNil from 'lodash/isNil'
import { mapProps } from './propTypes'
import FormEvents from './FormEvents'

export default {
  extends: FormEvents,
  props: {
    ...mapProps([
      'shownViaNewRelationModal',
      'field',
      'viaResource',
      'viaResourceId',
      'viaRelationship',
      'resourceName',
      'showHelpText',
      'mode',
    ]),
    formUniqueId: {
      type: String,
    },
  },

  data: () => ({ value: '' }),

  mounted() {
    this.setInitialValue()

    // Add a default fill method for the field
    this.field.fill = this.fill

    // Register a global event for setting the field's value
    Nova.$on(this.fieldAttributeValueEventName, this.listenToValueChanges)
  },

  beforeUnmount() {
    Nova.$off(this.fieldAttributeValueEventName, this.listenToValueChanges)
  },

  methods: {
    /*
     * Set the initial value for the field
     */
    setInitialValue() {
      this.value = !(
        this.field.value === undefined || this.field.value === null
      )
        ? this.field.value
        : ''
    },

    /**
     * Provide a function that fills a passed FormData object with the
     * field's internal value attribute
     */
    fill(formData) {
      this.fillIfVisible(formData, this.field.attribute, String(this.value))
    },

    /**
     * Provide a function to fills FormData when field is visible.
     */
    fillIfVisible(formData, attribute, value) {
      if (this.isVisible) {
        formData.append(attribute, value)
      }
    },

    /**
     * Update the field's internal value
     */
    handleChange(event) {
      this.value = event.target.value

      if (this.field) {
        this.emitFieldValueChange(this.field.attribute, this.value)
      }
    },

    listenToValueChanges(value) {
      this.value = value
    },
  },

  computed: {
    /**
     * Determine the current field
     */
    currentField() {
      return this.field
    },

    /**
     * Determine if the field should use all the available white-space.
     */
    fullWidthContent() {
      return this.currentField.fullWidth || this.field.fullWidth
    },

    /**
     * Return the placeholder text for the field.
     */
    placeholder() {
      return this.currentField.placeholder || this.field.name
    },

    /**
     * Determine if the field is in visible mode
     */
    isVisible() {
      return this.field.visible
    },

    /**
     * Determine if the field is in readonly mode
     */
    isReadonly() {
      return Boolean(
        this.field.readonly || get(this.field, 'extraAttributes.readonly')
      )
    },
  },
}

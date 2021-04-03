<template>
  <create-form
    @resource-created="handleResourceCreated"
    @cancelled-create="handleCancelledCreate"
    :mode="mode"
    :resource-name="resourceName"
    :via-resource="viaResource"
    :via-resource-id="viaResourceId"
    :via-relationship="viaRelationship"
    :update-form-status="updateFormStatus"
    :should-override-meta="mode == 'form' ? true : false"
  />
</template>

<script>
import { mapProps, PreventsFormAbandonment } from 'laravel-nova'

export default {
  mixins: [PreventsFormAbandonment],

  props: {
    mode: {
      type: String,
      default: 'form',
      validator: val => ['modal', 'form'].includes(val),
    },

    ...mapProps([
      'resourceName',
      'viaResource',
      'viaResourceId',
      'viaRelationship',
    ]),
  },

  methods: {
    handleResourceCreated({ redirect, id }) {
      this.canLeave = true

      if (this.mode == 'form') {
        return this.$router.push({ path: redirect })
      }

      return this.$emit('refresh', { redirect, id })
    },

    handleCancelledCreate() {
      if (this.mode == 'form') {
        return this.$router.back()
      }

      return this.$emit('cancelled-create')
    },
  },
}
</script>

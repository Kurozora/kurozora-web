<template>
  <div v-if="panel.fields.length > 0">
    <heading :level="1" class="mb-3">{{ panel.name }}</heading>

    <card>
      <component
        :class="{
          'remove-bottom-border': index == panel.fields.length - 1,
        }"
        v-for="(field, index) in panel.fields"
        :key="index"
        :is="`${mode}-${field.component}`"
        :errors="validationErrors"
        :resource-id="resourceId"
        :resource-name="resourceName"
        :field="field"
        :via-resource="viaResource"
        :via-resource-id="viaResourceId"
        :via-relationship="viaRelationship"
        @file-deleted="$emit('update-last-retrieved-at-timestamp')"
      />
    </card>
  </div>
</template>

<script>
export default {
  name: 'FormPanel',

  props: {
    panel: {
      type: Object,
      required: true,
    },

    name: {
      default: 'Panel',
    },

    mode: {
      type: String,
      default: 'form',
    },

    fields: {
      type: Array,
      default: [],
    },

    validationErrors: {
      type: Object,
      required: true,
    },

    resourceName: {
      type: String,
      required: true,
    },

    resourceId: {
      type: [Number, String],
    },

    viaResource: {
      type: String,
    },

    viaResourceId: {
      type: [Number, String],
    },

    viaRelationship: {
      type: String,
    },
  },
}
</script>

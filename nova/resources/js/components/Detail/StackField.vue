<template>
  <panel-item :field="field">
    <div slot="value" :class="`text-${field.textAlign}`">
      <template v-if="hasValue">
        <div class="leading-normal">
          <component
            v-for="line in field.lines"
            :key="line.value"
            :class="line.classes"
            :is="`index-${line.component}`"
            :field="line"
          />
        </div>
      </template>
      <p v-else>&mdash;</p>
    </div>
  </panel-item>
</template>

<script>
export default {
  props: ['resource', 'resourceName', 'resourceId', 'field'],

  computed: {
    /**
     * Determine if the field has a value other than null.
     */
    hasValue() {
      return this.field.lines
    },
  },
}
</script>

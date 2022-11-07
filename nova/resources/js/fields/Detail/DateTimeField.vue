<template>
  <PanelItem :index="index" :field="field">
    <template #value>
      <p v-if="fieldHasValue || usesCustomizedDisplay" :title="field.value">
        {{ formattedDateTime }}
      </p>
      <p v-else>&mdash;</p>
    </template>
  </PanelItem>
</template>

<script>
import { DateTime } from 'luxon'
import { FieldValue } from '@/mixins'

export default {
  mixins: [FieldValue],

  props: ['index', 'resource', 'resourceName', 'resourceId', 'field'],

  computed: {
    formattedDateTime() {
      if (this.usesCustomizedDisplay) {
        return this.field.displayedAs
      }

      return DateTime.fromISO(this.field.value)
        .setZone(Nova.config('userTimezone') || Nova.config('timezone'))
        .toLocaleString({
          year: 'numeric',
          month: '2-digit',
          day: '2-digit',
          hour: '2-digit',
          minute: '2-digit',
          timeZoneName: 'short',
        })
    },
  },
}
</script>

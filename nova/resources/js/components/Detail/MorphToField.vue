<template>
  <panel-item :field="field" :field-name="field.resourceLabel">
    <template slot="value">
      <router-link
        v-if="field.viewable && field.value"
        :to="{
          name: 'detail',
          params: {
            resourceName: field.resourceName,
            resourceId: field.morphToId,
          },
        }"
        class="no-underline font-bold dim text-primary"
      >
        {{ field.value }}
      </router-link>
      <p v-else-if="field.value && field.resourceLabel === null">
        {{ field.morphToType }}: {{ field.value }}
      </p>
      <p v-else-if="field.value && field.resourceLabel !== null">
        {{ field.value }}
      </p>
      <p v-else>&mdash;</p>
    </template>
  </panel-item>
</template>

<script>
export default {
  props: ['resourceName', 'resourceId', 'field'],
}
</script>

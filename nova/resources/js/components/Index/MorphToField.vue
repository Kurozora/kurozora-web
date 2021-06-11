<template>
  <div :class="`text-${field.textAlign}`">
    <span>
      <span v-if="field.viewable && field.value">
        <router-link
          :to="{
            name: 'detail',
            params: {
              resourceName: field.resourceName,
              resourceId: field.morphToId,
            },
          }"
          class="no-underline dim text-primary font-bold"
        >
          {{ field.resourceLabel }}: {{ field.value }}
        </router-link>
      </span>
      <span v-else-if="field.value">
        {{ field.resourceLabel || field.morphToType }}: {{ field.value }}
      </span>
      <span v-else>-</span>
    </span>
  </div>
</template>

<script>
export default {
  props: ['resourceName', 'field'],
}
</script>

<template>
  <div :class="`text-${field.textAlign}`">
    <span>
      <span v-if="field.viewable && field.value">
        <RelationPeek
          v-if="field.peekable && field.hasFieldsToPeekAt"
          :resource-name="field.resourceName"
          :resource-id="field.belongsToId"
          :resource="resource"
        >
          <Link
            @click.stop
            :href="
              $url(`/resources/${field.resourceName}/${field.belongsToId}`)
            "
            class="link-default"
          >
            {{ field.value }}
          </Link>
        </RelationPeek>

        <Link
          v-else
          @click.stop
          :href="$url(`/resources/${field.resourceName}/${field.belongsToId}`)"
          class="link-default"
        >
          {{ field.value }}
        </Link>
      </span>
      <span v-else-if="field.value">{{ field.value }}</span>
      <span v-else>&mdash;</span>
    </span>
  </div>
</template>

<script setup>
const props = defineProps({
  resource: { type: Object },
  resourceName: { type: String },
  field: { type: Object },
})
</script>

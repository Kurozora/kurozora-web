<template>
  <div :class="`text-${field.textAlign}`">
    <span>
      <span v-if="field.viewable && field.value">
        <Tooltip
          v-if="field.peekable && field.hasFieldsToPeekAt"
          :triggers="['hover']"
          placement="top-start"
          theme="plain"
        >
          <Link
            @click.stop
            :href="$url(`/resources/${field.resourceName}/${field.morphToId}`)"
            class="link-default"
          >
            {{ field.resourceLabel }}: {{ field.value }}
          </Link>

          <template #content>
            <RelationPeek
              :resource-name="field.resourceName"
              :resource-id="field.morphToId"
            />
          </template>
        </Tooltip>

        <Link
          v-else
          @click.stop
          :href="$url(`/resources/${field.resourceName}/${field.morphToId}`)"
          class="link-default"
        >
          {{ field.resourceLabel }}: {{ field.value }}
        </Link>
      </span>
      <span v-else-if="field.value">
        {{ field.resourceLabel || field.morphToType }}: {{ field.value }}
      </span>
      <span v-else>&mdash;</span>
    </span>
  </div>
</template>

<script>
export default {
  props: ['resourceName', 'field'],
}
</script>

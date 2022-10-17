<template>
  <PanelItem :index="index" :field="field" :field-name="field.resourceLabel">
    <template #value>
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
            {{ field.value }}
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
          :href="$url(`/resources/${field.resourceName}/${field.morphToId}`)"
          class="link-default"
        >
          {{ field.value }}
        </Link>
      </span>

      <p v-else-if="field.value && field.resourceLabel === null">
        {{ field.morphToType }}: {{ field.value }}
      </p>
      <p v-else-if="field.value && field.resourceLabel !== null">
        {{ field.value }}
      </p>
      <p v-else>&mdash;</p>
    </template>
  </PanelItem>
</template>

<script>
export default {
  props: ['index', 'resourceName', 'resourceId', 'field'],
}
</script>

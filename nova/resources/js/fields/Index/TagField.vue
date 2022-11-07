<template>
  <div :class="`text-${field.textAlign}`">
    <Dropdown v-if="field.value.length > 0">
      <DropdownTrigger
        class="text-gray-500 inline-flex items-center cursor-pointer"
        :showArrow="false"
      >
        <span class="link-default font-bold">{{ __('View') }}</span>
      </DropdownTrigger>

      <template #menu>
        <DropdownMenu width="auto">
          <div class="p-2">
            <TagList
              v-if="field.style === 'list'"
              :tags="field.value"
              :resource-name="field.resourceName"
              :editable="false"
              :with-preview="field.withPreview"
            />
            <TagGroup
              v-if="field.style === 'group'"
              :tags="field.value"
              :resource-name="field.resourceName"
              :editable="false"
              :with-preview="field.withPreview"
            />
          </div>
        </DropdownMenu>
      </template>
    </Dropdown>
    <p v-else>&mdash;</p>
  </div>
</template>

<script>
export default {
  props: ['index', 'resource', 'resourceName', 'resourceId', 'field'],
}
</script>

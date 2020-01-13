<template>
  <div :class="`text-${field.textAlign}`">
    <tooltip trigger="click">
      <div class="text-primary inline-flex items-center dim cursor-pointer">
        <span class="text-primary font-bold">{{ __('View') }}</span>
      </div>

      <tooltip-content slot="content">
        <ul class="list-reset">
          <li v-for="option in value" class="mb-1">
            <span
              :class="classes[option.checked]"
              class="inline-flex items-center py-1 pl-2 pr-3 rounded-full font-bold text-sm"
            >
              <boolean-icon :value="option.checked" width="20" height="20" />
              <span class="ml-1">{{ option.label }}</span>
            </span>
          </li>
        </ul>
      </tooltip-content>
    </tooltip>
  </div>
</template>

<script>
export default {
  props: ['resourceName', 'field'],

  data: () => ({
    value: [],
    classes: {
      true: 'bg-success-light text-success-dark',
      false: 'bg-danger-light text-danger-dark',
    },
  }),

  created() {
    this.field.value = this.field.value || {}

    this.value = _(this.field.options)
      .map(o => {
        return {
          name: o.name,
          label: o.label,
          checked: this.field.value[o.name] || false,
        }
      })
      .value()
  },
}
</script>

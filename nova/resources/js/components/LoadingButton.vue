<template>
  <button ref="button" v-bind="{ ...$attrs }" v-on="$listeners">
    <span :class="{ hidden: processing || loading }">
      <slot />
    </span>

    <span v-if="processing || loading">
      <loader width="32" />
    </span>
  </button>
</template>

<script>
export default {
  props: {
    loading: {
      type: Boolean,
      default: false,
    },

    processing: {
      type: Boolean,
      default: false,
    },
  },

  methods: {
    focus() {
      this.$refs.button.focus()
    },
  },
}
</script>

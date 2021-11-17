<template>
  <loading-button
    class="btn btn-default btn-primary inline-flex items-center relative"
    type="button"
    ref="button"
    :processing="processing"
  >
    <slot />
  </loading-button>
</template>

<script>
import LoadingButton from './LoadingButton'

export default {
  components: {
    LoadingButton,
  },

  props: {
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

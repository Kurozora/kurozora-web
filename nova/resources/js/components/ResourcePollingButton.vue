<template>
  <button
    class="rounded p-2 flex flex-no-shrink focus:shadow-outline focus:outline-none"
    @click="togglePolling"
    v-tooltip.click="buttonLabel"
  >
    <svg
      class="w-6 h-6"
      :class="{
        'text-success': currentlyPolling,
        'text-60': !currentlyPolling,
      }"
      fill="none"
      stroke="currentColor"
      viewBox="0 0 24 24"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
      ></path>
    </svg>
  </button>
</template>

<script>
export default {
  props: {
    currentlyPolling: {
      type: Boolean,
      default: false,
    },
  },

  methods: {
    togglePolling() {
      return this.currentlyPolling
        ? this.$emit('stop-polling')
        : this.$emit('start-polling')
    },
  },

  computed: {
    buttonLabel() {
      return this.currentlyPolling
        ? this.__('Stop Polling')
        : this.__('Start Polling')
    },
  },
}
</script>

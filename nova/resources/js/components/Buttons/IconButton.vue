<template>
  <button
    class="inline-flex focus:ring focus:ring-primary-200 focus:outline-none rounded"
    :class="buttonClasses"
  >
    <Icon :type="iconType" class="hover:opacity-50" />
  </button>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  iconType: { type: String, default: 'dots-horizontal' },
  small: { type: Boolean },
  medium: { type: Boolean },
  large: { type: Boolean },
})

const buttonClasses = computed(() => [
  props.small && 'w-6 h-6',
  props.medium && 'w-8 h-8',
  props.large && 'w-9 h-9',
])
</script>

<template>
  <img :src="src" :class="avatarClasses" />
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  src: { type: String },
  rounded: { type: Boolean, default: true },
  small: { type: Boolean },
  medium: { type: Boolean },
  large: { type: Boolean },
})

const avatarClasses = computed(() => [
  props.small && 'w-6 h-6',
  props.medium && !props.small && !props.large && 'w-8 h-8',
  props.large && 'w-12 h-12',
  props.rounded && 'rounded-full',
])
</script>

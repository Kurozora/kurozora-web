<template>
  <span v-if="!error">
    <img
      :class="classes"
      :style="styles"
      :src="src"
      @load="handleLoad"
      @error="handleError"
    />
  </span>
  <a v-else :href="src">
    <Icon
      type="exclamation-circle"
      class="text-red-500"
      v-tooltip="__('The image could not be loaded.')"
    />
  </a>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useLocalization } from '@/mixins/Localization'

const { __ } = useLocalization()

const props = defineProps({
  src: { type: String },
  maxWidth: { type: Number, default: 320 },
  rounded: { type: Boolean, default: false },
  aspect: {
    type: String,
    default: 'aspect-auto',
    validator: v => ['aspect-auto', 'aspect-square'].includes(v),
  },
})

const loaded = ref(false)
const error = ref(false)

const handleLoad = () => (loaded.value = true)
const handleError = () => {
  error.value = true
  Nova.log(`${__('The image could not be loaded.')}: ${props.src}`)
}

const classes = computed(() => [props.rounded && 'rounded-full'])

const styles = computed(() => ({
  'max-width': `${props.maxWidth}px`,
  ...(props.aspect === 'aspect-square' && { width: `${props.maxWidth}px` }),
  ...(props.aspect === 'aspect-square' && { height: `${props.maxWidth}px` }),
}))
</script>

<script>
export default {
  inheritAttrs: false,
}
</script>

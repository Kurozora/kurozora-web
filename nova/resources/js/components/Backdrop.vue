<template>
  <div
    v-bind="$attrs"
    v-show="props.show"
    class="absolute left-0 right-0 bottom-0 h-screen"
    :style="{ top: `${scrollY}px` }"
  />
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
})

const scrollY = ref()
const scrollEvent = () => {
  scrollY.value = window.scrollY
}

onMounted(() => {
  scrollEvent()

  document.addEventListener('scroll', scrollEvent)
})

onBeforeUnmount(() => {
  document.removeEventListener('scroll', scrollEvent)
})
</script>

<script>
export default {
  inheritAttrs: false,
}
</script>

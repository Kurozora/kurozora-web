<template>
  <button
    type="button"
    @click="handleClick"
    class="inline-flex items-center rounded-full px-2 space-x-1 -mx-2 text-gray-500 dark:text-gray-400 hover:bg-gray-100 hover:text-gray-500 active:text-gray-600 dark:hover:bg-gray-900"
  >
    <slot />

    <CopyIcon :copied="copied" />
  </button>
</template>

<script setup>
import { ref } from 'vue'
import debounce from 'lodash/debounce'

const copied = ref(false)

const denouncedHandleClick = debounce(
  () => {
    copied.value = !copied.value
    setTimeout(() => (copied.value = !copied.value), 2000)
  },
  2000,
  { leading: true, trailing: false }
)

const handleClick = () => denouncedHandleClick()
</script>

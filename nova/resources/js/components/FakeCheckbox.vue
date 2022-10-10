<template>
  <span
    class="fake-checkbox"
    :class="{
      'fake-checkbox-checked': checked,
      'fake-checkbox-indeterminate': indeterminate,
    }"
  />
</template>

<script setup>
const props = defineProps({
  checked: { type: Boolean, default: false },
  indeterminate: { type: Boolean, default: false },
})
</script>

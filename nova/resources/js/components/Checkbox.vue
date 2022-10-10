<template>
  <input
    type="checkbox"
    class="checkbox"
    :disabled="disabled"
    :checked="checked"
    @change="handleChange"
    @click.stop
  />
</template>

<script setup>
const props = defineProps({
  checked: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
})

const emit = defineEmits(['input'])

const handleChange = e => emit('input', e)
</script>

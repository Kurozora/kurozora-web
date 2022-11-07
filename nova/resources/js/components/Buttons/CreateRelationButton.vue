<template>
  <IconButton
    @click="$emit('click')"
    type="button"
    class="text-primary-500 hover:text-primary-600 focus:text-primary-400 active:text-primary-600"
    icon-type="plus-circle"
  />
</template>

<script>
export default {
  emits: ['click'],
}
</script>

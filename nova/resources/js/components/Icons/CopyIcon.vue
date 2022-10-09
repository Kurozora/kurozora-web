<template>
  <Icon
    v-if="copied"
    class="text-green-500"
    :solid="true"
    type="check-circle"
    width="14"
  />
  <Icon
    v-else
    class="text-gray-400 dark:text-gray-500"
    :solid="true"
    type="clipboard"
    width="14"
  />
</template>

<script setup>
const props = defineProps(['copied'])
</script>

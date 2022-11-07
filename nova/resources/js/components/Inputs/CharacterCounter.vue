<template>
  <p class="text-xs font-semibold text-gray-400 text-right space-x-1">
    <span
      :class="{
        'text-red-500': inDangerZone,
        'text-yellow-500': inWarningZone,
      }"
    >
      {{ count }}
    </span>
    <span>/</span>
    <span>{{ limit }}</span>
  </p>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  count: { type: Number },
  limit: { type: Number },
})

const dangerZone = 0.9
const warningZone = 0.7

const ratio = computed(() => props.count / props.limit)

const inWarningZone = computed(
  () => ratio.value > warningZone && ratio.value <= dangerZone
)

const inDangerZone = computed(() => ratio.value > dangerZone)
</script>

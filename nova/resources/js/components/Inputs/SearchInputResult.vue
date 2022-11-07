<template>
  <div class="flex items-center">
    <div v-if="option.avatar" class="flex-none mr-3">
      <img :src="option.avatar" class="w-8 h-8 rounded-full block" />
    </div>

    <div class="flex-auto">
      <div
        class="text-sm font-semibold leading-normal"
        :class="{ 'text-white dark:text-gray-900': selected }"
      >
        {{ option.display }}
      </div>

      <div
        v-if="withSubtitles"
        class="text-xs font-semibold leading-normal text-gray-500"
        :class="{ 'text-white dark:text-gray-700': selected }"
      >
        <span v-if="option.subtitle">{{ option.subtitle }}</span>
        <span v-else>{{ __('No additional information...') }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  option: { type: Object, required: true },
  selected: { type: Boolean, default: false },
  withSubtitles: { type: Boolean, default: true },
})
</script>

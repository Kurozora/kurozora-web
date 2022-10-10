<template>
  <div ref="image" />
  <slot name="loading" v-if="loading">
    <div class="flex" :class="[$attrs.class, alignmentClass]">
      <span>
        <Loader width="30" />
      </span>
    </div>
  </slot>
  <slot v-if="!loading && missing" name="missing" />
</template>

<script setup>
import {minimum} from '@/util'
import {computed, onMounted, ref} from 'vue'

const props = defineProps({
  src: { type: String },
  maxWidth: { type: Number, default: 320 },
  rounded: { type: Boolean, default: false },
  align: { type: String, default: 'center' },
})

const image = ref(null)
const loading = ref(true)
const missing = ref(false)

const alignmentClass = computed(() => {
  return {
    left: 'items-center justify-start',
    center: 'items-center justify-center',
    right: 'items-center justify-start',
  }[props.align]
})

onMounted(async () => {
  try {
    const newImage = await minimum(
      new Promise((resolve, reject) => {
        const image = new Image()
        image.addEventListener('load', () => resolve(image))
        image.addEventListener('error', () => reject())
        image.src = props.src
        image.classList.add('inline-block')
        image.draggable = false
        if (props.rounded) image.classList.add('rounded-full')
        if (props.maxWidth) image.style.maxWidth = `${props.maxWidth}px`
      })
    )
    image.value.replaceWith(newImage)
  } catch (error) {
    missing.value = true
  }

  loading.value = false
})
</script>

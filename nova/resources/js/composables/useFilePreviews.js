import { computed } from 'vue'

export function useFilePreviews(file) {
  const imageTypes = ['image/png', 'image/jpeg', 'image/gif']

  const type = computed(() =>
    imageTypes.includes(file.value.type) ? 'image' : 'other'
  )

  const previewUrl = computed(() =>
    URL.createObjectURL(file.value.originalFile)
  )

  const isImage = computed(() => type.value === 'image')

  return {
    imageTypes,
    isImage,
    type,
    previewUrl,
  }
}

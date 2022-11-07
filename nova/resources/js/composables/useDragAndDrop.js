import { ref } from 'vue'

export function useDragAndDrop(emit) {
  const startedDrag = ref(false)
  const files = ref([])

  const handleOnDragEnter = () => (startedDrag.value = true)

  const handleOnDragLeave = () => (startedDrag.value = false)

  const handleOnDrop = e => {
    files.value = e.dataTransfer.files
    emit('change', e.dataTransfer.files)
  }

  const handleDrop = files =>
    emit('change', props.multiple ? files : [files[0]])

  return {
    startedDrag,
    handleOnDragEnter,
    handleOnDragLeave,
    handleOnDrop,
  }
}

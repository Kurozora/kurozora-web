<template>
  <div ref="searchInputContainer" v-bind="$attrs">
    <div class="relative">
      <!-- Search Input -->
      <input
        @click.stop="open"
        @keydown.enter.prevent="chooseSelected"
        @keydown.down.prevent="move(1)"
        @keydown.up.prevent="move(-1)"
        class="block w-full form-control form-input form-input-bordered"
        :class="{
          'form-input-border-error': error,
        }"
        v-model="searchText"
        :disabled="disabled"
        ref="searchInput"
        tabindex="0"
        type="search"
        :placeholder="__('Search')"
        spellcheck="false"
        :aria-expanded="dropdownShown === true ? 'true' : 'false'"
      />
    </div>

    <!-- Search Result Dropdown -->
    <div
      v-if="dropdownShown"
      ref="searchResultsDropdown"
      :style="{ zIndex: 2000 }"
    >
      <div
        v-show="loading || options.length > 0"
        class="rounded-lg px-0 bg-white dark:bg-gray-900 shadow border border-gray-200 dark:border-gray-700 my-1 overflow-hidden"
        :style="{ width: searchInputWidth + 'px', zIndex: 2000 }"
      >
        <!-- Search Results -->
        <div
          ref="searchResultsContainer"
          class="relative overflow-y-scroll text-sm divide-y divide-gray-100 dark:divide-gray-800"
          tabindex="-1"
          style="max-height: 155px"
        >
          <div v-if="loading" class="px-3 py-2">
            <Loader width="30" />
          </div>

          <div
            v-else
            v-for="(option, index) in options"
            @click.stop="choose(option)"
            :ref="el => setSelectedRef(index, el)"
            :key="getTrackedByKey(option)"
            class="px-3 py-1.5 cursor-pointer"
            :class="{
              [`search-input-item-${index}`]: true,
              'hover:bg-gray-100 dark:hover:bg-gray-800':
                index !== selectedOptionIndex,
              'bg-primary-500 text-white dark:text-gray-900':
                index === selectedOptionIndex,
            }"
          >
            <slot
              name="option"
              :option="option"
              :selected="index === selectedOptionIndex"
            />
          </div>
        </div>
      </div>
    </div>

    <teleport to="body">
      <Backdrop @click="close" :show="dropdownShown" class="z-[35]" />
    </teleport>
  </div>
</template>

<script setup>
import { createPopper } from '@popperjs/core'
import { ref, onMounted, onBeforeUnmount, computed, nextTick, watch } from 'vue'
import debounce from 'lodash/debounce'
import get from 'lodash/get'
import findIndex from 'lodash/findIndex'

const debouncer = debounce(callback => callback(), props.debounce)

// Events
const emit = defineEmits(['clear', 'input', 'selected'])

// Props
const props = defineProps({
  error: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  options: { type: Array, default: [] },
  loading: { type: Boolean, default: false },
  debounce: { type: Number, default: 500 },
  trackBy: { type: String },
})

// References
const popper = ref(null)

// Elements
const searchInput = ref(null)
const searchResultsDropdown = ref(null)
const searchResultsContainer = ref(null)
const searchInputContainer = ref(null)
const selectedOption = ref(null)

// State
const searchText = ref('')
const dropdownShown = ref(false)
const selectedOptionIndex = ref(0)

// Lifecycle Methods
onMounted(() => document.addEventListener('keydown', handleEscape))
onBeforeUnmount(() => document.removeEventListener('keydown', handleEscape))

// Watchers
watch(searchText, newValue => {
  if (newValue) {
    dropdownShown.value = true
  }

  selectedOptionIndex.value = 0

  if (searchResultsContainer.value) {
    searchResultsContainer.value.scrollTop = 0
  } else {
    nextTick(() => (searchResultsContainer.value.scrollTop = 0))
  }

  debouncer(() => emit('input', newValue))
})

watch(dropdownShown, shown =>
  shown === true ? nextTick(() => createSearchPopper()) : popper.value.destroy()
)

// Computed Properties
const searchInputWidth = computed(() => searchInput.value?.offsetWidth)

// Methods
function getTrackedByKey(option) {
  return get(option, props.trackBy)
}

function createSearchPopper() {
  popper.value = createPopper(searchInput.value, searchResultsDropdown.value, {
    placement: 'bottom-start',
    onFirstUpdate: () => {
      searchInputContainer.value.scrollTop =
        searchInputContainer.value.scrollHeight
      updateScrollPosition()
    },
  })
}

function open() {
  dropdownShown.value = true
}

function close() {
  dropdownShown.value = false
}

function clear() {
  selectedOptionIndex.value = null
  close()
  emit('clear')
}

function move(offset) {
  let newIndex = selectedOptionIndex.value + offset

  if (newIndex >= 0 && newIndex < props.options.length) {
    selectedOptionIndex.value = newIndex

    nextTick(() => updateScrollPosition())
  }
}

function findOptionIndex(option) {
  return findIndex(props.options, [props.trackBy, get(option, props.trackBy)])
}

function findOption(index) {
  return props.options[index]
}

function choose(option) {
  emit('selected', option)
  nextTick(() => close())
  searchText.value = ''
}

function chooseSelected(event) {
  if (event.isComposing || event.keyCode === 229) return
  const selectedOption = findOption(selectedOptionIndex.value)
  choose(selectedOption)
}

function handleEscape(e) {
  // 'tab' or 'escape'
  if (dropdownShown.value === true && (e.keyCode === 9 || e.keyCode === 27)) {
    setTimeout(() => close(), 50)
  }
}

function updateScrollPosition() {
  // If we've highlighted an option...
  if (selectedOption.value) {
    // If we need to scroll the dropdown down to the selected element...
    if (
      selectedOption.value.offsetTop >
      searchResultsContainer.value.scrollTop +
        searchResultsContainer.value.clientHeight -
        selectedOption.value.clientHeight
    ) {
      searchResultsContainer.value.scrollTop =
        selectedOption.value.offsetTop +
        selectedOption.value.clientHeight -
        searchResultsContainer.value.clientHeight
    }

    // If we need to scroll the dropdown back up...
    if (
      selectedOption.value.offsetTop < searchResultsContainer.value.scrollTop
    ) {
      searchResultsContainer.value.scrollTop = selectedOption.value.offsetTop
    }
  }
}

function setSelectedRef(index, el) {
  if (selectedOptionIndex.value === index) {
    selectedOption.value = el
  }
}
</script>

<script>
export default {
  inheritAttrs: false,
}
</script>

<!--  props: {-->
<!--    dataTestid: {},-->
<!--  },-->

<!--    chooseSelected(event) {-->
<!--      if (event.isComposing || event.keyCode === 229) return-->

<!--      if (this.data[this.selected] !== undefined) {-->
<!--        this.$emit('selected', this.data[this.selected])-->
<!--        this.$refs.search.focus()-->
<!--        this.$nextTick(() => this.close())-->
<!--        this.search = ''-->
<!--      }-->
<!--    },-->

<!--    choose(option) {-->
<!--      this.selected = findIndex(this.data, [-->
<!--        this.trackBy,-->
<!--        get(option, this.trackBy),-->
<!--      ])-->
<!--      this.$emit('selected', option)-->
<!--      this.$refs.search.focus()-->
<!--      this.$nextTick(() => this.close())-->
<!--      this.search = ''-->
<!--    },-->

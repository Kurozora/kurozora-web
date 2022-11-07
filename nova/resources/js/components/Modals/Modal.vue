<template>
  <teleport to="#modals">
    <template v-if="show">
      <div
        v-bind="defaultAttributes"
        class="modal fixed inset-0 z-[60]"
        :class="{
          'px-3 md:px-0 py-3 md:py-6 overflow-x-hidden overflow-y-auto':
            modalStyle === 'window',
          'h-full': modalStyle === 'fullscreen',
        }"
        :tabindex="tabIndex"
        :role="role"
        :data-modal-open="show"
        :aria-modal="show"
      >
        <div
          class="relative mx-auto z-20"
          :class="contentClasses"
          ref="modalContent"
        >
          <slot />
        </div>
      </div>

      <div
        class="fixed inset-0 z-[55] bg-gray-500 dark:bg-gray-900 opacity-75"
        dusk="modal-backdrop"
      />
    </template>
  </teleport>
</template>

<script>
import { mapGetters, mapMutations } from 'vuex'
import filter from 'lodash/filter'
import omit from 'lodash/omit'
import trapFocus from '@/util/trapFocus'

export default {
  emits: ['showing', 'closing', 'close-via-escape'],

  inheritAttrs: false,

  props: {
    show: {
      type: Boolean,
      default: false,
    },

    size: {
      type: String,
      default: 'xl',
      validator: v =>
        [
          'sm',
          'md',
          'lg',
          'xl',
          '2xl',
          '3xl',
          '4xl',
          '5xl',
          '6xl',
          '7xl',
        ].includes(v),
    },

    modalStyle: {
      type: String,
      default: 'window',
    },

    role: {
      type: String,
      default: 'dialog',
    },
  },

  watch: {
    show(showing) {
      this.handleVisibilityChange(showing)
    },
  },

  created() {
    document.addEventListener('keydown', this.closeOnEscape)
  },

  beforeUnmount() {
    document.body.classList.remove('overflow-hidden')
    Nova.resumeShortcuts()
    document.removeEventListener('keydown', this.closeOnEscape)
  },

  mounted() {
    if (this.show === true) {
      this.handleVisibilityChange(true)
    }
  },

  methods: {
    ...mapMutations(['allowLeavingModal', 'preventLeavingModal']),

    handleVisibilityChange(showing) {
      this.$nextTick(() => {
        if (showing === true) {
          this.$emit('showing')
          document.body.classList.add('overflow-hidden')
          Nova.pauseShortcuts()
          trapFocus(this.$refs.modalContent)
        } else {
          this.$emit('closing')
          document.body.classList.remove('overflow-hidden')
          Nova.resumeShortcuts()
        }

        this.allowLeavingModal()
      })
    },

    closeOnEscape(event) {
      if (event.key === 'Escape' && this.show === true) {
        this.$emit('close-via-escape')
      }
    },
  },

  computed: {
    ...mapGetters(['canLeaveModal']),

    tabIndex() {
      return this.show ? 0 : -1
    },

    defaultAttributes() {
      return omit(this.$attrs, ['class'])
    },

    sizeClasses() {
      return {
        sm: 'max-w-sm',
        md: 'max-w-md',
        lg: 'max-w-lg',
        xl: 'max-w-xl',
        '2xl': 'max-w-2xl',
        '3xl': 'max-w-3xl',
        '4xl': 'max-w-4xl',
        '5xl': 'max-w-5xl',
        '6xl': 'max-w-6xl',
        '7xl': 'max-w-7xl',
      }
    },

    contentClasses() {
      let windowedClasses = this.modalStyle === 'window' ? this.sizeClasses : {}

      return filter([
        windowedClasses[this.size] ?? null,
        this.modalStyle === 'fullscreen' ? 'h-full' : '',
        this.$attrs.class,
      ])
    },
  },
}
</script>

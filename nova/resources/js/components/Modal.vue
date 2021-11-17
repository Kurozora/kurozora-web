<template>
  <div
    class="modal select-none fixed pin z-50 overflow-x-hidden overflow-y-auto"
  >
    <div class="relative mx-auto flex justify-center z-20 py-view">
      <div v-on-clickaway="backdropClose"><slot /></div>
    </div>
  </div>
</template>

<script>
import {mixin as clickaway} from 'vue-clickaway'
import composedPath from '@/polyfills/composedPath'

export default {
  mixins: [clickaway],

  props: {
    classWhitelist: [Array, String],

    closesViaEscape: {
      type: Boolean,
      default: true,
    },

    closesViaBackdrop: {
      type: Boolean,
      default: true,
    },
  },

  created() {
    document.addEventListener('keydown', this.handleEscape)
    document.body.classList.add('overflow-hidden')

    const modalBg = document.createElement('div')
    modalBg.classList = 'fixed pin bg-80 z-20 opacity-75'

    this.modalBg = modalBg

    document.body.appendChild(this.modalBg)
  },

  mounted() {
    Nova.pauseShortcuts()
  },

  destroyed() {
    document.removeEventListener('keydown', this.handleEscape)
    document.body.classList.remove('overflow-hidden')
    document.body.removeChild(this.modalBg)

    Nova.resumeShortcuts()
  },

  data: () => ({ modalBg: null }),

  methods: {
    handleEscape(e) {
      e.stopPropagation()

      if (e.keyCode == 27 && this.closesViaEscape === true) {
        this.close(e)
      }
    },

    backdropClose(e) {
      if (!e.isTrusted) return

      let classArray = Array.isArray(this.classWhitelist)
        ? this.classWhitelist
        : [this.classWhitelist]

      if (
        _.filter(classArray, className => pathIncludesClass(e, className))
          .length > 0
      ) {
        return
      }

      if (this.closesViaBackdrop === true) {
        this.close(e)
      }
    },

    close(e) {
      this.$emit('modal-close', e)
    },
  },
}

function pathIncludesClass(event, className) {
  return composedPath(event)
    .filter(el => el !== document && el !== window)
    .reduce((acc, e) => acc.concat([...e.classList]), [])
    .includes(className)
}
</script>

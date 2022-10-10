<template>
  <Modal data-testid="restore-resource-modal" :show="show" size="sm">
    <form
      @submit.prevent="handleConfirm"
      class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden"
      style="width: 460px"
    >
      <slot>
        <ModalHeader v-text="__('Restore Resource')" />
        <ModalContent>
          <p class="leading-normal">
            {{ __('Are you sure you want to restore the selected resources?') }}
          </p>
        </ModalContent>
      </slot>

      <ModalFooter>
        <div class="ml-auto">
          <LinkButton
            type="button"
            data-testid="cancel-button"
            dusk="cancel-restore-button"
            @click.prevent="handleClose"
            class="mr-3"
          >
            {{ __('Cancel') }}
          </LinkButton>

          <LoadingButton
            ref="confirmButton"
            dusk="confirm-restore-button"
            data-testid="confirm-button"
            :processing="working"
            :disabled="working"
            type="submit"
          >
            {{ __('Restore') }}
          </LoadingButton>
        </div>
      </ModalFooter>
    </form>
  </Modal>
</template>

<script>
export default {
  emits: ['confirm', 'close'],

  props: {
    show: { type: Boolean, default: false },
  },

  data: () => ({
    working: false,
  }),

  /**
   * Mount the component.
   */
  mounted() {
    this.$nextTick(() => {
      // this.$refs.confirmButton.focus()
    })
  },

  methods: {
    handleClose() {
      this.$emit('close')
      this.working = false
    },

    handleConfirm() {
      this.$emit('confirm')
      this.working = true
    },
  },
}
</script>

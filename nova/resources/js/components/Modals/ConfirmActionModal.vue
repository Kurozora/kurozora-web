<template>
  <Modal
    :show="show"
    @showing="handleShowingModal"
    @close-via-escape="handlePreventModalAbandonmentOnClose"
    data-testid="confirm-action-modal"
    tabindex="-1"
    role="dialog"
    :size="action.modalSize"
    :modal-style="action.modalStyle"
  >
    <form
      ref="theForm"
      autocomplete="off"
      @change="onUpdateFormStatus"
      @submit.prevent.stop="$emit('confirm')"
      :data-form-unique-id="formUniqueId"
      class="bg-white dark:bg-gray-800"
      :class="{
        'rounded-lg shadow-lg overflow-hidden space-y-6':
          action.modalStyle === 'window',
        'flex flex-col justify-between h-full':
          action.modalStyle === 'fullscreen',
      }"
    >
      <div
        class="space-y-6"
        :class="{
          'overflow-hidden overflow-y-auto': action.modalStyle === 'fullscreen',
        }"
      >
        <ModalHeader v-text="action.name" />

        <!-- Confirmation Text -->
        <p
          v-if="action.confirmText"
          class="px-8"
          :class="{ 'text-red-500': action.destructive }"
        >
          {{ action.confirmText }}
        </p>

        <!-- Action Fields -->
        <div v-if="action.fields.length > 0">
          <div
            class="action"
            v-for="field in action.fields"
            :key="field.attribute"
          >
            <component
              :is="'form-' + field.component"
              :errors="errors"
              :resource-name="resourceName"
              :field="field"
              :show-help-text="true"
              :form-unique-id="formUniqueId"
              mode="modal"
              :sync-endpoint="syncEndpoint"
              @field-changed="onUpdateFormStatus"
            />
          </div>
        </div>
      </div>

      <ModalFooter>
        <div class="flex items-center ml-auto">
          <CancelButton
            component="button"
            type="button"
            dusk="cancel-action-button"
            class="ml-auto mr-3"
            @click="$emit('close')"
          >
            {{ action.cancelButtonText }}
          </CancelButton>

          <LoadingButton
            type="submit"
            ref="runButton"
            dusk="confirm-action-button"
            :disabled="working"
            :loading="working"
            :component="action.destructive ? 'DangerButton' : 'DefaultButton'"
          >
            {{ action.confirmButtonText }}
          </LoadingButton>
        </div>
      </ModalFooter>
    </form>
  </Modal>
</template>

<script>
import { PreventsModalAbandonment } from '@/mixins'
import { uid } from 'uid/single'

export default {
  emits: ['confirm', 'close'],

  mixins: [PreventsModalAbandonment],

  props: {
    action: { type: Object, required: true },
    endpoint: { type: String, required: false },
    errors: { type: Object, required: true },
    resourceName: { type: String, required: true },
    selectedResources: { type: [Array, String], required: true },
    show: { type: Boolean, default: false },
    working: Boolean,
  },

  data: () => ({
    formUniqueId: uid(),
  }),

  created() {
    document.addEventListener('keydown', this.handleKeydown)
  },

  beforeUnmount() {
    document.removeEventListener('keydown', this.handleKeydown)
  },

  methods: {
    /**
     * Prevent accidental abandonment only if form was changed.
     */
    onUpdateFormStatus() {
      this.updateModalStatus()
    },

    /**
     * Handle focus when modal being shown.
     */
    handleShowingModal(e) {
      // If the modal has inputs, let's highlight the first one, otherwise
      // let's highlight the submit button
      this.$nextTick(() => {
        if (this.$refs.theForm) {
          let formFields = this.$refs.theForm.querySelectorAll(
            'input, textarea, select'
          )

          formFields.length > 0
            ? formFields[0].focus()
            : this.$refs.runButton.focus()
        } else {
          this.$refs.runButton.focus()
        }
      })
    },

    handlePreventModalAbandonmentOnClose() {
      this.handlePreventModalAbandonment(
        () => {
          this.$emit('close')
        },
        () => {
          e.stopPropagation()
        }
      )
    },
  },

  computed: {
    syncEndpoint() {
      let searchParams = new URLSearchParams({ action: this.action.uriKey })

      if (this.selectedResources === 'all') {
        searchParams.append('resources', 'all')
      } else {
        this.selectedResources.forEach(resourceId => {
          searchParams.append('resources[]', resourceId)
        })
      }

      return (
        (this.endpoint || `/nova-api/${this.resourceName}/action`) +
        '?' +
        searchParams.toString()
      )
    },
  },
}
</script>

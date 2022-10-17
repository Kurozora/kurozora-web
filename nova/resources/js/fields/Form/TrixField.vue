<template>
  <DefaultField
    :field="field"
    :errors="errors"
    :full-width-content="fullWidthContent"
    :key="index"
    :show-help-text="showHelpText"
  >
    <template #field>
      <div class="rounded-lg" :class="{ disabled: isReadonly }">
        <Trix
          name="trixman"
          :value="value"
          @change="handleChange"
          @file-added="handleFileAdded"
          @file-removed="handleFileRemoved"
          :class="{ 'form-input-border-error': hasError }"
          :with-files="field.withFiles"
          v-bind="field.extraAttributes"
          :disabled="isReadonly"
          class="rounded-lg"
        />
      </div>
    </template>
  </DefaultField>
</template>

<script>
import { FormField, HandlesValidationErrors } from '@/mixins'

export default {
  emits: ['field-changed'],

  mixins: [HandlesValidationErrors, FormField],

  data: () => ({ draftId: null, index: 0 }),

  async created() {
    const {
      data: { draftId },
    } = await Nova.request().get(
      `/nova-api/{resourceName}/trix-attachment/{field}/draftId`
    )

    this.draftId = draftId
  },

  mounted() {
    Nova.$on(this.fieldAttributeValueEventName, this.listenToValueChanges)
  },

  beforeUnmount() {
    Nova.$off(this.fieldAttributeValueEventName, this.listenToValueChanges)

    this.cleanUp()
  },

  methods: {
    /**
     * Update the field's internal value when it's value changes
     */
    handleChange(value) {
      this.value = value

      this.$emit('field-changed')
    },

    fill(formData) {
      this.fillIfVisible(formData, this.field.attribute, this.value || '')
      this.fillIfVisible(
        formData,
        `${this.field.attribute}DraftId`,
        this.draftId
      )
    },

    /**
     * Initiate an attachement upload
     */
    handleFileAdded({ attachment }) {
      if (attachment.file) {
        this.uploadAttachment(attachment)
      }
    },

    /**
     * Upload an attachment
     */
    uploadAttachment(attachment) {
      const data = new FormData()
      data.append('Content-Type', attachment.file.type)
      data.append('attachment', attachment.file)
      data.append('draftId', this.draftId)

      Nova.request()
        .post(
          `/nova-api/${this.resourceName}/trix-attachment/${this.field.attribute}`,
          data,
          {
            onUploadProgress: function (progressEvent) {
              attachment.setUploadProgress(
                Math.round((progressEvent.loaded * 100) / progressEvent.total)
              )
            },
          }
        )
        .then(({ data: { url } }) => {
          return attachment.setAttributes({
            url: url,
            href: url,
          })
        })
        .catch(error => {
          this.$toasted.show(
            __('An error occurred while uploading the file.'),
            { type: 'error' }
          )
        })
    },

    /**
     * Remove an attachment from the server
     */
    handleFileRemoved({ attachment: { attachment } }) {
      Nova.request()
        .delete(
          `/nova-api/${this.resourceName}/trix-attachment/${this.field.attribute}`,
          {
            params: {
              attachmentUrl: attachment.attributes.values.url,
            },
          }
        )
        .then(response => {})
        .catch(error => {})
    },

    /**
     * Purge pending attachments for the draft
     */
    cleanUp() {
      if (this.field.withFiles) {
        Nova.request()
          .delete(
            `/nova-api/${this.resourceName}/trix-attachment/${this.field.attribute}/${this.draftId}`
          )
          .then(response => {})
          .catch(error => {})
      }
    },

    listenToValueChanges(value) {
      this.index++
    },
  },
}
</script>

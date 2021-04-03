<template>
  <loading-view :loading="loading">
    <custom-update-header
      class="mb-3"
      :resource-name="resourceName"
      :resource-id="resourceId"
    />

    <form
      v-if="panels"
      @submit="submitViaUpdateResource"
      @change="onUpdateFormStatus"
      autocomplete="off"
      ref="form"
    >
      <form-panel
        v-for="panel in panelsWithFields"
        @update-last-retrieved-at-timestamp="updateLastRetrievedAtTimestamp"
        @field-changed="onUpdateFormStatus"
        @file-upload-started="handleFileUploadStarted"
        @file-upload-finished="handleFileUploadFinished"
        :panel="panel"
        :name="panel.name"
        :key="panel.name"
        :resource-id="resourceId"
        :resource-name="resourceName"
        :fields="panel.fields"
        mode="form"
        class="mb-8"
        :validation-errors="validationErrors"
        :via-resource="viaResource"
        :via-resource-id="viaResourceId"
        :via-relationship="viaRelationship"
      />

      <!-- Update Button -->
      <div class="flex items-center">
        <cancel-button @click="$router.back()" />

        <progress-button
          class="mr-3"
          dusk="update-and-continue-editing-button"
          @click.native="submitViaUpdateResourceAndContinueEditing"
          :disabled="isWorking"
          :processing="wasSubmittedViaUpdateResourceAndContinueEditing"
        >
          {{ __('Update & Continue Editing') }}
        </progress-button>

        <progress-button
          dusk="update-button"
          type="submit"
          :disabled="isWorking"
          :processing="wasSubmittedViaUpdateResource"
        >
          {{ updateButtonLabel }}
        </progress-button>
      </div>
    </form>
  </loading-view>
</template>

<script>
import {
  mapProps,
  Errors,
  InteractsWithResourceInformation,
  PreventsFormAbandonment,
} from 'laravel-nova'
import HandlesUploads from '@/mixins/HandlesUploads'

export default {
  mixins: [
    InteractsWithResourceInformation,
    HandlesUploads,
    PreventsFormAbandonment,
  ],

  metaInfo() {
    if (this.resourceInformation && this.title) {
      return {
        title: this.__('Update :resource: :title', {
          resource: this.resourceInformation.singularLabel,
          title: this.title,
        }),
      }
    }
  },

  props: mapProps([
    'resourceName',
    'resourceId',
    'viaResource',
    'viaResourceId',
    'viaRelationship',
  ]),

  data: () => ({
    relationResponse: null,
    loading: true,
    submittedViaUpdateResourceAndContinueEditing: false,
    submittedViaUpdateResource: false,
    title: null,
    fields: [],
    panels: [],
    validationErrors: new Errors(),
    lastRetrievedAt: null,
  }),

  async created() {
    if (Nova.missingResource(this.resourceName))
      return this.$router.push({ name: '404' })

    // If this update is via a relation index, then let's grab the field
    // and use the label for that as the one we use for the title and buttons
    if (this.isRelation) {
      const { data } = await Nova.request(
        `/nova-api/${this.viaResource}/field/${this.viaRelationship}`
      )
      this.relationResponse = data
    }

    this.getFields()
    this.updateLastRetrievedAtTimestamp()
  },

  watch: {
    $route(to, from) {
      if (
        from.params.resourceName === to.params.resourceName &&
        from.params.resourceId !== to.params.resourceId
      ) {
        this.getFields()
      }
    },
  },

  methods: {
    /**
     * Get the available fields for the resource.
     */
    async getFields() {
      this.loading = true

      this.panels = []
      this.fields = []

      const {
        data: { title, panels, fields },
      } = await Nova.request()
        .get(
          `/nova-api/${this.resourceName}/${this.resourceId}/update-fields`,
          {
            params: {
              editing: true,
              editMode: 'update',
              viaResource: this.viaResource,
              viaResourceId: this.viaResourceId,
              viaRelationship: this.viaRelationship,
            },
          }
        )
        .catch(error => {
          if (error.response.status == 404) {
            this.$router.push({ name: '404' })
            return
          }
        })

      this.title = title
      this.panels = panels
      this.fields = fields
      this.loading = false

      Nova.$emit('resource-loaded')
    },

    async submitViaUpdateResource(e) {
      e.preventDefault()
      this.submittedViaUpdateResource = true
      this.submittedViaUpdateResourceAndContinueEditing = false
      this.canLeave = true
      await this.updateResource()
    },

    async submitViaUpdateResourceAndContinueEditing() {
      this.submittedViaUpdateResourceAndContinueEditing = true
      this.submittedViaUpdateResource = false
      this.canLeave = true
      await this.updateResource()
    },

    /**
     * Update the resource using the provided data.
     */
    async updateResource() {
      this.isWorking = true

      if (this.$refs.form.reportValidity()) {
        try {
          const {
            data: { redirect },
          } = await this.updateRequest()

          Nova.success(
            this.__('The :resource was updated!', {
              resource: this.resourceInformation.singularLabel.toLowerCase(),
            })
          )

          await this.updateLastRetrievedAtTimestamp()

          if (this.submittedViaUpdateResource) {
            this.$router.push({ path: redirect }, () => {
              window.scrollTo(0, 0)
            })
          } else {
            // Reset the form by refetching the fields
            this.getFields()
            this.validationErrors = new Errors()
            this.submittedViaUpdateResource = false
            this.submittedViaUpdateResourceAndContinueEditing = false
            this.isWorking = false

            return
          }
        } catch (error) {
          window.scrollTo(0, 0)

          this.submittedViaUpdateResource = false
          this.submittedViaUpdateResourceAndContinueEditing = false

          if (this.resourceInformation.preventFormAbandonment) {
            this.canLeave = false
          }

          if (error.response.status == 422) {
            this.validationErrors = new Errors(error.response.data.errors)
            Nova.error(this.__('There was a problem submitting the form.'))
          }

          if (error.response.status == 409) {
            Nova.error(
              this.__(
                'Another user has updated this resource since this page was loaded. Please refresh the page and try again.'
              )
            )
          }
        }
      }

      this.submittedViaUpdateResource = false
      this.submittedViaUpdateResourceAndContinueEditing = false
      this.isWorking = false
    },

    /**
     * Send an update request for this resource
     */
    updateRequest() {
      return Nova.request().post(
        `/nova-api/${this.resourceName}/${this.resourceId}`,
        this.updateResourceFormData,
        {
          params: {
            viaResource: this.viaResource,
            viaResourceId: this.viaResourceId,
            viaRelationship: this.viaRelationship,
            editing: true,
            editMode: 'update',
          },
        }
      )
    },

    /**
     * Update the last retrieved at timestamp to the current UNIX timestamp.
     */
    updateLastRetrievedAtTimestamp() {
      this.lastRetrievedAt = Math.floor(new Date().getTime() / 1000)
    },

    /**
     * Prevent accidental abandonment only if form was changed.
     */
    onUpdateFormStatus() {
      if (this.resourceInformation.preventFormAbandonment) {
        this.updateFormStatus()
      }
    },
  },

  computed: {
    wasSubmittedViaUpdateResourceAndContinueEditing() {
      return this.isWorking && this.submittedViaUpdateResourceAndContinueEditing
    },

    wasSubmittedViaUpdateResource() {
      return this.isWorking && this.submittedViaUpdateResource
    },

    /**
     * Create the form data for creating the resource.
     */
    updateResourceFormData() {
      return _.tap(new FormData(), formData => {
        _(this.fields).each(field => {
          field.fill(formData)
        })

        formData.append('_method', 'PUT')
        formData.append('_retrieved_at', this.lastRetrievedAt)
      })
    },

    singularName() {
      if (this.relationResponse) {
        return this.relationResponse.singularLabel
      }

      return this.resourceInformation.singularLabel
    },

    updateButtonLabel() {
      return this.resourceInformation.updateButtonLabel
    },

    isRelation() {
      return Boolean(this.viaResourceId && this.viaRelationship)
    },

    panelsWithFields() {
      return _.map(this.panels, panel => {
        return {
          ...panel,
          fields: _.filter(this.fields, field => field.panel == panel.name),
        }
      })
    },
  },
}
</script>

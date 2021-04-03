<template>
  <loading-view :loading="loading">
    <custom-update-attached-header
      class="mb-3"
      :resource-name="resourceName"
      :resource-id="resourceId"
    />

    <heading class="mb-3" v-if="relatedResourceLabel && title">
      {{
        __('Update attached :resource: :title', {
          resource: relatedResourceLabel,
          title: title,
        })
      }}
    </heading>

    <form
      v-if="field"
      @submit.prevent="updateAttachedResource"
      @change="onUpdateFormStatus"
      autocomplete="off"
    >
      <card class="overflow-hidden mb-8">
        <!-- Related Resource -->
        <div
          v-if="viaResourceField"
          dusk="via-resource-field"
          class="flex border-b border-40"
        >
          <div class="w-1/5 px-8 py-6">
            <label
              :for="viaResourceField.name"
              class="inline-block text-80 pt-2 leading-tight"
            >
              {{ viaResourceField.name }}
            </label>
          </div>
          <div class="py-6 px-8 w-1/2">
            <span class="inline-block font-bold text-80 pt-2">
              {{ viaResourceField.display }}
            </span>
          </div>
        </div>
        <default-field
          :field="field"
          :errors="validationErrors"
          :show-help-text="field.helpText != null"
        >
          <template slot="field">
            <select-control
              class="form-control form-select w-full"
              dusk="attachable-select"
              :class="{
                'border-danger': validationErrors.has(field.attribute),
              }"
              :data-testid="`${field.resourceName}-select`"
              @change="selectResourceFromSelectControl"
              disabled
              :options="availableResources"
              :label="'display'"
              :selected="selectedResourceId"
            >
              <option value="" disabled selected>
                {{ __('Choose :field', { field: field.name }) }}
              </option>
            </select-control>
          </template>
        </default-field>

        <!-- Pivot Fields -->
        <div v-for="field in fields">
          <component
            :is="'form-' + field.component"
            :resource-name="resourceName"
            :resource-id="resourceId"
            :field="field"
            :errors="validationErrors"
            :related-resource-name="relatedResourceName"
            :related-resource-id="relatedResourceId"
            :via-resource="viaResource"
            :via-resource-id="viaResourceId"
            :via-relationship="viaRelationship"
            :show-help-text="field.helpText != null"
          />
        </div>
      </card>
      <!-- Attach Button -->
      <div class="flex items-center">
        <cancel-button @click="$router.back()" />

        <progress-button
          class="mr-3"
          dusk="update-and-continue-editing-button"
          @click.native="updateAndContinueEditing"
          :disabled="isWorking"
          :processing="submittedViaUpdateAndContinueEditing"
        >
          {{ __('Update & Continue Editing') }}
        </progress-button>

        <progress-button
          dusk="update-button"
          type="submit"
          :disabled="isWorking"
          :processing="submittedViaUpdateAttachedResource"
        >
          {{
            __('Update :resource', {
              resource: relatedResourceLabel,
            })
          }}
        </progress-button>
      </div>
    </form>
  </loading-view>
</template>

<script>
import _ from 'lodash'
import {
  PerformsSearches,
  TogglesTrashed,
  Errors,
  PreventsFormAbandonment,
} from 'laravel-nova'

export default {
  mixins: [PerformsSearches, TogglesTrashed, PreventsFormAbandonment],

  metaInfo() {
    if (this.relatedResourceLabel && this.title) {
      return {
        title: this.__('Update attached :resource: :title', {
          resource: this.relatedResourceLabel,
          title: this.title,
        }),
      }
    }
  },

  props: {
    resourceName: {
      type: String,
      required: true,
    },
    resourceId: {
      required: true,
    },
    relatedResourceName: {
      type: String,
      required: true,
    },
    relatedResourceId: {
      required: true,
    },
    viaResource: {
      default: '',
    },
    viaResourceId: {
      default: '',
    },
    viaRelationship: {
      default: '',
    },
    polymorphic: {
      default: false,
    },
  },

  data: () => ({
    loading: true,
    submittedViaUpdateAndContinueEditing: false,
    submittedViaUpdateAttachedResource: false,
    viaResourceField: null,
    field: null,
    softDeletes: false,
    fields: [],
    validationErrors: new Errors(),
    selectedResource: null,
    selectedResourceId: null,
    lastRetrievedAt: null,
    title: null,
  }),

  created() {
    if (Nova.missingResource(this.resourceName))
      return this.$router.push({ name: '404' })
  },

  /**
   * Mount the component.
   */
  mounted() {
    this.initializeComponent()
  },

  methods: {
    /**
     * Initialize the component's data.
     */
    async initializeComponent() {
      this.softDeletes = false
      this.disableWithTrashed()
      this.clearSelection()
      await this.getField()
      await this.getPivotFields()
      await this.getAvailableResources()
      this.resetErrors()

      this.selectedResourceId = this.relatedResourceId

      this.selectInitialResource()

      this.updateLastRetrievedAtTimestamp()
    },

    /**
     * Get the many-to-many relationship field.
     */
    async getField() {
      this.field = null

      const { data: field } = await Nova.request().get(
        '/nova-api/' + this.resourceName + '/field/' + this.viaRelationship,
        {
          params: {
            relatable: true,
          },
        }
      )

      this.field = field

      if (this.field.searchable) {
        this.determineIfSoftDeletes()
      }

      this.loading = false
    },

    /**
     * Get all of the available pivot fields for the relationship.
     */
    async getPivotFields() {
      this.fields = []

      const {
        data: { title, fields },
      } = await Nova.request()
        .get(
          `/nova-api/${this.resourceName}/${this.resourceId}/update-pivot-fields/${this.relatedResourceName}/${this.relatedResourceId}`,
          {
            params: {
              editing: true,
              editMode: 'update-attached',
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
      this.fields = fields

      _.each(this.fields, field => {
        if (field) {
          field.fill = () => ''
        }
      })
    },

    resetErrors() {
      this.validationErrors = new Errors()
    },

    /**
     * Get all of the available resources for the current search / trashed state.
     */
    async getAvailableResources(search = '') {
      try {
        const response = await Nova.request().get(
          `/nova-api/${this.resourceName}/${this.resourceId}/attachable/${this.relatedResourceName}`,
          {
            params: {
              search,
              current: this.relatedResourceId,
              first: true,
              withTrashed: this.withTrashed,
            },
          }
        )

        this.viaResourceField = response.data.viaResource
        this.availableResources = response.data.resources
        this.withTrashed = response.data.withTrashed
        this.softDeletes = response.data.softDeletes
      } catch (error) {}
    },

    /**
     * Determine if the related resource is soft deleting.
     */
    determineIfSoftDeletes() {
      Nova.request()
        .get('/nova-api/' + this.relatedResourceName + '/soft-deletes')
        .then(response => {
          this.softDeletes = response.data.softDeletes
        })
    },

    /**
     * Update the attached resource.
     */
    async updateAttachedResource() {
      this.submittedViaUpdateAttachedResource = true

      try {
        await this.updateRequest()

        this.submittedViaUpdateAttachedResource = false
        this.canLeave = true

        Nova.success(this.__('The resource was updated!'))

        this.$router.push({
          name: 'detail',
          params: {
            resourceName: this.resourceName,
            resourceId: this.resourceId,
          },
        })
      } catch (error) {
        window.scrollTo(0, 0)

        this.submittedViaUpdateAttachedResource = false
        if (
          this.resourceInformation &&
          this.resourceInformation.preventFormAbandonment
        ) {
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
    },

    /**
     * Update the resource and reset the form
     */
    async updateAndContinueEditing() {
      this.submittedViaUpdateAndContinueEditing = true

      try {
        await this.updateRequest()

        this.submittedViaUpdateAndContinueEditing = false

        Nova.success(this.__('The resource was updated!'))

        // Reset the form by refetching the fields
        this.initializeComponent()
      } catch (error) {
        this.submittedViaUpdateAndContinueEditing = false

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
    },

    /**
     * Send an update request for this resource
     */
    updateRequest() {
      return Nova.request().post(
        `/nova-api/${this.resourceName}/${this.resourceId}/update-attached/${this.relatedResourceName}/${this.relatedResourceId}`,
        this.updateAttachmentFormData,
        {
          params: {
            editing: true,
            editMode: 'update-attached',
          },
        }
      )
    },

    /**
     * Select a resource using the <select> control
     */
    selectResourceFromSelectControl(e) {
      this.selectedResourceId = e.target.value
      this.selectInitialResource()

      if (this.field) {
        Nova.$emit(this.field.attribute + '-change', this.selectedResourceId)
      }
    },

    /**
     * Toggle the trashed state of the search
     */
    toggleWithTrashed() {
      this.withTrashed = !this.withTrashed

      // Reload the data if the component doesn't support searching
      if (!this.isSearchable) {
        this.getAvailableResources()
      }
    },

    /**
     * Select the initial selected resource
     */
    selectInitialResource() {
      this.selectedResource = _.find(
        this.availableResources,
        r => r.value == this.selectedResourceId
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
      if (
        this.resourceInformation &&
        this.resourceInformation.preventFormAbandonment
      ) {
        this.updateFormStatus()
      }
    },
  },

  computed: {
    /**
     * Get the attachment endpoint for the relationship type.
     */
    attachmentEndpoint() {
      return this.polymorphic
        ? '/nova-api/' +
            this.resourceName +
            '/' +
            this.resourceId +
            '/attach-morphed/' +
            this.relatedResourceName
        : '/nova-api/' +
            this.resourceName +
            '/' +
            this.resourceId +
            '/attach/' +
            this.relatedResourceName
    },

    /*
     * Get the form data for the resource attachment update.
     */
    updateAttachmentFormData() {
      return _.tap(new FormData(), formData => {
        _.each(this.fields, field => {
          field.fill(formData)
        })

        formData.append('viaRelationship', this.viaRelationship)

        if (!this.selectedResource) {
          formData.append(this.relatedResourceName, '')
        } else {
          formData.append(this.relatedResourceName, this.selectedResource.value)
        }

        formData.append(this.relatedResourceName + '_trashed', this.withTrashed)
        formData.append('_retrieved_at', this.lastRetrievedAt)
      })
    },

    /**
     * Get the label for the related resource.
     */
    relatedResourceLabel() {
      if (this.field) {
        return this.field.singularLabel
      }
    },

    /**
     * Determine if the related resources is searchable
     */
    isSearchable() {
      return this.field.searchable
    },

    /**
     * Determine if the form is being processed
     */
    isWorking() {
      return (
        this.submittedViaUpdateAttachedResource ||
        this.submittedViaUpdateAndContinueEditing
      )
    },
  },
}
</script>

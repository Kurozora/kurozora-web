<template>
  <loading-view :loading="loading">
    <custom-attach-header
      class="mb-3"
      :resource-name="resourceName"
      :resource-id="resourceId"
    />

    <heading class="mb-3">{{
      __('Attach :resource', { resource: relatedResourceLabel })
    }}</heading>

    <form
      v-if="field"
      @submit.prevent="attachResource"
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
            <search-input
              v-if="field.searchable"
              :data-testid="`${field.resourceName}-search-input`"
              @input="performSearch"
              @clear="clearSelection"
              @selected="selectResource"
              :debounce="field.debounce"
              :value="selectedResource"
              :data="availableResources"
              trackBy="value"
            >
              <div
                slot="default"
                v-if="selectedResource"
                class="flex items-center"
              >
                <div v-if="selectedResource.avatar" class="mr-3">
                  <img
                    :src="selectedResource.avatar"
                    class="w-8 h-8 rounded-full block"
                  />
                </div>

                {{ selectedResource.display }}
              </div>

              <div
                slot="option"
                slot-scope="{ option, selected }"
                class="flex items-center"
              >
                <div v-if="option.avatar" class="mr-3">
                  <img
                    :src="option.avatar"
                    class="w-8 h-8 rounded-full block"
                  />
                </div>

                <div>
                  <div
                    class="text-sm font-semibold leading-5 text-90"
                    :class="{ 'text-white': selected }"
                  >
                    {{ option.display }}
                  </div>

                  <div
                    v-if="field.withSubtitles"
                    class="mt-1 text-xs font-semibold leading-5 text-80"
                    :class="{ 'text-white': selected }"
                  >
                    <span v-if="option.subtitle">{{ option.subtitle }}</span>
                    <span v-else>{{ __('No additional information...') }}</span>
                  </div>
                </div>
              </div>
            </search-input>

            <select-control
              v-else
              dusk="attachable-select"
              class="form-control form-select w-full"
              :class="{
                'border-danger': validationErrors.has(field.attribute),
              }"
              :data-testid="`${field.resourceName}-select`"
              @change="selectResourceFromSelectControl"
              :options="availableResources"
              :label="'display'"
              :selected="selectedResourceId"
            >
              <option value="" disabled selected>
                {{
                  __('Choose :resource', {
                    resource: relatedResourceLabel,
                  })
                }}
              </option>
            </select-control>

            <!-- Trashed State -->
            <div v-if="softDeletes" class="mt-3">
              <checkbox-with-label
                :dusk="field.resourceName + '-with-trashed-checkbox'"
                :checked="withTrashed"
                @input="toggleWithTrashed"
              >
                {{ __('With Trashed') }}
              </checkbox-with-label>
            </div>
          </template>
        </default-field>

        <!-- Pivot Fields -->
        <div v-for="field in fields">
          <component
            :is="'form-' + field.component"
            :resource-name="resourceName"
            :field="field"
            :errors="validationErrors"
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
          dusk="attach-and-attach-another-button"
          @click.native="attachAndAttachAnother"
          :disabled="isWorking"
          :processing="submittedViaAttachAndAttachAnother"
        >
          {{ __('Attach & Attach Another') }}
        </progress-button>

        <progress-button
          dusk="attach-button"
          type="submit"
          :disabled="isWorking"
          :processing="submittedViaAttachResource"
        >
          {{
            __('Attach :resource', {
              resource: relatedResourceLabel,
            })
          }}
        </progress-button>
      </div>
    </form>
  </loading-view>
</template>

<script>
import {
  PerformsSearches,
  TogglesTrashed,
  Errors,
  PreventsFormAbandonment,
} from 'laravel-nova'

export default {
  mixins: [PerformsSearches, TogglesTrashed, PreventsFormAbandonment],

  metaInfo() {
    if (this.relatedResourceLabel) {
      return {
        title: this.__('Attach :resource', {
          resource: this.relatedResourceLabel,
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
    submittedViaAttachAndAttachAnother: false,
    submittedViaAttachResource: false,
    viaResourceField: null,
    field: null,
    softDeletes: false,
    fields: [],
    validationErrors: new Errors(),
    selectedResource: null,
    selectedResourceId: null,
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
    initializeComponent() {
      this.softDeletes = false
      this.disableWithTrashed()
      this.clearSelection()
      this.getField()
      this.getPivotFields()
      this.resetErrors()
    },

    /**
     * Get the many-to-many relationship field.
     */
    getField() {
      this.field = null

      Nova.request()
        .get(
          '/nova-api/' + this.resourceName + '/field/' + this.viaRelationship,
          {
            params: {
              relatable: true,
            },
          }
        )
        .then(({ data }) => {
          this.field = data
          this.field.searchable
            ? this.determineIfSoftDeletes()
            : this.getAvailableResources()
          this.loading = false
        })
    },

    /**
     * Get all of the available pivot fields for the relationship.
     */
    getPivotFields() {
      this.fields = []

      Nova.request()
        .get(
          '/nova-api/' +
            this.resourceName +
            '/' +
            this.resourceId +
            '/creation-pivot-fields/' +
            this.relatedResourceName,
          {
            params: {
              editing: true,
              editMode: 'attach',
              viaRelationship: this.viaRelationship,
            },
          }
        )
        .then(({ data }) => {
          this.fields = data

          _.each(this.fields, field => {
            field.fill = () => ''
          })
        })
    },

    resetErrors() {
      this.validationErrors = new Errors()
    },

    /**
     * Get all of the available resources for the current search / trashed state.
     */
    getAvailableResources(search = '') {
      Nova.request()
        .get(
          `/nova-api/${this.resourceName}/${this.resourceId}/attachable/${this.relatedResourceName}`,
          {
            params: {
              search,
              current: this.selectedResourceId,
              withTrashed: this.withTrashed,
            },
          }
        )
        .then(response => {
          this.viaResourceField = response.data.viaResource
          this.availableResources = response.data.resources
          this.withTrashed = response.data.withTrashed
          this.softDeletes = response.data.softDeletes
        })
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
     * Attach the selected resource.
     */
    async attachResource() {
      this.submittedViaAttachResource = true

      try {
        await this.attachRequest()

        this.submittedViaAttachResource = false
        this.canLeave = true

        this.$router.push({
          name: 'detail',
          params: {
            resourceName: this.resourceName,
            resourceId: this.resourceId,
          },
        })
      } catch (error) {
        window.scrollTo(0, 0)

        this.submittedViaAttachResource = false
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
      }
    },

    /**
     * Attach a new resource and reset the form
     */
    async attachAndAttachAnother() {
      this.submittedViaAttachAndAttachAnother = true

      try {
        await this.attachRequest()

        this.submittedViaAttachAndAttachAnother = false

        // Reset the form by refetching the fields
        this.initializeComponent()
      } catch (error) {
        this.submittedViaAttachAndAttachAnother = false

        if (error.response.status == 422) {
          this.validationErrors = new Errors(error.response.data.errors)
          Nova.error(this.__('There was a problem submitting the form.'))
        }
      }
    },

    /**
     * Send an attach request for this resource
     */
    attachRequest() {
      return Nova.request().post(
        this.attachmentEndpoint,
        this.attachmentFormData,
        {
          params: {
            editing: true,
            editMode: 'attach',
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
     * Select the initial selected resource
     */
    selectInitialResource() {
      this.selectedResource = _.find(
        this.availableResources,
        r => r.value == this.selectedResourceId
      )
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

    /**
     * Get the form data for the resource attachment.
     */
    attachmentFormData() {
      return _.tap(new FormData(), formData => {
        _.each(this.fields, field => {
          field.fill(formData)
        })

        if (!this.selectedResource) {
          formData.append(this.relatedResourceName, '')
        } else {
          formData.append(this.relatedResourceName, this.selectedResource.value)
        }

        formData.append(this.relatedResourceName + '_trashed', this.withTrashed)
        formData.append('viaRelationship', this.viaRelationship)
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
        this.submittedViaAttachResource ||
        this.submittedViaAttachAndAttachAnother
      )
    },

    /**
     * Return the heading for the view
     */
    headingTitle() {
      return this.__('Attach :resource', {
        resource: this.relatedResourceLabel,
      })
    },
  },
}
</script>

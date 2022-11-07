<template>
  <DefaultField
    :field="currentField"
    :errors="errors"
    :show-help-text="showHelpText"
    :full-width-content="fullWidthContent"
  >
    <template #field>
      <div class="space-y-4">
        <div class="flex items-center space-x-2">
          <SearchSearchInput
            ref="searchable"
            :data-testid="`${field.uniqueKey}-search-filter`"
            @input="performSearch"
            :error="hasError"
            :debounce="field.debounce"
            :options="tags"
            @selected="selectResource"
            trackBy="value"
            :disabled="currentlyIsReadonly"
            :loading="loading"
            class="w-full"
          >
            <template #option="{ selected, option }">
              <SearchInputResult
                :option="option"
                :selected="selected"
                :with-subtitles="field.withSubtitles"
              />
            </template>
          </SearchSearchInput>

          <CreateRelationButton
            v-if="field.showCreateRelationButton"
            v-tooltip="
              __('Create :resource', { resource: field.singularLabel })
            "
            @click="openRelationModal"
            :dusk="`${field.attribute}-inline-create`"
            tabindex="0"
          />
        </div>

        <div v-if="value.length > 0">
          <TagList
            v-if="field.style === 'list'"
            :tags="value"
            @tag-removed="i => removeResource(i)"
            :resource-name="field.resourceName"
            :editable="!currentlyIsReadonly"
            :with-preview="field.withPreview"
          />

          <TagGroup
            v-if="field.style === 'group'"
            :tags="value"
            @tag-removed="i => removeResource(i)"
            :resource-name="field.resourceName"
            :editable="!currentlyIsReadonly"
            :with-preview="field.withPreview"
          />
        </div>
      </div>

      <CreateRelationModal
        :resource-name="field.resourceName"
        :show="field.showCreateRelationButton && relationModalOpen"
        :size="field.modalSize"
        @set-resource="handleSetResource"
        @create-cancelled="relationModalOpen = false"
      />
    </template>
  </DefaultField>
</template>

<script>
import {
  DependentFormField,
  PerformsSearches,
  HandlesValidationErrors,
  mapProps,
} from '@/mixins'
import { minimum } from '@/util'
import { search as hasManySearch } from '@/storage/HasManyFieldStorage'
import TagList from '../../components/Tags/TagList'
import SearchInputResult from '../../components/Inputs/SearchInputResult'
import PreviewResourceModal from '../../components/Modals/PreviewResourceModal'

export default {
  components: { PreviewResourceModal, SearchInputResult, TagList },
  mixins: [DependentFormField, PerformsSearches, HandlesValidationErrors],

  props: {
    ...mapProps(['resourceId']),
  },

  data: () => ({
    relationModalOpen: false,
    search: '',
    value: [],
    tags: [],
    loading: false,
  }),

  methods: {
    fill(formData) {
      this.fillIfVisible(
        formData,
        this.currentField.attribute,
        this.value.length > 0 ? JSON.stringify(this.value) : ''
      )
    },

    async getAvailableResources(search) {
      this.loading = true
      const { resources } = await minimum(
        hasManySearch(search, this.currentField.resourceName),
        250
      )

      this.loading = false
      this.tags = resources
    },

    selectResource(resource) {
      const found = this.value.filter(t => t.display === resource.display)

      if (found.length === 0) {
        this.value.push(resource)
      }
    },

    handleSetResource() {
      this.getAvailableResources().then(() => {
        this.closeRelationModal()
      })
    },

    removeResource(index) {
      this.value.splice(index, 1)
    },

    openRelationModal() {
      this.relationModalOpen = true
    },

    closeRelationModal() {
      this.relationModalOpen = false
    },
  },
}
</script>

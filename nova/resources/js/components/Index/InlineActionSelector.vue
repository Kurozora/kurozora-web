<template>
  <span>
    <select
      ref="selectBox"
      v-if="actions.length > 1"
      class="rounded-sm select-box-sm mr-2 h-6 text-xs appearance-none bg-40 pl-2 pr-6 active:outline-none active:shadow-outline focus:outline-none focus:shadow-outline"
      style="max-width: 90px"
      @change="handleSelectionChange"
      dusk="inline-action-select"
    >
      <option disabled selected>{{ __('Actions') }}</option>
      <option
        v-for="action in actions"
        :key="action.uriKey"
        :value="action.uriKey"
      >
        {{ action.name }}
      </option>
    </select>

    <button
      v-else
      v-for="action in actions"
      :key="action.uriKey"
      @click="executeSingleAction(action)"
      class="btn btn-xs mr-1"
      :class="action.class"
      dusk="run-inline-action-button"
      :data-testid="action.uriKey"
    >
      {{ action.name }}
    </button>

    <!-- Action Confirmation Modal -->
    <portal to="modals">
      <component
        v-if="confirmActionModalOpened"
        class="text-left"
        :is="selectedAction.component"
        :working="working"
        :selected-resources="selectedResources"
        :resource-name="resourceName"
        :action="selectedAction"
        :endpoint="actionsEndpoint"
        :errors="errors"
        @confirm="executeAction"
        @close="closeConfirmationModal"
      />

      <component
        :is="actionResponseData.modal"
        @close="closeActionResponseModal"
        v-if="showActionResponseModal"
        :data="actionResponseData"
      />
    </portal>
  </span>
</template>

<script>
import HandlesActions from '@/mixins/HandlesActions'

export default {
  mixins: [HandlesActions],

  props: {
    resource: {},
    actions: {},
  },

  data: () => ({
    showActionResponseModal: false,
    actionResponseData: {},
  }),

  methods: {
    handleSelectionChange(event) {
      this.selectedActionKey = event.target.value
      this.determineActionStrategy()
      this.$refs.selectBox.selectedIndex = 0
    },

    executeSingleAction(action) {
      this.selectedActionKey = action.uriKey
      this.determineActionStrategy()
    },
  },

  computed: {
    selectedResources() {
      return [this.resource.id.value]
    },
  },
}
</script>

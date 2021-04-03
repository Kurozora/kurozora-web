export default {
  data: () => ({
    actions: [],
    pivotActions: null,
  }),

  computed: {
    /**
     * Determine whether there are any standalone actions.
     */
    haveStandaloneActions() {
      return this.standaloneActions.length > 0
    },

    /**
     * Filter actions to non-standalone actions.
     */
    nonStandaloneActions() {
      return _.filter(this.allActions, a => a.standalone == false)
    },

    /**
     * Filter actions to only standalone actions.
     */
    standaloneActions() {
      return _.filter(this.allActions, a => a.standalone == true)
    },

    /**
     * Return the available actions depending on if any resources are selected.
     */
    availableActions() {
      return this.selectedResources.length > 0
        ? this.nonStandaloneActions
        : this.standaloneActions
    },

    /**
     * Determine if the resource has any pivot actions available.
     */
    hasPivotActions() {
      return this.pivotActions && this.pivotActions.actions.length > 0
    },

    /**
     * Get the name of the pivot model for the resource.
     */
    pivotName() {
      return this.pivotActions ? this.pivotActions.name : ''
    },

    /**
     * Determine if the resource has any actions available.
     */
    actionsAreAvailable() {
      return this.allActions.length > 0
    },

    /**
     * Get all of the actions available to the resource.
     */
    allActions() {
      return this.hasPivotActions
        ? this.actions.concat(this.pivotActions.actions)
        : this.actions
    },

    /**
     * Get the selected resources for the action selector.
     */
    selectedResourcesForActionSelector() {
      return this.selectAllMatchingChecked ? 'all' : this.selectedResourceIds
    },
  },
}

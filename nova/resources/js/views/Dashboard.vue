<template>
  <LoadingView
    :loading="loading"
    :dusk="'dashboard-' + this.name"
    class="space-y-3"
  >
    <Head :title="label" />

    <div
      v-if="(label && !isHelpCard) || showRefreshButton"
      class="flex items-center"
    >
      <Heading v-if="label && !isHelpCard">
        {{ __(label) }}
      </Heading>

      <button
        @click.stop="refreshDashboard"
        type="button"
        class="ml-1 hover:opacity-50 active:ring"
        v-if="showRefreshButton"
        tabindex="0"
      >
        <Icon
          class="text-gray-500 dark:text-gray-400"
          :solid="true"
          type="refresh"
          width="14"
          v-tooltip="__('Refresh')"
        />
      </button>
    </div>

    <div v-if="shouldShowCards">
      <Cards v-if="cards.length > 0" :cards="cards" />
    </div>
  </LoadingView>
</template>
<script>
import { minimum } from '@/util'

export default {
  props: {
    name: {
      type: String,
      required: false,
      default: 'main',
    },
  },

  data: () => ({
    loading: true,
    label: '',
    cards: [],
    showRefreshButton: false,
    isHelpCard: false,
  }),

  created() {
    this.fetchDashboard()
  },

  methods: {
    async fetchDashboard() {
      this.loading = true

      try {
        const {
          data: { label, cards, showRefreshButton, isHelpCard },
        } = await minimum(
          Nova.request().get(this.dashboardEndpoint, {
            params: this.extraCardParams,
          }),
          200
        )

        this.loading = false
        this.label = label
        this.cards = cards
        this.showRefreshButton = showRefreshButton
        this.isHelpCard = isHelpCard
      } catch (error) {
        if (error.response.status == 401) {
          return Nova.redirectToLogin()
        }

        Nova.visit('/404')
      }
    },

    refreshDashboard() {
      Nova.$emit('metric-refresh')
    },
  },

  computed: {
    /**
     * Get the endpoint for this dashboard.
     */
    dashboardEndpoint() {
      return `/nova-api/dashboards/${this.name}`
    },

    /**
     * Determine whether we have cards to show on the Dashboard
     */
    shouldShowCards() {
      return this.cards.length > 0
    },

    /**
     * Get the extra card params to pass to the endpoint.
     */
    extraCardParams() {
      return null
    },
  },
}
</script>

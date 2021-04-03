<template>
  <div :dusk="'dashboard-' + this.name">
    <custom-dashboard-header class="mb-3" :dashboard-name="name" />

    <heading v-if="cards.length > 1" class="mb-6">{{
      __('Dashboard')
    }}</heading>

    <div v-if="shouldShowCards">
      <cards v-if="smallCards.length > 0" :cards="smallCards" class="mb-3" />
      <cards v-if="largeCards.length > 0" :cards="largeCards" size="large" />
    </div>
  </div>
</template>

<script>
import { CardSizes } from 'laravel-nova'

export default {
  metaInfo() {
    return {
      title: `${this.label}`,
    }
  },

  data: () => ({ label: '', cards: '' }),

  props: {
    name: {
      type: String,
      required: false,
      default: 'main',
    },
  },

  watch: {
    name() {
      this.fetchDashboard()
    },
  },

  created() {
    this.fetchDashboard()
  },

  methods: {
    async fetchDashboard() {
      const {
        data: { label, cards },
      } = await Nova.request()
        .get(this.dashboardEndpoint, {
          params: this.extraCardParams,
        })
        .catch(e => {
          this.$router.push({ name: '404' })
        })

      this.label = label
      this.cards = cards
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
     * Return the small cards used for the Dashboard
     */
    smallCards() {
      return _.filter(this.cards, c => CardSizes.indexOf(c.width) !== -1)
    },

    /**
     * Return the full-width cards used for the Dashboard
     */
    largeCards() {
      return _.filter(this.cards, c => c.width == 'full')
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

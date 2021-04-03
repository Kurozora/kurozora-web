<template>
  <loading-card :loading="loading" class="px-6 py-4">
    <div class="flex mb-4">
      <h3 class="mr-3 text-base text-80 font-bold">{{ title }}</h3>

      <div v-if="helpText" class="absolute pin-r pin-b p-2 z-20">
        <tooltip trigger="click" placement="top-start">
          <icon
            type="help"
            viewBox="0 0 17 17"
            height="16"
            width="16"
            class="cursor-pointer text-60 -mb-1"
          />

          <tooltip-content
            slot="content"
            v-html="helpText"
            :max-width="helpWidth"
          />
        </tooltip>
      </div>

      <select
        v-if="ranges.length > 0"
        @change="handleChange"
        class="select-box-sm ml-auto min-w-24 h-6 text-xs appearance-none bg-40 pl-2 pr-6 active:outline-none active:shadow-outline focus:outline-none focus:shadow-outline"
      >
        <option
          v-for="option in ranges"
          :key="option.value"
          :value="option.value"
          :selected="selectedRangeKey == option.value"
        >
          {{ option.label }}
        </option>
      </select>
    </div>

    <p class="flex items-center text-4xl mb-4">
      {{ formattedValue }}
      <span v-if="suffix" class="ml-2 text-sm font-bold text-80">{{
        formattedSuffix
      }}</span>
    </p>

    <div
      ref="chart"
      class="absolute pin rounded-b-lg ct-chart"
      style="top: 60%"
    />
  </loading-card>
</template>

<script>
import _ from 'lodash'
import Chartist from 'chartist'
import 'chartist-plugin-tooltips'
import 'chartist/dist/chartist.min.css'
import { SingularOrPlural } from 'laravel-nova'
import 'chartist-plugin-tooltips/dist/chartist-plugin-tooltip.css'

export default {
  name: 'BaseTrendMetric',

  props: {
    loading: Boolean,
    title: {},
    helpText: {},
    helpWidth: {},
    value: {},
    chartData: {},
    maxWidth: {},
    prefix: '',
    suffix: '',
    suffixInflection: true,
    ranges: { type: Array, default: () => [] },
    selectedRangeKey: [String, Number],
    format: {
      type: String,
      default: '0[.]00a',
    },
  },

  data: () => ({
    chartist: null,
    resizeObserver: null,
  }),

  watch: {
    selectedRangeKey: function (newRange, oldRange) {
      this.renderChart()
    },

    chartData: function (newData, oldData) {
      this.renderChart()
    },
  },

  created() {
    const debouncer = _.debounce(callback => callback(), Nova.config.debounce)

    this.resizeObserver = new ResizeObserver(entries => {
      debouncer(() => {
        this.renderChart()
      })
    })
  },

  mounted() {
    const low = Math.min(...this.chartData)
    const high = Math.max(...this.chartData)

    // Use zero as the graph base if the lowest value is greater than or equal to zero.
    // This avoids the awkward situation where the chart doesn't appear filled in.
    const areaBase = low >= 0 ? 0 : low

    this.chartist = new Chartist.Line(this.$refs.chart, this.chartData, {
      lineSmooth: Chartist.Interpolation.none(),
      fullWidth: true,
      showPoint: true,
      showLine: true,
      showArea: true,
      chartPadding: {
        top: 10,
        right: 0,
        bottom: 0,
        left: 0,
      },
      low,
      high,
      areaBase,
      axisX: {
        showGrid: false,
        showLabel: true,
        offset: 0,
      },
      axisY: {
        showGrid: false,
        showLabel: true,
        offset: 0,
      },
      plugins: [
        Chartist.plugins.tooltip({
          anchorToPoint: true,
          transformTooltipTextFnc: value => {
            let formattedValue = Nova.formatNumber(
              new String(value),
              this.format
            )

            if (this.prefix) {
              return `${this.prefix}${formattedValue}`
            }

            if (this.suffix) {
              const suffix = this.suffixInflection
                ? SingularOrPlural(value, this.suffix)
                : this.suffix

              return `${formattedValue} ${suffix}`
            }

            return `${formattedValue}`
          },
        }),
      ],
    })

    this.resizeObserver.observe(this.$refs.chart)
  },

  beforeDestroy() {
    this.resizeObserver.unobserve(this.$refs.chart)
  },

  methods: {
    renderChart() {
      this.chartist.update(this.chartData)
    },

    handleChange(event) {
      this.$emit('selected', event.target.value)
    },
  },

  computed: {
    isNullValue() {
      return this.value == null
    },

    formattedValue() {
      if (!this.isNullValue) {
        const value = Nova.formatNumber(new String(this.value), this.format)

        return `${this.prefix}${value}`
      }

      return ''
    },

    formattedSuffix() {
      if (this.suffixInflection === false) {
        return this.suffix
      }

      return SingularOrPlural(this.value, this.suffix)
    },
  },
}
</script>

<template>
  <component :is="component" :class="classes">
    <slot />
  </component>
</template>

<script>
const classes = {
  1: 'font-normal text-xl md:text-xl',
  2: 'font-normal md:text-xl',
  3: 'uppercase tracking-wide font-bold text-xs',
  4: 'font-normal md:text-2xl',
}

export default {
  props: {
    level: {
      default: 1,
      type: Number,
    },
  },

  computed: {
    component() {
      return 'h' + this.level
    },
    classes() {
      return classes[this.level]
    },
  },
}
</script>

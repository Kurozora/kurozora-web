export default {
  methods: {
    /**
     * Translate the given key.
     */
    __(key, replace) {
      var translation = window.config.translations[key]
        ? window.config.translations[key]
        : key

      _.forEach(replace, (value, key) => {
        const searches = [
          ':' + key,
          ':' + key.toUpperCase(),
          ':' + key.charAt(0).toUpperCase() + key.slice(1),
        ]

        const replacements = [
          value,
          value.toUpperCase(),
          value.charAt(0).toUpperCase() + value.slice(1),
        ]

        for (var i = searches.length - 1; i >= 0; i--) {
          translation = translation.replace(searches[i], replacements[i])
        }
      })

      return translation
    },
  },
}

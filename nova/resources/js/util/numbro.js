import numbro from 'numbro'
import numbroLanguages from 'numbro/dist/languages.min'

if (window.config.locale) {
  let locale = window.config.locale.replace('_', '-')

  Object.values(numbroLanguages).forEach(language => {
    let name = language.languageTag

    if (locale === name || locale === name.substr(0, 2)) {
      numbro.registerLanguage(language)
    }
  })

  numbro.setLanguage(locale)
}

numbro.setDefaults({
  thousandSeparated: true,
})

export default numbro

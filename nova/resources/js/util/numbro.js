import numbro from 'numbro'
import numbroLanguages from 'numbro/dist/languages.min'

if (window.config.locale) {
  numbro.setLanguage(window.config.locale.replace('_', '-'))
}

numbro.setDefaults({
  thousandSeparated: true,
})

export default numbro

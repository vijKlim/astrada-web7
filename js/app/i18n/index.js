/*
 * i18n initialisation
 *
 * Initialise the i18n instance to be used in the component hierarchy
 *
 * See https://react.i18next.com/components/i18next-instance.html
 */
import i18next from 'i18next'
import { initReactI18next } from 'react-i18next'
import LanguageDetector from 'i18next-browser-languagedetector'

import moment from 'moment'
import 'moment-timezone'


import ua from './locales/ua.json'
import en from './locales/en.json'

import uk_UA from 'antd/es/locale/uk_UA'
import en_US from 'antd/es/locale/en_US'


import numbro from 'numbro'
// Use minified language files to avoid syntax error
// @see https://github.com/BenjaminVanRyseghem/numbro/pull/413
import enGB from 'numbro/dist/languages/en-GB.min.js'
import ukUA from 'numbro/dist/languages/uk-UA.min.js'

i18next
  .use(LanguageDetector)
  .use(initReactI18next)
  .init({
    fallbackLng: 'en',
    resources: { ua, en },
    ns: ['common'],
    defaultNS: 'common',
    debug: process.env.DEBUG,
    detection: {
      order: ['htmlTag', 'path', 'navigator'],
    },
  })

export default i18next

export const localeDetector = () => i18next.language

export const setTimezone = timezone => moment.tz.setDefault(timezone)

const antdLocaleMap = {
  'ua': uk_UA,
  'en': en_US,
}

// Load Numbro locales
numbro.registerLanguage(ukUA)
numbro.registerLanguage(enGB)

numbro.setLanguage(localeDetector())

export const antdLocale =
  Object.prototype.hasOwnProperty.call(antdLocaleMap, localeDetector()) ? antdLocaleMap[localeDetector()] : en_US

let country

export function getCountry() {
  if (!country) {
    country = document.querySelector('body').dataset.country
  }

  return country
}

let currencySymbol

export function getCurrencySymbol() {
  if (!currencySymbol) {
    currencySymbol = document.querySelector('body').dataset.currencySymbol
  }

  return currencySymbol
}

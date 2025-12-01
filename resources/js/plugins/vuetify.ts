import { createVuetify } from 'vuetify'
import { aliases, mdi } from 'vuetify/iconsets/mdi'
import type { App } from 'vue'
import '@mdi/font/css/materialdesignicons.css'

export default {
  install(app: App) {
    const vuetify = createVuetify({
      defaults: {},
      ssr: true,
      theme: {
        defaultTheme: 'dark'
      },
      icons: {
        defaultSet: 'mdi',
        aliases,
        sets: {
          mdi
        }
      }
    })
    app.use(vuetify)
  }
}

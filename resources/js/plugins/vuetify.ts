import { createVuetify } from 'vuetify'
import type { App } from 'vue'

export default {
  install(app: App) {
    const vuetify = createVuetify({
      defaults: {},
      ssr: true,
      theme: {
        defaultTheme: 'dark'
      }
    })
    app.use(vuetify)
  }
}

import { createPinia } from 'pinia'
import piniaPersist from 'pinia-plugin-persistedstate'
import { piniaAxiosPlugin } from '@/plugins/axios'
import { piniaSnackbarPlugin } from '@/plugins/snackbar'

const pinia = createPinia()

pinia
  .use(piniaPersist)
  .use(piniaAxiosPlugin)
  .use(piniaSnackbarPlugin)

export default pinia

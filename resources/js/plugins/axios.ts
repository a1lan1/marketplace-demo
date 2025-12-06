import axios, { type AxiosInstance } from 'axios'
import type { PiniaPluginContext } from 'pinia'
import type { App } from 'vue'

declare module 'vue' {
  interface ComponentCustomProperties {
    $axios: AxiosInstance;
  }
}

export const api: AxiosInstance = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
})

export function piniaAxiosPlugin({ store }: PiniaPluginContext) {
  store.$axios = api
}

export default {
  install(app: App) {
    app.config.globalProperties.$axios = api
  }
}

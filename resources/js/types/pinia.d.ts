import 'pinia'
import type { AxiosInstance } from 'axios'
import type { snackbar } from '@/plugins/snackbar'

declare module 'pinia' {
  export interface PiniaCustomProperties {
    $axios: AxiosInstance;
    $snackbar: typeof snackbar
  }
}

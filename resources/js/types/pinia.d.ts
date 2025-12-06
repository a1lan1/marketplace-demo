import type { snackbar } from '@/plugins/snackbar'
import type { AxiosInstance } from 'axios'
import 'pinia'

declare module 'pinia' {
  export interface PiniaCustomProperties {
    $axios: AxiosInstance;
    $snackbar: typeof snackbar;
  }
}

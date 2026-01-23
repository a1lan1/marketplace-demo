import { snackbar } from '@/plugins/snackbar'
import type { AppPageProps, FlashMessage } from '@/types'
import { usePage } from '@inertiajs/vue3'
import { watch } from 'vue'

export function useFlashMessages() {
  const page = usePage<AppPageProps>()

  watch(
    () => page.props.flash,
    (flash: FlashMessage) => {
      if (!flash) return

      if (flash.success) {
        snackbar.success({ text: flash.success })
      }

      if (flash.error) {
        snackbar.error({ text: flash.error })
      }

      if (flash.message) {
        snackbar.info({ text: flash.message })
      }
    },
    { deep: true }
  )
}

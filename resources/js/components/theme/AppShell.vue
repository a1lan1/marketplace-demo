<script setup lang="ts">
import ChatWidget from '@/components/chat/ChatWidget.vue'
import { SidebarProvider } from '@/components/ui/sidebar'
import { useOrderStore } from '@/stores/order'
import type {
  AppPageProps,
  FlashMessage,
  OrderCreatedEvent,
  OrderStatusChangedEvent
} from '@/types'
import { usePage } from '@inertiajs/vue3'
import { echo } from '@laravel/echo-vue'
import { getCurrentInstance, onMounted, watch } from 'vue'

interface Props {
  variant?: 'header' | 'sidebar';
}

defineProps<Props>()

const page = usePage<AppPageProps>()
const isOpen = page.props.sidebarOpen

const app = getCurrentInstance()
const snackbar = app?.appContext.config.globalProperties.$snackbar
const orderStore = useOrderStore()

onMounted(() => {
  if (page.props.auth.user) {
    echo()
      .private(`App.Models.User.${page.props.auth.user.id}`)
      .listen('.order.created', (e: OrderCreatedEvent) => {
        snackbar.success({
          text: `New order #${e.order.id} has been placed!`,
          location: 'top right'
        })
      })
      .listen('OrderStatusChanged', (e: OrderStatusChangedEvent) => {
        snackbar.info({
          text: `Status for order #${e.order_id} updated to "${e.status}"`,
          location: 'top right'
        })

        if (orderStore.orders.length > 0) {
          orderStore.updateOrderStatus(e.order_id, e.status)
        }
      })
  }
})

watch(
  () => page.props.flash,
  (flash: FlashMessage) => {
    if (flash && flash.success) {
      if (snackbar) {
        snackbar.success({
          text: flash.success,
          location: 'top right'
        })
      }
    }
    if (flash && flash.error) {
      if (snackbar) {
        snackbar.error({
          text: flash.error,
          location: 'top right'
        })
      }
    }
    if (flash && flash.message) {
      // For general messages
      if (snackbar) {
        snackbar.info({
          text: flash.message,
          location: 'top right'
        })
      }
    }
  },
  { deep: true }
)
</script>

<template>
  <div
    v-if="variant === 'header'"
    class="flex min-h-screen w-full flex-col"
  >
    <slot />
    <ChatWidget v-if="page.props.auth.user" />
  </div>
  <SidebarProvider
    v-else
    :default-open="isOpen"
  >
    <slot />
    <ChatWidget v-if="page.props.auth.user" />
  </SidebarProvider>
</template>

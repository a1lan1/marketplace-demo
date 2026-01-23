import { snackbar } from '@/plugins/snackbar'
import { useOrderStore } from '@/stores/order'
import type {
  AppPageProps,
  OrderCreatedEvent,
  OrderStatusChangedEvent
} from '@/types'
import { usePage } from '@inertiajs/vue3'
import { echo } from '@laravel/echo-vue'
import { onMounted, onUnmounted } from 'vue'

export function useEchoSubscriptions() {
  const page = usePage<AppPageProps>()
  const orderStore = useOrderStore()

  const channelName = page.props.auth.user
    ? `App.Models.User.${page.props.auth.user.id}`
    : null

  onMounted(() => {
    if (channelName) {
      echo()
        .private(channelName)
        .listen('.order.created', (e: OrderCreatedEvent) => {
          snackbar.success({
            text: `New order #${e.order.id} has been placed!`
          })
        })
        .listen('OrderStatusChanged', (e: OrderStatusChangedEvent) => {
          snackbar.info({
            text: `Status for order #${e.order_id} updated to "${e.status}"`
          })

          if (orderStore.orders.length > 0) {
            orderStore.updateOrderStatus(e.order_id, e.status)
          }
        })
        .listen(
          '.seller.payout.processed',
          (e: { orderId: number; sellerId: number; amount: number }) => {
            if (
              page.props.auth.user &&
              page.props.auth.user.id === e.sellerId
            ) {
              snackbar.success({
                text: `You received a payout of $${(e.amount / 100).toFixed(2)} for order #${e.orderId}!`
              })
            }
          }
        )
    }
  })

  onUnmounted(() => {
    if (channelName) {
      echo().leave(channelName)
    }
  })
}

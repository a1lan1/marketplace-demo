<script setup lang="ts">
import { usePermissions } from '@/composables/usePermissions'
import { orderStatusOptions, StatusVariant } from '@/enums/OrderStatus'
import AppLayout from '@/layouts/AppLayout.vue'
import { update as updateOrderStatusRoute } from '@/routes/orders/status'
import {
  type BreadcrumbItem,
  type Order,
  type OrderStatusChangedEvent,
  type Pagination
} from '@/types'
import { formatCurrency, formatDate } from '@/utils/formatters'
import { Head, router } from '@inertiajs/vue3'
import { echo } from '@laravel/echo-vue'
import { onMounted, onUnmounted, ref } from 'vue'

const props = defineProps<{
  orders: Pagination<Order>;
}>()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Orders',
    href: '#'
  }
]

const { hasPermission } = usePermissions()
const orders = ref<Order[]>(props.orders.data)

const headers = [
  { title: 'Order ID', key: 'id' },
  { title: 'Date', key: 'created_at' },
  { title: 'Total Amount', key: 'total_amount' },
  { title: 'Status', key: 'status', sortable: false, width: 200 }
]

const updateOrderStatus = (orderId: number, status: Order['status']) => {
  router.put(updateOrderStatusRoute(orderId).url, { status })
}

const orderStatusColor = (status: Order['status']) => {
  return StatusVariant[status] ?? 'info'
}

onMounted(() => {
  orders.value.forEach((order) => {
    echo()
      .private(`orders.${order.id}`)
      .listen('OrderStatusChanged', (e: OrderStatusChangedEvent) => {
        const updatedOrder = orders.value.find((o) => o.id === e.order_id)
        if (updatedOrder) {
          updatedOrder.status = e.status
        }
      })
  })
})

onUnmounted(() => {
  orders.value.forEach((order) => {
    echo().leave(`orders.${order.id}`)
  })
})
</script>

<template>
  <Head title="Orders" />

  <AppLayout :breadcrumbs>
    <v-container>
      <v-card>
        <v-card-title>Your Orders</v-card-title>
        <v-data-table
          v-if="orders.length > 0"
          :headers="headers"
          :items="orders"
          item-value="id"
          class="elevation-1"
        >
          <template #[`item.id`]="{ item }">
            #{{ item.id }}
          </template>

          <template #[`item.total_amount`]="{ item }">
            {{ formatCurrency(Number(item.total_amount) / 100) }}
          </template>

          <template #[`item.created_at`]="{ item }">
            {{ formatDate(item.created_at) }}
          </template>

          <template #[`item.status`]="{ item }">
            <div v-if="hasPermission('orders.update-status')">
              <v-select
                v-model="item.status"
                :items="orderStatusOptions"
                label="Update Status"
                variant="solo-filled"
                density="compact"
                hide-details
                :base-color="orderStatusColor(item.status)"
                @update:model-value="updateOrderStatus(item.id, $event)"
              />
            </div>
            <v-chip
              v-else
              :color="orderStatusColor(item.status)"
            >
              {{ item.status }}
            </v-chip>
          </template>
        </v-data-table>
        <v-alert
          v-else
          type="info"
          variant="tonal"
          class="ma-4"
        >
          You don't have any orders yet.
        </v-alert>
      </v-card>
    </v-container>
  </AppLayout>
</template>

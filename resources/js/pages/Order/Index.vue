<script setup lang="ts">
import OrderDetailsDialog from '@/components/order/OrderDetailsDialog.vue'
import { usePermissions } from '@/composables/usePermissions'
import { orderStatusOptions, StatusVariant } from '@/enums/OrderStatus'
import AppLayout from '@/layouts/AppLayout.vue'
import { update as updateOrderStatusRoute } from '@/routes/orders/status'
import type { BreadcrumbItem, Order, Pagination } from '@/types'
import { formatCurrency, formatDate } from '@/utils/formatters'
import { Head, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import type { VDataTableServer } from 'vuetify/components'

type ReadonlyHeaders = VDataTableServer['$props']['headers'];

interface OrdersPageProps {
  orders: Pagination<Order>;
}

const props = defineProps<OrdersPageProps>()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Orders',
    href: '#'
  }
]

const headers: ReadonlyHeaders = [
  { title: 'Order ID', key: 'id' },
  { title: 'Date', key: 'created_at' },
  { title: 'Total Amount', key: 'total_amount' },
  { title: 'Payment Method', key: 'payment_method' },
  { title: 'Status', key: 'status', sortable: false, width: 200 },
  { title: 'Actions', key: 'actions', sortable: false, align: 'end' }
]

const { hasPermission } = usePermissions()
const loading = ref(false)
const items = ref(props.orders.data)
const totalItems = ref(props.orders.meta.total)

const isDialogVisible = ref(false)
const selectedOrderId = ref<number | null>(null)

const openDialog = (order: Order) => {
  selectedOrderId.value = order.id
  isDialogVisible.value = true
}

const updateOrderStatus = (orderId: number, status: Order['status']) => {
  router.put(updateOrderStatusRoute(orderId).url, { status })
}

const orderStatusColor = (status: Order['status']) => {
  return StatusVariant[status] ?? 'info'
}

const loadItems = ({
  page,
  itemsPerPage
}: {
  page: number;
  itemsPerPage: number;
}) => {
  if (
    page === props.orders.meta.current_page &&
    itemsPerPage === props.orders.meta.per_page
  )
    return

  router.get(
    '/orders',
    { page, per_page: itemsPerPage },
    {
      preserveState: true,
      replace: true,
      onStart: () => (loading.value = true),
      onFinish: () => (loading.value = false)
    }
  )
}

watch(
  () => props.orders,
  (newOrders) => {
    items.value = newOrders.data
    totalItems.value = newOrders.meta.total
  }
)
</script>

<template>
  <Head title="Orders" />

  <AppLayout :breadcrumbs>
    <v-container>
      <v-card>
        <v-card-title>Your Orders</v-card-title>
        <v-data-table-server
          v-if="items.length > 0"
          :headers="headers"
          :items="items"
          :items-length="totalItems"
          :loading="loading"
          :items-per-page="props.orders.meta.per_page"
          item-value="id"
          class="elevation-1"
          @update:options="loadItems"
        >
          <template #[`item.id`]="{ item }">
            #{{ item.id }}
          </template>

          <template #[`item.total_amount`]="{ item }">
            {{ formatCurrency(item.total_amount) }}
          </template>

          <template #[`item.created_at`]="{ item }">
            {{ formatDate(item.created_at) }}
          </template>

          <template #[`item.payment_method`]="{ item }">
            {{ item.payment_method }}
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

          <template #[`item.actions`]="{ item }">
            <v-btn
              icon="mdi-eye"
              variant="text"
              size="small"
              color="primary"
              @click="openDialog(item)"
            />
          </template>
        </v-data-table-server>
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

    <OrderDetailsDialog
      v-model="isDialogVisible"
      :order-id="selectedOrderId"
    />
  </AppLayout>
</template>

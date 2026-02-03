<script setup lang="ts">
import OrderDetails from '@/components/order/OrderDetails.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { useOrderStore } from '@/stores/order'
import type { BreadcrumbItem, Order } from '@/types'
import { Head } from '@inertiajs/vue3'
import { onMounted, onUnmounted } from 'vue'

interface OrderShowPageProps {
  order: Order;
}

const props = defineProps<OrderShowPageProps>()

const orderStore = useOrderStore()
const { setActiveOrder, clearActiveOrder } = orderStore

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Orders', href: '/orders' },
  { title: `Order #${props.order.id}`, href: '#' }
]

onMounted(() => setActiveOrder(props.order))
onUnmounted(() => clearActiveOrder())
</script>

<template>
  <Head :title="`Order #${order.id}`" />
  <AppLayout :breadcrumbs>
    <v-container>
      <v-card>
        <v-card-title> Order #{{ order.id }} Details </v-card-title>
        <OrderDetails />
      </v-card>
    </v-container>
  </AppLayout>
</template>

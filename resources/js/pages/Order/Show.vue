<script setup lang="ts">
import OrderDetails from '@/components/order/OrderDetails.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { BreadcrumbItem, Order } from '@/types'
import { Head } from '@inertiajs/vue3'

interface OrderShowPageProps {
  order: Order;
}

const props = defineProps<OrderShowPageProps>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Orders', href: '/orders' },
  { title: `Order #${props.order.id}`, href: '#' }
]
</script>

<template>
  <Head :title="`Order #${order.id}`" />
  <AppLayout :breadcrumbs>
    <v-container>
      <v-card>
        <v-card-title> Order #{{ order.id }} Details </v-card-title>
        <OrderDetails :order="order" />
      </v-card>
    </v-container>
  </AppLayout>
</template>

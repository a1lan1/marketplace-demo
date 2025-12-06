<script setup lang="ts">
import { useOrderStore } from '@/stores/order'
import type { Order } from '@/types'
import { storeToRefs } from 'pinia'
import { computed, onMounted, onUnmounted } from 'vue'

const props = defineProps<{
  orders?: Order[];
}>()

const orderStore = useOrderStore()
const { loading, orders: orderList, activeOrder } = storeToRefs(orderStore)
const { fetchUserOrders } = orderStore

const orderItems = computed(() => {
  return props.orders || orderList.value || []
})

onMounted(() => {
  if (!props.orders) {
    fetchUserOrders()
  }
})

onUnmounted(() => {
  orderStore.$reset()
})
</script>

<template>
  <div
    class="flex w-1/5 flex-col"
    style="height: calc(100vh - 110px)"
  >
    <v-list-item
      title="Your Orders"
      class="text-h6"
      density="compact"
    />

    <v-divider />

    <div class="min-h-0 flex-grow overflow-y-auto">
      <v-skeleton-loader
        v-if="loading"
        type="list-item-two-line"
      />

      <v-list
        v-model:active="activeOrder"
        nav
        density="compact"
        variant="tonal"
      >
        <v-list-item
          v-for="order in orderItems"
          :key="order.id"
          :title="`Order #${order.id}`"
          :subtitle="`Total: ${order.total_amount.formatted}`"
          :active="activeOrder?.id === order.id"
          active-color="primary"
          @click="activeOrder = order"
        >
          <template #prepend>
            <v-avatar>
              <v-img
                v-if="order.buyer"
                :src="order.buyer.avatar"
              />
              <v-icon
                v-else
                icon="mdi-account"
              />
            </v-avatar>
          </template>
        </v-list-item>
      </v-list>
    </div>
  </div>
</template>

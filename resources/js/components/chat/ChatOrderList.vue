<script setup lang="ts">
import InfiniteScroll from '@/components/theme/InfiniteScroll.vue'
import { useOrderStore } from '@/stores/order'
import type { Order, Pagination } from '@/types'
import { storeToRefs } from 'pinia'
import { onMounted, onUnmounted } from 'vue'

const props = defineProps<{
  orders?: Pagination<Order>;
}>()

const orderStore = useOrderStore()
const {
  loading,
  orders: paginatedOrders,
  activeOrder,
  pagination
} = storeToRefs(orderStore)
const { fetchUserOrders, setOrders } = orderStore

onMounted(() => {
  if (props.orders) {
    setOrders(props.orders)
  } else {
    fetchUserOrders(pagination.value?.current_page || 1)
  }
})

onUnmounted(() => {
  orderStore.$reset()
})
</script>

<template>
  <div
    class="flex w-1/5 flex-col"
    style="height: calc(100vh - 70px)"
  >
    <v-list-item
      title="Your Orders"
      class="text-h6"
      density="compact"
    />

    <v-divider />

    <div class="min-h-0 flex-grow overflow-y-auto">
      <InfiniteScroll
        :items="paginatedOrders"
        :pagination="pagination"
        :on-load="fetchUserOrders"
      >
        <v-skeleton-loader
          v-if="loading && !paginatedOrders.length"
          type="list-item-two-line@5"
        />
        <v-list
          v-else
          v-model:active="activeOrder"
          nav
          density="compact"
          variant="tonal"
        >
          <v-list-item
            v-for="order in paginatedOrders"
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
      </InfiniteScroll>
    </div>
  </div>
</template>

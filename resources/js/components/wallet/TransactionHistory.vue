<script setup lang="ts">
import OrderDetailsDialog from '@/components/order/OrderDetailsDialog.vue'
import InfiniteScroll from '@/components/theme/InfiniteScroll.vue'
import { useTransactionStore } from '@/stores/transaction'
import type { Transaction } from '@/types'
import { getTransactionColor, getTransactionIcon } from '@/utils/transaction'
import { usePage } from '@inertiajs/vue3'
import { echo } from '@laravel/echo-vue'
import { storeToRefs } from 'pinia'
import { onMounted, onUnmounted, ref } from 'vue'

const transactionStore = useTransactionStore()
const { transactions, pagination } = storeToRefs(transactionStore)
const { fetchTransactions, resetTransactions } = transactionStore

const isOrderDetailsModalOpen = ref(false)
const selectedOrderId = ref<number | null>(null)

const userId = usePage().props.auth.user.id
const channelName = `App.Models.User.${userId}`

onMounted(() => {
  fetchTransactions()

  echo()
    .private(channelName)
    .listen('.funds.deposited', fetchTransactions)
    .listen('.funds.withdrawn', fetchTransactions)
    .listen('.funds.transferred', fetchTransactions)
})

onUnmounted(() => {
  echo()
    .private(channelName)
    .stopListening('.funds.deposited')
    .stopListening('.funds.withdrawn')
    .stopListening('.funds.transferred')

  resetTransactions()
})

const handleTransactionClick = (transaction: Transaction) => {
  if (transaction.order_id) {
    selectedOrderId.value = transaction.order_id
    isOrderDetailsModalOpen.value = true
  }
}
</script>

<template>
  <v-card>
    <v-card-title>Transaction History</v-card-title>
    <v-card-text style="height: calc(100vh - 165px); overflow-y: auto">
      <InfiniteScroll
        :items="transactions"
        :pagination="pagination"
        :on-load="fetchTransactions"
      >
        <v-list
          lines="three"
          density="compact"
        >
          <v-list-item
            v-for="transaction in transactions"
            :key="transaction.id"
            @click="handleTransactionClick(transaction)"
          >
            <template #prepend>
              <v-icon :color="getTransactionColor(transaction.type)">
                {{ getTransactionIcon(transaction.type) }}
              </v-icon>
            </template>
            <v-list-item-title class="text-capitalize">
              {{ transaction.type }}
              <span
                :class="{
                  'text-success': transaction.type === 'deposit' || transaction.type === 'transfer',
                  'text-error': transaction.type === 'withdrawal',
                }"
                class="font-weight-bold ml-2"
              >
                {{ transaction.formatted_amount }}
              </span>
            </v-list-item-title>
            <v-list-item-subtitle>
              {{ transaction.description }}
            </v-list-item-subtitle>
            <v-list-item-subtitle class="text-caption">
              {{ new Date(transaction.created_at).toLocaleString() }}
            </v-list-item-subtitle>
          </v-list-item>
        </v-list>
      </InfiniteScroll>
    </v-card-text>

    <OrderDetailsDialog
      v-model="isOrderDetailsModalOpen"
      :order-id="selectedOrderId"
    />
  </v-card>
</template>

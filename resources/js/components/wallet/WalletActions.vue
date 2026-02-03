<script setup lang="ts">
import DepositModal from '@/components/wallet/DepositModal.vue'
import TransferModal from '@/components/wallet/TransferModal.vue'
import WithdrawModal from '@/components/wallet/WithdrawModal.vue'
import type { PayoutMethod } from '@/types'
import { ref } from 'vue'

defineProps<{
  payoutMethods: PayoutMethod[];
}>()

const isDepositModalOpen = ref(false)
const isWithdrawModalOpen = ref(false)
const isTransferModalOpen = ref(false)
</script>

<template>
  <div class="mt-3 text-center">
    <v-btn-group
      divided
      color="primary"
      variant="tonal"
      density="compact"
    >
      <v-btn
        prepend-icon="mdi-arrow-up-bold"
        @click="isDepositModalOpen = true"
      >
        Deposit
      </v-btn>
      <v-btn
        prepend-icon="mdi-arrow-down-bold"
        @click="isWithdrawModalOpen = true"
      >
        Withdraw
      </v-btn>
      <v-btn
        prepend-icon="mdi-swap-horizontal-bold"
        @click="isTransferModalOpen = true"
      >
        Transfer
      </v-btn>
    </v-btn-group>

    <DepositModal v-model="isDepositModalOpen" />
    <WithdrawModal
      v-model="isWithdrawModalOpen"
      :payout-methods="payoutMethods"
    />
    <TransferModal v-model="isTransferModalOpen" />
  </div>
</template>

<script setup lang="ts">
import PayoutMethods from '@/components/wallet/PayoutMethods.vue'
import TransactionHistory from '@/components/wallet/TransactionHistory.vue'
import WalletActions from '@/components/wallet/WalletActions.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { BreadcrumbItem, MoneyData, PayoutMethod } from '@/types'
import { Head } from '@inertiajs/vue3'

interface Props {
  balance: MoneyData;
  payoutMethods: PayoutMethod[];
}

defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Wallet',
    href: '#'
  }
]
</script>

<template>
  <Head title="Wallet" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <v-container>
      <v-row>
        <v-col
          cols="12"
          md="4"
        >
          <v-card>
            <v-card-title>Current Balance</v-card-title>
            <v-card-text>
              <p class="text-h3 font-weight-bold">
                {{ balance.formatted }}
              </p>
              <WalletActions :payout-methods="payoutMethods" />
            </v-card-text>
          </v-card>

          <PayoutMethods :payout-methods="payoutMethods" />
        </v-col>

        <v-col
          cols="12"
          md="8"
        >
          <TransactionHistory />
        </v-col>
      </v-row>
    </v-container>
  </AppLayout>
</template>

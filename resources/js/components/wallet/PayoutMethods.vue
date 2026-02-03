<script setup lang="ts">
import AddPayoutMethodModal from '@/components/wallet/AddPayoutMethodModal.vue'
import { destroy } from '@/routes/payout-methods'
import type { PayoutMethod } from '@/types'
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

defineProps<{
  payoutMethods: PayoutMethod[];
}>()

const isAddPayoutMethodModalOpen = ref(false)

const deletePayoutMethod = (id: number) => {
  if (confirm('Are you sure you want to delete this payout method?')) {
    router.delete(destroy(id).url, {
      onSuccess: () => {
        //
      },
      onError: (errors) => {
        console.error(errors)
      }
    })
  }
}
</script>

<template>
  <v-card class="mt-8">
    <v-card-title>Payout Methods</v-card-title>
    <v-card-text>
      <v-list lines="two">
        <v-list-item
          v-for="method in payoutMethods"
          :key="method.id"
        >
          <template #prepend>
            <v-icon :icon="method.type === 'bank_account' ? 'mdi-bank' : 'mdi-credit-card'" />
          </template>
          <v-list-item-title>
            {{
              method.details?.bank_name ||
                method.details?.brand ||
                'Unknown Method'
            }}
          </v-list-item-title>
          <v-list-item-subtitle>
            **** {{ method.details?.last4 || '' }}
          </v-list-item-subtitle>

          <template #append>
            <v-btn
              icon="mdi-delete"
              variant="text"
              color="error"
              size="small"
              @click="deletePayoutMethod(method.id)"
            />
          </template>
        </v-list-item>
        <v-list-item v-if="payoutMethods.length === 0">
          <v-list-item-title class="text-grey text-center">
            No payout methods added.
          </v-list-item-title>
        </v-list-item>
      </v-list>
      <v-btn
        color="secondary"
        block
        class="mt-4"
        @click="isAddPayoutMethodModalOpen = true"
      >
        Add Payout Method
      </v-btn>
    </v-card-text>

    <AddPayoutMethodModal v-model="isAddPayoutMethodModalOpen" />
  </v-card>
</template>

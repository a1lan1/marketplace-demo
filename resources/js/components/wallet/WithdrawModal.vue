<script setup lang="ts">
import { useZodValidation } from '@/composables/useZodValidation'
import { snackbar } from '@/plugins/snackbar'
import { useTransactionStore } from '@/stores/transaction'
import type { PayoutMethod } from '@/types'
import { router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { z } from 'zod'

interface WithdrawForm {
  amount: number;
  currency: string;
  payout_method_id: number | null;
  description: string;
}

const isOpen = defineModel<boolean>()

const props = defineProps<{
  payoutMethods: PayoutMethod[];
}>()

const processing = ref(false)
const transactionStore = useTransactionStore()

const schema = z.object({
  amount: z.number().min(1, 'Amount must be at least $1'),
  currency: z.string().default('USD'),
  payout_method_id: z
    .number()
    .nullable()
    .refine((val) => val !== null, {
      message: 'Payout method is required'
    }),
  description: z.string().optional().default('Wallet withdrawal')
})

const defaultForm: WithdrawForm = {
  amount: 10,
  currency: 'USD',
  payout_method_id: null,
  description: 'Wallet withdrawal'
}

const form = ref<WithdrawForm>({ ...defaultForm })

const { errors: formErrors, validate } = useZodValidation(schema, form)

const resetForm = () => {
  form.value = { ...defaultForm }
  Object.keys(formErrors.value).forEach(
    (key) => delete formErrors.value[key as keyof typeof formErrors.value]
  )
}

watch(isOpen, (newVal) => {
  if (!newVal) {
    resetForm()
  }
})

const getPayoutMethodTitle = (method: PayoutMethod) => {
  const details = method.details
  const brand = details?.bank_name || details?.brand || 'Unknown'
  const last4 = details?.last4 || ''

  return `${brand} (**** ${last4})`
}

const submit = async() => {
  if (!validate()) return

  processing.value = true

  try {
    const amountInCents = Math.round(form.value.amount * 100)

    await transactionStore.withdrawFunds({
      amount: amountInCents,
      currency: form.value.currency,
      payout_method_id: form.value.payout_method_id!,
      description: form.value.description
    })

    isOpen.value = false
    snackbar.success({ text: 'Withdrawal successful!' })
    router.reload()
  } catch (e: any) {
    console.error(e)
    if (e.response?.data?.errors) {
      const errors = e.response.data.errors
      if (errors.amount) formErrors.value.amount = errors.amount[0]
      if (errors.payout_method_id)
        formErrors.value.payout_method_id = errors.payout_method_id[0]
    } else {
      const message =
        e.response?.data?.error ||
        e.response?.data?.message ||
        'An error occurred during withdrawal.'
      snackbar.error({ text: message })
    }
  } finally {
    processing.value = false
  }
}

const payoutMethodItems = computed(() => {
  return props.payoutMethods.map((method) => ({
    title: getPayoutMethodTitle(method),
    value: method.id
  }))
})
</script>

<template>
  <v-dialog
    v-model="isOpen"
    max-width="500"
    persistent
  >
    <v-card>
      <v-card-title class="headline">
        Withdraw Funds
      </v-card-title>
      <v-card-text>
        <p class="mb-4">
          Enter the amount you want to withdraw from your wallet.
        </p>

        <form @submit.prevent="submit">
          <v-select
            v-model="form.payout_method_id"
            :items="payoutMethodItems"
            item-title="title"
            item-value="value"
            label="Payout Method"
            :error-messages="formErrors.payout_method_id"
            variant="outlined"
            class="mb-4"
          />

          <v-text-field
            v-model.number="form.amount"
            label="Amount ($)"
            type="number"
            step="0.01"
            :error-messages="formErrors.amount"
            variant="outlined"
            class="mb-4"
            min="1"
          />

          <v-text-field
            v-model="form.description"
            label="Description (optional)"
            :error-messages="formErrors.description"
            variant="outlined"
            class="mb-4"
          />

          <v-card-actions class="justify-end">
            <v-btn
              variant="text"
              :disabled="processing"
              @click="isOpen = false"
            >
              Cancel
            </v-btn>
            <v-btn
              color="primary"
              type="submit"
              :loading="processing"
              :disabled="processing"
            >
              Withdraw
            </v-btn>
          </v-card-actions>
        </form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

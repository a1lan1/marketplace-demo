<script setup lang="ts">
import { useZodValidation } from '@/composables/useZodValidation'
import { snackbar } from '@/plugins/snackbar'
import { useTransactionStore } from '@/stores/transaction'
import { router } from '@inertiajs/vue3'
import { storeToRefs } from 'pinia'
import { ref, watch } from 'vue'
import { z } from 'zod'

interface TransferForm {
  email: string;
  amount: number;
  currency: string;
  description: string;
}

const isOpen = defineModel<boolean>()

const processing = ref(false)

const transactionStore = useTransactionStore()
const { recipients, loadingRecipients } = storeToRefs(transactionStore)
const { fetchRecipients, transferFunds } = transactionStore

const schema = z.object({
  email: z.string().email('Invalid email address'),
  amount: z.number().min(1, 'Amount must be at least $1'),
  currency: z.string().default('USD'),
  description: z.string().optional().default('Wallet transfer')
})

const defaultForm: TransferForm = {
  email: '',
  amount: 10,
  currency: 'USD',
  description: 'Wallet transfer'
}

const form = ref<TransferForm>({ ...defaultForm })

const { errors: formErrors, validate } = useZodValidation(schema, form)

const resetForm = () => {
  form.value = { ...defaultForm }
  Object.keys(formErrors.value).forEach(
    (key) => delete formErrors.value[key as keyof typeof formErrors.value]
  )
}

watch(isOpen, (newVal) => {
  if (newVal) {
    fetchRecipients()
  } else {
    resetForm()
  }
})

const submit = async() => {
  if (!validate()) return

  processing.value = true

  try {
    const amountInCents = Math.round(form.value.amount * 100)

    await transferFunds({
      email: form.value.email,
      amount: amountInCents,
      currency: form.value.currency,
      description: form.value.description
    })

    isOpen.value = false
    snackbar.success({ text: 'Transfer successful!' })
    router.reload()
  } catch (e: any) {
    if (e.response?.data?.errors) {
      const errors = e.response.data.errors
      if (errors.email) formErrors.value.email = errors.email[0]
      if (errors.amount) formErrors.value.amount = errors.amount[0]
    } else {
      const message =
        e.response?.data?.error ||
        e.response?.data?.message ||
        'An error occurred during transfer.'
      snackbar.error({ text: message })
    }
  } finally {
    processing.value = false
  }
}
</script>

<template>
  <v-dialog
    v-model="isOpen"
    max-width="500"
    persistent
  >
    <v-card>
      <v-card-title class="headline">
        Transfer Funds
      </v-card-title>
      <v-card-text>
        <p class="mb-4">
          Transfer funds to another user.
        </p>

        <form @submit.prevent="submit">
          <v-autocomplete
            v-model="form.email"
            :items="recipients"
            item-title="email"
            item-value="email"
            label="Recipient Email"
            :loading="loadingRecipients"
            :error-messages="formErrors.email"
            variant="outlined"
            class="mb-4"
            placeholder="Start typing to search..."
            @update:search="fetchRecipients"
          >
            <template #item="{ props, item }">
              <v-list-item
                v-bind="props"
                :title="item.raw.name"
                :subtitle="item.raw.email"
              />
            </template>
          </v-autocomplete>

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
              Transfer
            </v-btn>
          </v-card-actions>
        </form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

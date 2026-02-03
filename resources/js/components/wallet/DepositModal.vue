<script setup lang="ts">
import StripeForm from '@/components/forms/StripeForm.vue'
import { useZodValidation } from '@/composables/useZodValidation'
import { snackbar } from '@/plugins/snackbar'
import { useTransactionStore } from '@/stores/transaction'
import { router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { z } from 'zod'

interface DepositForm {
  amount: number;
  currency: string;
  payment_method_id: string;
  provider: 'stripe';
}

const processing = ref(false)
const isOpen = defineModel<boolean>()
const stripeFormRef = ref<InstanceType<typeof StripeForm> | null>(null)

const transactionStore = useTransactionStore()

const schema = z.object({
  amount: z.number().min(1, 'Amount must be at least $1'),
  currency: z.string().default('USD'),
  payment_method_id: z.string().min(1, 'Payment method is required'),
  provider: z.string().default('stripe')
})

const defaultForm: DepositForm = {
  amount: 10,
  currency: 'USD',
  payment_method_id: '',
  provider: 'stripe'
}

const form = ref<DepositForm>({ ...defaultForm })

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

const submit = async() => {
  if (!stripeFormRef.value) {
    snackbar.error({ text: 'Stripe form is not ready.' })

    return
  }

  processing.value = true

  const { paymentMethod, error } =
    await stripeFormRef.value.createPaymentMethod()

  if (error) {
    processing.value = false
    snackbar.error({ text: error.message || 'An error occurred with Stripe.' })

    return
  }

  if (paymentMethod) {
    form.value.payment_method_id = paymentMethod.id

    if (!validate()) return

    try {
      const amountInCents = Math.round(form.value.amount * 100)

      await transactionStore.depositFunds({
        amount: amountInCents,
        currency: form.value.currency,
        payment_method_id: form.value.payment_method_id,
        provider: form.value.provider
      })

      isOpen.value = false
      snackbar.success({ text: 'Deposit successful!' })
      router.reload()
    } catch (e: any) {
      snackbar.error({
        text:
          e.response?.data?.error ||
          e.response?.data?.message ||
          'An error occurred during deposit.'
      })

      if (e.response?.data?.errors) {
        if (e.response.data.errors.amount) {
          formErrors.value.amount = e.response.data.errors.amount[0]
        }
        if (e.response.data.errors.payment_method_id) {
          formErrors.value.payment_method_id =
            e.response.data.errors.payment_method_id[0]
        }
      }
    } finally {
      processing.value = false
    }
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
        Deposit Funds
      </v-card-title>
      <v-card-text>
        <p class="mb-4">
          Enter the amount you want to deposit into your wallet.
        </p>

        <form @submit.prevent="submit">
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

          <StripeForm
            v-if="isOpen"
            ref="stripeFormRef"
            element-id="deposit-card-element"
          />

          <v-alert
            v-if="formErrors.payment_method_id"
            type="error"
            variant="tonal"
            class="mb-4"
            density="compact"
          >
            {{ formErrors.payment_method_id }}
          </v-alert>

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
              Deposit
            </v-btn>
          </v-card-actions>
        </form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

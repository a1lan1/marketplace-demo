<script setup lang="ts">
import { useStripe } from '@/composables/useStripe'
import { useZodValidation } from '@/composables/useZodValidation'
import { snackbar } from '@/plugins/snackbar'
import { useTransactionStore } from '@/stores/transaction'
import type { Stripe, StripeCardElement, StripeCardElementChangeEvent } from '@stripe/stripe-js'
import { nextTick, ref, watch } from 'vue'
import { z } from 'zod'

type PayoutMethodType = 'bank_account' | 'card';
type AccountHolderType = 'individual' | 'company';

interface PayoutForm {
  type: PayoutMethodType;
  bankAccount: {
    country: string;
    currency: string;
    routing_number: string;
    account_number: string;
    account_holder_name: string;
    account_holder_type: AccountHolderType;
  };
  token: string;
  provider: 'stripe';
}

const isOpen = defineModel<boolean>()

const { stripe, stripeErrors } = useStripe()
const cardElement = ref<StripeCardElement | null>(null)
const processing = ref(false)

const transactionStore = useTransactionStore()

const bankAccountSchema = z.object({
  account_holder_name: z.string().min(1, 'Account holder name is required'),
  routing_number: z.string().min(1, 'Routing number is required'),
  account_number: z.string().min(1, 'Account number is required')
})

const cardSchema = z.object({
  token: z.string().min(1, 'Card details are required')
})

const schema = z.discriminatedUnion('type', [
  z.object({ type: z.literal('bank_account'), bankAccount: bankAccountSchema }),
  z.object({ type: z.literal('card'), token: cardSchema.shape.token })
])

const defaultForm: PayoutForm = {
  type: 'bank_account',
  bankAccount: {
    country: 'US',
    currency: 'usd',
    routing_number: '',
    account_number: '',
    account_holder_name: '',
    account_holder_type: 'individual'
  },
  token: '',
  provider: 'stripe'
}

const form = ref<PayoutForm>({ ...defaultForm })

const { errors: formErrors, validate } = useZodValidation(schema, form)

const resetForm = () => {
  form.value = { ...defaultForm }
  stripeErrors.value = null
  Object.keys(formErrors.value).forEach(
    (key) => delete formErrors.value[key as keyof typeof formErrors.value]
  )
}

const createCardElement = () => {
  if (!stripe.value || cardElement.value) return

  const elements = stripe.value.elements()
  cardElement.value = elements.create('card')
  cardElement.value.mount('#card-element-payout')
  cardElement.value.on('change', (event: StripeCardElementChangeEvent) => {
    stripeErrors.value = event.error ? event.error.message : null
    if (!event.error) {
      formErrors.value.token = ''
    }
  })
}

const destroyCardElement = () => {
  if (cardElement.value) {
    cardElement.value.destroy()
    cardElement.value = null
  }
}

watch(
  () => form.value.type,
  async(newType) => {
    destroyCardElement()
    if (newType === 'card') {
      await nextTick()
      createCardElement()
    }
  }
)

watch(isOpen, (newVal) => {
  if (newVal) {
    if (form.value.type === 'card') {
      nextTick().then(createCardElement)
    }
  } else {
    destroyCardElement()
    resetForm()
  }
})

const createStripeToken = async(
  stripeInstance: Stripe,
  type: PayoutMethodType,
  data: any
): Promise<string> => {
  let tokenResult
  if (type === 'bank_account') {
    tokenResult = await stripeInstance.createToken('bank_account', data)
  } else if (type === 'card' && data) {
    tokenResult = await stripeInstance.createToken(data as StripeCardElement)
  } else {
    throw new Error('Payment method element not initialized.')
  }

  if (tokenResult.error) {
    throw new Error(tokenResult.error.message || 'An unknown error occurred.')
  }
  if (!tokenResult.token) {
    throw new Error('Token creation failed.')
  }

  return tokenResult.token.id
}

const submit = async() => {
  if (!stripe.value) {
    snackbar.error({ text: 'Stripe has not loaded yet.' })

    return
  }
  processing.value = true
  stripeErrors.value = null

  try {
    form.value.token = await createStripeToken(
      stripe.value,
      form.value.type,
      form.value.type === 'card' ? cardElement.value : form.value.bankAccount
    )

    if (!validate()) {
      processing.value = false

      return
    }

    await transactionStore.addPayoutMethod({
      provider: form.value.provider,
      token: form.value.token,
      type: form.value.type
    })

    isOpen.value = false
    snackbar.success({ text: 'Payout method added successfully!' })
  } catch (e: any) {
    console.error(e)
    if (e.response?.data?.errors) {
      if (e.response.data.errors.token) {
        formErrors.value.token = e.response.data.errors.token[0]
      }
    } else {
      snackbar.error({ text: e.message || 'Error adding payout method.' })
    }
  } finally {
    processing.value = false
  }
}
</script>

<template>
  <v-dialog
    v-model="isOpen"
    max-width="600"
    persistent
  >
    <v-card>
      <v-card-title class="headline">
        Add Payout Method
      </v-card-title>
      <v-card-text>
        <p class="mb-4">
          Choose a type and provide details for your payout method.
        </p>

        <v-radio-group
          v-model="form.type"
          row
          class="mb-4"
        >
          <v-radio
            label="Bank Account (US)"
            value="bank_account"
          />
          <v-radio
            label="Debit Card"
            value="card"
          />
        </v-radio-group>

        <form @submit.prevent="submit">
          <div v-if="form.type === 'bank_account'">
            <v-text-field
              v-model="form.bankAccount.account_holder_name"
              label="Account Holder Name"
              :error-messages="formErrors['bankAccount.account_holder_name']"
              required
              class="mb-2"
            />
            <v-radio-group
              v-model="form.bankAccount.account_holder_type"
              row
              label="Account Holder Type"
              class="mb-2"
            >
              <v-radio
                label="Individual"
                value="individual"
              />
              <v-radio
                label="Company"
                value="company"
              />
            </v-radio-group>
            <v-text-field
              v-model="form.bankAccount.routing_number"
              label="Routing Number"
              :error-messages="formErrors['bankAccount.routing_number']"
              required
              class="mb-2"
            />
            <v-text-field
              v-model="form.bankAccount.account_number"
              label="Account Number"
              :error-messages="formErrors['bankAccount.account_number']"
              required
              class="mb-4"
            />
          </div>

          <div
            v-show="form.type === 'card'"
            id="card-element-payout"
            class="pa-3 mb-4 rounded-md border"
          />

          <v-alert
            v-if="formErrors.token"
            type="error"
            variant="tonal"
            class="mb-4"
            density="compact"
          >
            {{ formErrors.token }}
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
              Add Method
            </v-btn>
          </v-card-actions>
        </form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

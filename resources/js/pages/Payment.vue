<script setup lang="ts">
import Money from '@/components/common/Money.vue'
import CreditCardForm from '@/components/forms/CreditCardForm.vue'
import StripeForm from '@/components/forms/StripeForm.vue'
import { usePayment } from '@/composables/usePayment'
import AppLayout from '@/layouts/AppLayout.vue'
import { index as checkoutIndex } from '@/routes/checkout'
import { useCartStore } from '@/stores/cart'
import { usePaymentStore } from '@/stores/payment'
import type { BreadcrumbItem } from '@/types'
import { PaymentProvider } from '@/types/payment'
import { usePage } from '@inertiajs/vue3'
import { storeToRefs } from 'pinia'
import { computed, onMounted } from 'vue'

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Checkout',
    href: checkoutIndex().url
  },
  {
    title: 'Payment',
    href: '#'
  }
]

const page = usePage()
const userBalance = computed(() => page.props.auth.user.balance)

const cartStore = useCartStore()
const { totalPrice } = storeToRefs(cartStore)

const paymentStore = usePaymentStore()
const { paymentMethods } = storeToRefs(paymentStore)
const { fetchPaymentMethods } = paymentStore

const {
  saveCard,
  processing,
  selectedMethod,
  paymentProvider,
  customCardData,
  paymentMethodType,
  stripeFormRef,
  isBalancePayment,
  isNewCardSelected,
  isStripePaymentProvider,
  isAmountInvalid,
  errors,
  handlePayment,
  NEW_CARD_SELECTION
} = usePayment({ userBalance })

onMounted(async() => {
  await fetchPaymentMethods()
})

const paymentOptions = computed(() => {
  const options = paymentMethods.value.map((method) => ({
    title: `${method.brand} **** ${method.last_four}`,
    value: method.provider_id
  }))

  options.push({
    title: 'New Card',
    value: NEW_CARD_SELECTION
  })

  return options
})
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <v-container>
      <v-row justify="center">
        <v-col
          cols="12"
          md="6"
        >
          <v-card class="pa-4">
            <v-card-title class="text-h5 mb-4">
              Payment
            </v-card-title>

            <v-card-text>
              <v-btn-toggle
                v-model="paymentMethodType"
                color="primary"
                mandatory
                density="compact"
                class="mb-4 w-100"
              >
                <v-btn
                  value="balance"
                  prepend-icon="mdi-cash"
                  class="flex-grow-1"
                >
                  Balance
                </v-btn>
                <v-btn
                  value="card"
                  prepend-icon="mdi-credit-card"
                  class="flex-grow-1"
                >
                  Card
                </v-btn>
              </v-btn-toggle>

              <div
                v-if="isBalancePayment"
                class="py-4 text-center"
              >
                <div class="text-h6">
                  Current Balance
                </div>
                <div class="text-h4 text-primary">
                  {{ userBalance.formatted }}
                </div>
                <div
                  v-if="isAmountInvalid"
                  class="text-error mt-2"
                >
                  Insufficient funds
                </div>
              </div>
              <div v-else>
                <v-select
                  v-model="selectedMethod"
                  :items="paymentOptions"
                  label="Select Payment Method"
                  variant="outlined"
                  density="compact"
                />

                <div v-if="isNewCardSelected">
                  <v-switch
                    v-model="paymentProvider"
                    :true-value="PaymentProvider.Stripe"
                    :false-value="PaymentProvider.Custom"
                    :label="`Use: ${isStripePaymentProvider ? 'Stripe' : 'Custom'}`"
                    color="primary"
                    inset
                  />

                  <StripeForm
                    v-if="isStripePaymentProvider"
                    ref="stripeFormRef"
                  />
                  <CreditCardForm
                    v-else
                    v-model="customCardData"
                    :errors="errors"
                  />

                  <v-checkbox
                    v-model="saveCard"
                    label="Save card for future purchases"
                    hide-details
                  />
                </div>
              </div>
            </v-card-text>

            <v-card-actions>
              <v-btn
                color="success"
                block
                size="large"
                variant="elevated"
                :loading="processing"
                :disabled="isAmountInvalid"
                @click="handlePayment"
              >
                Pay: <Money :value="totalPrice" />
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </AppLayout>
</template>

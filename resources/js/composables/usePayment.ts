import type StripeForm from '@/components/forms/StripeForm.vue'
import { useZodValidation } from '@/composables/useZodValidation'
import { snackbar } from '@/plugins/snackbar'
import { useCartStore } from '@/stores/cart'
import { usePaymentStore } from '@/stores/payment'
import type { MoneyData } from '@/types'
import type {
  CardDetails,
  PaymentMethodType,
  PaymentSelection
} from '@/types/payment'
import { PaymentProvider } from '@/types/payment'
import { generateUUID } from '@/utils/uuid'
import { router } from '@inertiajs/vue3'
import { storeToRefs } from 'pinia'
import { computed, ref, Ref } from 'vue'
import { z } from 'zod'

const NEW_CARD_SELECTION: PaymentSelection = 'new'

interface UsePaymentOptions {
  userBalance: Ref<MoneyData | undefined | null>;
}

export function usePayment({ userBalance }: UsePaymentOptions) {
  const cartStore = useCartStore()
  const { totalPrice, cartItemsForOrder } = storeToRefs(cartStore)
  const { clearCart } = cartStore

  const paymentStore = usePaymentStore()
  const { paymentMethods } = storeToRefs(paymentStore)

  const stripeFormRef = ref<InstanceType<typeof StripeForm> | null>(null)

  const saveCard = ref(false)
  const processing = ref(false)
  const selectedMethod = ref<string | PaymentSelection>(NEW_CARD_SELECTION)
  const idempotencyKey = ref(generateUUID())

  const paymentProvider = ref<PaymentProvider>(PaymentProvider.Fake)
  const customCardData = ref<CardDetails>({
    number: '',
    name: '',
    expiry: '',
    cvv: ''
  })

  const paymentMethodType = ref<PaymentMethodType>('balance')

  const isCardPayment = computed(() => paymentMethodType.value === 'card')
  const isBalancePayment = computed(
    () => paymentMethodType.value === 'balance'
  )

  const isNewCardSelected = computed(
    () => selectedMethod.value === NEW_CARD_SELECTION
  )
  const isStripePaymentProvider = computed(
    () => paymentProvider.value === PaymentProvider.Stripe
  )

  const isAmountInvalid = computed(() => {
    const balanceAmount = userBalance.value?.amount ?? 0

    return (
      totalPrice.value <= 0 ||
      (isBalancePayment.value && Number(balanceAmount) < totalPrice.value)
    )
  })

  const validationSchema = z.object({
    paymentType: z.enum(['balance', 'card']),
    totalPrice: z.number().min(0.01, 'Cart is empty'),
    userBalance: z.number().optional(),
    isNewCard: z.boolean(),
    provider: z.nativeEnum(PaymentProvider),
    // Custom card details
    cardNumber: z.string().optional(),
    cardName: z.string().optional(),
    cardExpiry: z.string().optional(),
    cardCvv: z.string().optional()
  }).superRefine((data, ctx) => {
    // Balance Validation
    if (data.paymentType === 'balance') {
      if (!data.userBalance) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: 'User balance information is missing.',
          path: ['paymentType']
        })
      } else if (data.userBalance < data.totalPrice) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: 'Insufficient balance.',
          path: ['paymentType']
        })
      }
    }

    // Custom Card Validation
    if (data.paymentType === 'card' && data.isNewCard && data.provider === PaymentProvider.Custom) {
      if (!data.cardNumber) ctx.addIssue({ code: z.ZodIssueCode.custom, message: 'Card number is required', path: ['cardNumber'] })
      if (!data.cardName) ctx.addIssue({ code: z.ZodIssueCode.custom, message: 'Card name is required', path: ['cardName'] })
      if (!data.cardCvv) ctx.addIssue({ code: z.ZodIssueCode.custom, message: 'CVV is required', path: ['cardCvv'] })

      if (!data.cardExpiry) {
        ctx.addIssue({ code: z.ZodIssueCode.custom, message: 'Expiry date is required', path: ['cardExpiry'] })
      } else {
        const [monthStr, yearStr] = data.cardExpiry.split('/')
        const month = parseInt(monthStr, 10)
        const year = parseInt(yearStr, 10)

        if (!month || !year || month < 1 || month > 12) {
          ctx.addIssue({ code: z.ZodIssueCode.custom, message: 'Invalid date (MM/YY)', path: ['cardExpiry'] })
        }
      }
    }
  })

  const formData = computed(() => ({
    paymentType: paymentMethodType.value,
    totalPrice: totalPrice.value,
    userBalance: userBalance.value ? Number(userBalance.value.amount) : undefined,
    isNewCard: isNewCardSelected.value,
    provider: paymentProvider.value,
    cardNumber: customCardData.value.number,
    cardName: customCardData.value.name,
    cardExpiry: customCardData.value.expiry,
    cardCvv: customCardData.value.cvv
  }))

  const { validate, errors } = useZodValidation(validationSchema, formData)

  function regenerateIdempotencyKey() {
    idempotencyKey.value = generateUUID()
  }

  async function _getPaymentMethodId(): Promise<string | null> {
    if (!isCardPayment.value) {
      return null
    }

    if (!isNewCardSelected.value) {
      return selectedMethod.value
    }

    if (paymentProvider.value === PaymentProvider.Stripe) {
      if (!stripeFormRef.value) {
        throw new Error('Stripe form is not ready.')
      }

      const { paymentMethod, error } = await stripeFormRef.value.createPaymentMethod()

      if (error || !paymentMethod) {
        throw new Error(error?.message || 'Failed to create payment method.')
      }

      return paymentMethod.id
    } else {
      return 'pm_fake_' + generateUUID()
    }
  }

  const handlePayment = async() => {
    if (!validate()) {
      const firstError = Object.values(errors.value)[0]

      if (firstError) {
        snackbar.error({ text: firstError })
      }

      return
    }

    processing.value = true

    try {
      let paymentMethodId: string | null = null
      let provider: string | null = null

      if (isCardPayment.value) {
        try {
          paymentMethodId = await _getPaymentMethodId()
        } catch (e: any) {
          snackbar.error({ text: e.message })
          processing.value = false

          return
        }

        if (!isNewCardSelected.value) {
          const selectedPaymentMethod = paymentMethods.value.find(
            (method) => method.provider_id === selectedMethod.value
          )
          if (selectedPaymentMethod) {
            provider = selectedPaymentMethod.provider
          } else {
            snackbar.error({ text: 'Selected payment method not found.' })
            processing.value = false

            return
          }
        } else {
          provider = paymentProvider.value
        }
      }

      const payload: any = {
        cart: cartItemsForOrder.value,
        payment_type: paymentMethodType.value,
        payment_method_id: paymentMethodId,
        payment_provider: provider,
        save_card: saveCard.value && isNewCardSelected.value
      }

      router.post('/orders', payload, {
        headers: {
          'Idempotency-Key': idempotencyKey.value
        },
        onSuccess: () => {
          clearCart()
          regenerateIdempotencyKey()
          snackbar.success({ text: 'Order placed successfully!' })
          router.visit('/orders')
        },
        onError: (errors) => {
          console.error(errors)
          regenerateIdempotencyKey()
          const message =
              errors.purchase || errors.payment_type || 'Error placing order.'
          snackbar.error({ text: message })
        },
        onFinish: () => {
          processing.value = false
        }
      }
      )
    } catch (e: any) {
      console.error(e)
      snackbar.error({ text: e.message || 'Payment failed' })
      processing.value = false
    }
  }

  return {
    // State
    saveCard,
    processing,
    selectedMethod,
    paymentProvider,
    customCardData,
    paymentMethodType,
    stripeFormRef,

    // Computed
    isCardPayment,
    isBalancePayment,
    isNewCardSelected,
    isStripePaymentProvider,
    isAmountInvalid,
    errors, // Expose errors for UI

    // Methods
    handlePayment,

    // Constants
    NEW_CARD_SELECTION
  }
}

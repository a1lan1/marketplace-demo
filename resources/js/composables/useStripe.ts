import {
  loadStripe,
  Stripe,
  StripeCardElement,
  StripeCardElementOptions
} from '@stripe/stripe-js'
import { onUnmounted, ref } from 'vue'

const stripePromise = loadStripe(import.meta.env.VITE_STRIPE_KEY)
const stripeInstance = ref<Stripe | null>(null)

export function useStripe() {
  const cardElement = ref<StripeCardElement | null>(null)
  const stripeErrors = ref<string | null>(null)

  const initStripe = async(
    containerSelector: string,
    options: StripeCardElementOptions = {}
  ) => {
    if (!stripeInstance.value) {
      stripeInstance.value = await stripePromise
    }

    if (stripeInstance.value) {
      if (cardElement.value) {
        cardElement.value.destroy()
      }

      const elements = stripeInstance.value.elements()
      cardElement.value = elements.create('card', options)
      cardElement.value.mount(containerSelector)
      cardElement.value.on('change', (event) => {
        stripeErrors.value = event.error ? event.error.message : null
      })
    }
  }

  const destroyStripe = () => {
    if (cardElement.value) {
      cardElement.value.destroy()
      cardElement.value = null
    }
  }

  onUnmounted(() => {
    destroyStripe()
  })

  const createPaymentMethod = async() => {
    if (!stripeInstance.value || !cardElement.value) {
      stripeErrors.value ='Stripe has not loaded yet or card element is not initialized.'

      return { paymentMethod: null, error: { message: stripeErrors.value } }
    }

    const { paymentMethod, error } =
      await stripeInstance.value.createPaymentMethod({
        type: 'card',
        card: cardElement.value
      })

    if (error) {
      stripeErrors.value = error.message || 'An unknown error occurred.'

      return { paymentMethod: null, error }
    } else if (paymentMethod) {
      stripeErrors.value = null

      return { paymentMethod, error: null }
    }

    return {
      paymentMethod: null,
      error: { message: 'Unknown error creating payment method.' }
    }
  }

  return {
    stripe: stripeInstance,
    initStripe,
    destroyStripe,
    createPaymentMethod,
    stripeErrors
  }
}

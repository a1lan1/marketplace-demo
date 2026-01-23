import { loadStripe, Stripe, StripeCardElement } from '@stripe/stripe-js'
import { ref } from 'vue'

const stripePromise = loadStripe(import.meta.env.VITE_STRIPE_KEY)
const stripe = ref<Stripe | null>(null)
const cardElement = ref<StripeCardElement | null>(null)
const stripeErrors = ref<string | null>(null)

export function useStripe() {
  const initStripe = async(containerSelector: string) => {
    if (!stripe.value) {
      stripe.value = await stripePromise
    }

    if (stripe.value && !cardElement.value) {
      const elements = stripe.value.elements()
      cardElement.value = elements.create('card')
      cardElement.value.mount(containerSelector)
      cardElement.value.on('change', (event) => {
        stripeErrors.value = event.error ? event.error.message : null
      })
    }
  }

  const createPaymentMethod = async() => {
    if (!stripe.value || !cardElement.value) {
      stripeErrors.value = 'Stripe has not loaded yet or card element is not initialized.'

      return { paymentMethod: null, error: { message: stripeErrors.value } }
    }

    const { paymentMethod, error } = await stripe.value.createPaymentMethod({
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
    stripe,
    initStripe,
    createPaymentMethod,
    stripeErrors
  }
}

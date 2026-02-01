import { PaymentForm, PaymentMethod } from '@/types/payment'
import { defineStore } from 'pinia'

interface PaymentState {
  paymentMethods: PaymentMethod[];
  loading: boolean;
  error: string | null;
  clientSecret: string | null;
}

export const usePaymentStore = defineStore('payment', {
  state: (): PaymentState => ({
    paymentMethods: [],
    loading: false,
    error: null,
    clientSecret: null
  }),

  actions: {
    async fetchPaymentMethods() {
      this.loading = true
      try {
        const response = await this.$axios.get('/payments/methods')
        this.paymentMethods = response.data
      } catch (e: any) {
        this.error =
          e.response?.data?.message || 'Failed to fetch payment methods'
      } finally {
        this.loading = false
      }
    },
    async createSetupIntent(provider: string = 'stripe') {
      this.loading = true
      try {
        const response = await this.$axios.post('/payments/setup-intent', {
          provider
        })
        this.clientSecret = response.data.client_secret

        return response.data
      } catch (e: any) {
        this.error =
          e.response?.data?.message || 'Failed to create setup intent'
        throw e
      } finally {
        this.loading = false
      }
    },
    async savePaymentMethod(paymentMethodId: string, provider: string = 'stripe') {
      this.loading = true
      try {
        const response = await this.$axios.post('/payments/methods', {
          payment_method_id: paymentMethodId,
          provider
        })
        this.paymentMethods.unshift(response.data)

        return response.data
      } catch (e: any) {
        this.error =
          e.response?.data?.message || 'Failed to save payment method'
        throw e
      } finally {
        this.loading = false
      }
    },
    async processPayment(data: PaymentForm, idempotencyKey: string) {
      this.loading = true
      try {
        const response = await this.$axios.post('/payments/purchase', data, {
          headers: { 'Idempotency-Key': idempotencyKey }
        })

        return response.data
      } catch (e: any) {
        this.error = e.response?.data?.message || 'Payment failed'
        throw e
      } finally {
        this.loading = false
      }
    }
  }
})

import { defineStore } from 'pinia'

interface ExchangeRates {
  amount: number;
  base: string;
  date: string;
  rates: Record<string, number>;
}

interface State {
  currentCurrency: string;
  baseCurrency: string;
  rates: Record<string, number>;
  loading: boolean;
}

export const useCurrencyStore = defineStore('currency', {
  state: (): State => ({
    loading: false,
    rates: {},
    baseCurrency: 'USD',
    currentCurrency: 'USD'
  }),

  persist: {
    pick: ['currentCurrency']
  },

  getters: {
    currencies: (state) => Object.keys(state.rates)
  },

  actions: {
    async fetchRates() {
      this.loading = true
      try {
        const { data } = await this.$axios.get<ExchangeRates>('/currency/rates?base=USD')
        this.rates = data.rates
        this.rates['USD'] = 1.0
      } catch (error: any) {
        this.$snackbar.error({
          text: error.message || 'Failed to fetch rates'
        })
      } finally {
        this.loading = false
      }
    },
    setCurrency(currency: string) {
      if (!this.currencies.includes(currency)) return
      this.currentCurrency = currency
    }
  }
})

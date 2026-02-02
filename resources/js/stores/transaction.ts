import type { Pagination, Transaction } from '@/types'
import { defineStore } from 'pinia'

interface State {
  loading: boolean;
  transactions: Transaction[];
  pagination: Pagination<Transaction>['meta'] | null;
}

export const useTransactionStore = defineStore('transaction', {
  state: (): State => ({
    loading: false,
    transactions: [],
    pagination: null
  }),

  actions: {
    async fetchTransactions(page = 1): Promise<void> {
      try {
        this.loading = true
        const { data } = await this.$axios.get<Pagination<Transaction>>(`/balance/transactions?page=${page}`)

        this.setTransactions(data)
      } catch (e: any) {
        this.$snackbar.error({
          text: e.message || 'An error occurred while fetching transactions.'
        })
      } finally {
        this.loading = false
      }
    },
    resetTransactions() {
      this.transactions = []
      this.pagination = null
    },
    setTransactions(response: Pagination<Transaction>) {
      if (response.meta.current_page === 1) {
        this.transactions = response.data
      } else {
        this.transactions.push(...response.data)
      }

      this.pagination = response.meta
    }
  }
})

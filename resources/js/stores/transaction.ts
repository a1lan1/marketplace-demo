import type { Pagination, Transaction, User } from '@/types'
import { generateUUID } from '@/utils/uuid'
import { defineStore } from 'pinia'

interface State {
  loading: boolean;
  transactions: Transaction[];
  pagination: Pagination<Transaction>['meta'] | null;
  recipients: User[];
  loadingRecipients: boolean;
}

export const useTransactionStore = defineStore('transaction', {
  state: (): State => ({
    loading: false,
    transactions: [],
    pagination: null,
    recipients: [],
    loadingRecipients: false
  }),

  actions: {
    async fetchTransactions(page = 1): Promise<void> {
      this.loading = true
      try {
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
    },
    async depositFunds(payload: {
      amount: number;
      currency: string;
      payment_method_id: string;
      provider: string;
    }): Promise<void> {
      this.loading = true
      try {
        await this.$axios.post('/balance/deposit', payload, {
          headers: {
            'Idempotency-Key': generateUUID()
          }
        })
      } catch (e: any) {
        throw e
      } finally {
        this.loading = false
      }
    },
    async withdrawFunds(payload: {
      amount: number;
      currency: string;
      payout_method_id: number;
      description: string;
    }): Promise<void> {
      this.loading = true
      try {
        await this.$axios.post('/balance/withdraw', payload)
      } catch (e: any) {
        throw e
      } finally {
        this.loading = false
      }
    },
    async transferFunds(payload: {
      email: string;
      amount: number;
      currency: string;
      description: string;
    }): Promise<void> {
      this.loading = true
      try {
        await this.$axios.post('/balance/transfer', payload)
      } catch (e: any) {
        throw e
      } finally {
        this.loading = false
      }
    },
    async fetchRecipients(search = '') {
      this.loadingRecipients = true
      try {
        const { data } = await this.$axios.get<User[]>('/balance/recipients', {
          params: { search }
        })
        this.recipients = data
      } catch (e) {
        console.error(e)
        this.recipients = []
      } finally {
        this.loadingRecipients = false
      }
    },
    async addPayoutMethod(payload: {
      provider: string;
      token: string;
      type: string;
    }): Promise<void> {
      this.loading = true
      try {
        await this.$axios.post('/payout-methods', payload)
      } catch (e: any) {
        throw e
      } finally {
        this.loading = false
      }
    }
  }
})

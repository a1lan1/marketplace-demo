import type { Order } from '@/types'
import { defineStore } from 'pinia'

interface State {
  loading: boolean;
  storing: boolean;
  orders: Order[];
  activeOrder: Order | null;
}

export const useOrderStore = defineStore('order', {
  state: (): State => ({
    loading: false,
    storing: false,
    orders: [],
    activeOrder: null
  }),

  actions: {
    async fetchUserOrders(): Promise<void> {
      try {
        this.loading = true
        const { data } = await this.$axios.get<Order[]>('/user/orders')
        this.orders = data
      } catch (e: any) {
        this.$snackbar.error({
          text: e.message || 'An error occurred while fetching orders.'
        })
      } finally {
        this.loading = false
      }
    },
    resetOrders() {
      this.orders = []
    },
    setOrders(initialOrders: Order[]) {
      this.orders = initialOrders
    },
    updateOrderStatus(orderId: number, status: Order['status']) {
      const order = this.orders.find((o) => o.id === orderId)
      if (order) {
        order.status = status
      }
    }
  }
})

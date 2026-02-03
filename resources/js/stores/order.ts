import type { Order, Pagination } from '@/types'
import { defineStore } from 'pinia'

interface State {
  loading: boolean;
  storing: boolean;
  orders: Order[];
  activeOrder: Order | null;
  pagination: Pagination<Order>['meta'] | null;
}

export const useOrderStore = defineStore('order', {
  state: (): State => ({
    loading: false,
    storing: false,
    orders: [],
    activeOrder: null,
    pagination: null
  }),

  actions: {
    async fetchUserOrders(page = 1): Promise<void> {
      try {
        this.loading = true
        const { data } = await this.$axios.get<Pagination<Order>>(`/user/orders?page=${page}`)

        this.setOrders(data)
      } catch (e: any) {
        this.$snackbar.error({
          text: e.message || 'An error occurred while fetching orders.'
        })
      } finally {
        this.loading = false
      }
    },
    async fetchOrder(id: number): Promise<void> {
      if (this.activeOrder?.id === id) {
        return
      }

      try {
        this.loading = true
        this.activeOrder = null

        const { data } = await this.$axios.get<Order>(`/orders/${id}`)
        this.setActiveOrder(data)
      } catch (e: any) {
        this.$snackbar.error({
          text: e.message || 'Failed to load order details.'
        })
      } finally {
        this.loading = false
      }
    },
    setActiveOrder(order: Order) {
      this.activeOrder = order
    },
    clearActiveOrder() {
      this.activeOrder = null
    },
    resetOrders() {
      this.orders = []
    },
    setOrders(orders: Pagination<Order>) {
      if (orders.meta.current_page === 1) {
        this.orders = orders.data
      } else {
        this.orders.push(...orders.data)
      }

      this.pagination = orders.meta
    },
    updateOrderStatus(orderId: number, status: Order['status']) {
      const order = this.orders.find((o) => o.id === orderId)
      if (order) {
        order.status = status
      }
    }
  }
})

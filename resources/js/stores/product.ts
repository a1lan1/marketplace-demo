import type { AutocompleteItem, Product } from '@/types'
import { defineStore } from 'pinia'

interface State {
  loading: boolean;
  items: AutocompleteItem[];
}

export const useProductStore = defineStore('product', {
  state: (): State => ({
    loading: false,
    items: []
  }),

  actions: {
    async searchProducts(query: string): Promise<void> {
      if (query.trim().length < 3) {
        this.items = []

        return
      }

      this.loading = true
      try {
        const { data } = await this.$axios.get<Product[]>('/catalog/search', {
          params: { query }
        })
        this.items = data.map((product: Product) => ({
          title: product.name,
          value: product.id,
          product: product
        }))
      } catch (e: any) {
        this.$snackbar.error({
          text: e.message || 'An error occurred while fetching products.'
        })
        this.items = []
      } finally {
        this.loading = false
      }
    }
  }
})

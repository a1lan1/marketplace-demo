import type { CartItem, Product } from '@/types'
import { defineStore } from 'pinia'
import { computed, ref } from 'vue'

export const useCartStore = defineStore(
  'cart',
  () => {
    const items = ref<CartItem[]>([])

    const totalItems = computed(() => {
      return items.value.reduce((total, item) => total + item.quantity, 0)
    })

    const totalPrice = computed(() => {
      return items.value.reduce(
        (total, item) => total + Number(item.price) * item.quantity,
        0
      )
    })

    function addToCart(product: Product) {
      const existingItem = items.value.find(
        (item) => item.product_id === product.id
      )

      if (existingItem) {
        existingItem.quantity++
      } else {
        items.value.push({
          product_id: product.id,
          name: product.name,
          price: product.price,
          stock: product.stock,
          quantity: 1,
          cover_image: product.cover_image
        })
      }
    }

    function removeFromCart(productId: number) {
      const index = items.value.findIndex(
        (item) => item.product_id === productId
      )
      if (index !== -1) {
        items.value.splice(index, 1)
      }
    }

    function clearCart() {
      items.value = []
    }

    return {
      items,
      totalItems,
      totalPrice,
      addToCart,
      removeFromCart,
      clearCart
    }
  },
  {
    persist: true
  }
)

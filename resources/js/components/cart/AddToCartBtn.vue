<script setup lang="ts">
import { useCartStore } from '@/stores/cart'
import type { Product } from '@/types'
import { storeToRefs } from 'pinia'
import { computed } from 'vue'

const props = defineProps<{
  product: Product;
  block?: boolean;
}>()

const cartStore = useCartStore()
const { items } = storeToRefs(cartStore)
const { addToCart } = cartStore

const getCartQuantity = (productId: number) => {
  const item = items.value.find((item) => item.product_id === productId)

  return item ? item.quantity : 0
}

const isAddToCartDisabled = computed(() => {
  return props.product.stock <= getCartQuantity(props.product.id)
})
</script>

<template>
  <v-btn
    :color="isAddToCartDisabled ? 'danger' : 'primary'"
    :disabled="isAddToCartDisabled"
    variant="elevated"
    :block="block"
    prepend-icon="mdi-cart"
    :class="isAddToCartDisabled ? 'cursor-not-allowed' : 'cursor-pointer'"
    @click.prevent="addToCart(product)"
  >
    {{ isAddToCartDisabled ? 'No more stock' : 'Add to Cart' }}
  </v-btn>
</template>

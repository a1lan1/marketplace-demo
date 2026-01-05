<script setup lang="ts">
import { useCartStore } from '@/stores/cart'
import type { Product } from '@/types'
import { storeToRefs } from 'pinia'
import { computed } from 'vue'

const props = defineProps<{
  product: Product;
  block?: boolean;
  iconOnly?: boolean;
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
    :color="isAddToCartDisabled ? 'danger' : 'success'"
    :disabled="isAddToCartDisabled"
    variant="flat"
    :block="block"
    :icon="iconOnly"
    :size="iconOnly ? 'x-small' : 'small'"
    :class="isAddToCartDisabled ? 'cursor-not-allowed' : 'cursor-pointer'"
    @click.prevent="addToCart(product)"
  >
    <v-icon
      icon="mdi-cart"
      :class="{ 'mr-1': !iconOnly }"
    />

    <template v-if="!iconOnly">
      {{ isAddToCartDisabled ? 'No more stock' : 'Add to Cart' }}
    </template>
  </v-btn>
</template>

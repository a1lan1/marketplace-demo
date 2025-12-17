<script setup lang="ts">
import { index as checkoutIndex } from '@/routes/checkout'
import { useCartStore } from '@/stores/cart'
import type { CartItem } from '@/types'
import { formatCurrency } from '@/utils/formatters'
import { router } from '@inertiajs/vue3'
import { storeToRefs } from 'pinia'

const cartStore = useCartStore()
const { items, totalItems, totalPrice } = storeToRefs(cartStore)
const { removeFromCart } = cartStore

const goToCheckout = () => {
  router.get(checkoutIndex().url)
}

const getItemTotalPrice = (item: CartItem): string => {
  return formatCurrency(Number(item.price) * item.quantity)
}
</script>

<template>
  <v-menu location="bottom">
    <template #activator="{ props }">
      <v-btn
        icon
        v-bind="props"
      >
        <v-badge
          :content="totalItems"
          color="error"
          :model-value="totalItems > 0"
        >
          <v-icon>mdi-cart</v-icon>
        </v-badge>
      </v-btn>
    </template>

    <v-card min-width="300">
      <v-list v-if="items.length > 0">
        <v-list-item
          v-for="item in items"
          :key="item.product_id"
          :title="item.name"
          :subtitle="`Quantity: ${item.quantity} - ${getItemTotalPrice(item)}`"
          density="compact"
        >
          <template #prepend>
            <v-img
              :src="item.cover_image"
              width="35"
              class="mr-2"
              rounded
            />
          </template>

          <template #append>
            <v-btn
              color="grey-lighten-1"
              icon="mdi-close"
              variant="text"
              @click.stop="removeFromCart(item.product_id)"
            />
          </template>
        </v-list-item>
      </v-list>

      <v-alert
        v-else
        type="info"
        variant="tonal"
        class="ma-2"
      >
        Your cart is empty.
      </v-alert>

      <v-divider v-if="items.length > 0" />

      <v-card-actions v-if="items.length > 0">
        <div class="font-weight-bold">
          Total: {{ formatCurrency(totalPrice) }}
        </div>
        <v-spacer />
        <v-btn
          color="primary"
          variant="tonal"
          @click="goToCheckout"
        >
          Checkout
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-menu>
</template>

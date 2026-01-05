<script setup lang="ts">
import Money from '@/components/common/Money.vue'
import { index as checkoutIndex } from '@/routes/checkout'
import { useCartStore } from '@/stores/cart'
import { router } from '@inertiajs/vue3'
import { storeToRefs } from 'pinia'

const cartStore = useCartStore()
const { items, totalItems, totalPrice } = storeToRefs(cartStore)
const { removeFromCart } = cartStore

const getItemTotal = (item: any) => {
  const price =
    typeof item.price === 'object' && 'amount' in item.price
      ? item.price.amount / 100
      : Number(item.price)

  return price * item.quantity
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
          density="compact"
        >
          <template #subtitle>
            Quantity: {{ item.quantity }} -
            <Money
              :value="getItemTotal(item)"
              size="small"
            />
          </template>

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

      <v-card-actions
        v-if="items.length > 0"
        class="flex justify-between"
      >
        <div class="text-xl">
          Total: <Money :value="totalPrice" />
        </div>

        <v-btn
          color="success"
          variant="tonal"
          size="small"
          @click="router.get(checkoutIndex().url)"
        >
          Checkout
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-menu>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { index as ordersIndex, store as storeOrder } from '@/routes/orders'
import { useCartStore } from '@/stores/cart'
import type { CartItem } from '@/types'
import { formatCurrency } from '@/utils/formatters'
import { Head, router, useForm } from '@inertiajs/vue3'
import { storeToRefs } from 'pinia'

const cartStore = useCartStore()
const { items, totalPrice } = storeToRefs(cartStore)
const { clearCart, removeFromCart } = cartStore

interface CheckoutForm {
  cart: Pick<CartItem, 'product_id' | 'quantity'>[];
  purchase?: string;
}

const form = useForm<CheckoutForm>({
  cart: []
})

const placeOrder = () => {
  form.cart = items.value.map((item) => ({
    product_id: item.product_id,
    quantity: item.quantity
  }))

  form.post(storeOrder().url, {
    onSuccess: () => {
      clearCart()
      router.visit(ordersIndex().url)
    },
    onError: (errors) => {
      console.error(errors)
    }
  })
}

const getItemTotalPrice = (item: CartItem): string => {
  return formatCurrency(Number(item.price) * item.quantity)
}

const cartHeaders = [
  { title: '', key: 'cover_image', align: 'start' },
  { title: 'Product', key: 'name', align: 'start' },
  { title: 'Price', key: 'price', align: 'end' },
  { title: 'Quantity', key: 'quantity' },
  { title: 'Total', key: 'total', align: 'end' },
  { title: 'Actions', key: 'actions', sortable: false, align: 'end' }
] as const
</script>

<template>
  <Head title="Checkout" />

  <AppLayout>
    <template #header>
      <h2 class="text-h5">
        Checkout
      </h2>
    </template>

    <v-container>
      <v-card>
        <v-card-title>Order Summary</v-card-title>
        <v-card-text>
          <v-alert
            v-if="'purchase' in form.errors"
            type="error"
            variant="tonal"
            class="mb-4"
          >
            {{ form.errors.purchase }}
          </v-alert>

          <v-data-table
            v-if="items.length > 0"
            :headers="cartHeaders"
            :items="items"
            item-value="product_id"
            hide-default-footer
            class="elevation-1"
            density="compact"
          >
            <template #[`item.cover_image`]="{ item }">
              <v-img
                :src="item.cover_image"
                width="100"
                rounded
                class="my-2"
              />
            </template>
            <template #[`item.name`]="{ item }">
              <div class="text-md">
                {{ item.name }}
              </div>
            </template>

            <template #[`item.price`]="{ item }">
              {{ formatCurrency(Number(item.price)) }}
            </template>

            <template #[`item.quantity`]="{ item }">
              <v-number-input
                v-model="item.quantity"
                control-variant="stacked"
                :min="1"
                :max="item.stock"
                density="compact"
                hide-details
                max-width="100"
              />
            </template>

            <template #[`item.total`]="{ item }">
              {{ getItemTotalPrice(item) }}
            </template>

            <template #[`item.actions`]="{ item }">
              <v-btn
                color="grey-lighten-1"
                icon="mdi-delete"
                variant="text"
                @click="removeFromCart(item.product_id)"
              />
            </template>
          </v-data-table>
          <v-alert
            v-else
            type="info"
            variant="tonal"
          >
            Your cart is empty.
          </v-alert>

          <v-divider
            v-if="items.length > 0"
            class="my-4"
          />

          <div
            v-if="items.length > 0"
            class="text-h6 text-right"
          >
            Total: {{ formatCurrency(totalPrice) }}
          </div>
        </v-card-text>

        <v-card-actions v-if="items.length > 0">
          <v-spacer />
          <v-btn
            color="success"
            size="large"
            variant="elevated"
            prepend-icon="mdi-currency-usd"
            :loading="form.processing"
            :disabled="form.processing"
            @click="placeOrder"
          >
            Place Order
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-container>
  </AppLayout>
</template>

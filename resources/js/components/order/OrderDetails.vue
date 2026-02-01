<script setup lang="ts">
import { StatusVariant } from '@/enums/OrderStatus'
import type { Order } from '@/types'
import { formatCurrency, formatDate } from '@/utils/formatters'

interface Props {
  order: Order;
}

defineProps<Props>()

const orderStatusColor = (status: Order['status']) => {
  return StatusVariant[status] ?? 'info'
}
</script>

<template>
  <v-card-text>
    <v-row>
      <v-col
        cols="12"
        md="6"
      >
        <div class="text-subtitle-1 font-weight-bold">
          General Info
        </div>
        <v-list density="compact">
          <v-list-item>
            <template #prepend>
              <v-icon icon="mdi-calendar" />
            </template>
            <v-list-item-title>Date</v-list-item-title>
            <v-list-item-subtitle>
              {{ formatDate(order.created_at) }}
            </v-list-item-subtitle>
          </v-list-item>
          <v-list-item>
            <template #prepend>
              <v-icon icon="mdi-list-status" />
            </template>
            <v-list-item-title>Status</v-list-item-title>
            <v-list-item-subtitle>
              <v-chip
                size="small"
                :color="orderStatusColor(order.status)"
              >
                {{ order.status }}
              </v-chip>
            </v-list-item-subtitle>
          </v-list-item>
          <v-list-item>
            <template #prepend>
              <v-icon icon="mdi-credit-card" />
            </template>
            <v-list-item-title>Payment Method</v-list-item-title>
            <v-list-item-subtitle>
              {{ order.payment_method || 'N/A' }}
            </v-list-item-subtitle>
          </v-list-item>
        </v-list>
      </v-col>

      <v-col
        cols="12"
        md="6"
      >
        <div class="text-subtitle-1 font-weight-bold">
          Payment Info
        </div>
        <v-list density="compact">
          <v-list-item>
            <template #prepend>
              <v-icon icon="mdi-cash" />
            </template>
            <v-list-item-title>Total Amount</v-list-item-title>
            <v-list-item-subtitle class="text-h6 text-primary">
              {{ formatCurrency(order.total_amount) }}
            </v-list-item-subtitle>
          </v-list-item>
          <v-list-item v-if="order.buyer">
            <template #prepend>
              <v-icon icon="mdi-account" />
            </template>
            <v-list-item-title>Buyer</v-list-item-title>
            <v-list-item-subtitle>
              {{ order.buyer.name }}
            </v-list-item-subtitle>
          </v-list-item>
        </v-list>
      </v-col>
    </v-row>

    <v-divider class="my-4" />

    <div class="text-h6 mb-2">
      Products
    </div>
    <v-table>
      <thead>
        <tr>
          <th>Product</th>
          <th>Price</th>
          <th>Quantity</th>
          <th class="text-right">
            Total
          </th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="product in order.products"
          :key="product.id"
        >
          <td>
            <div class="d-flex align-center">
              <v-avatar
                size="32"
                class="mr-2"
                rounded
              >
                <v-img
                  :src="product.cover_image"
                  cover
                />
              </v-avatar>
              <div>
                <div>{{ product.name }}</div>
                <div
                  v-if="product.seller"
                  class="text-caption text-medium-emphasis"
                >
                  Seller: {{ product.seller.name }}
                </div>
              </div>
            </div>
          </td>
          <td>
            {{ formatCurrency(product.purchase_price ?? product.price) }}
          </td>
          <td>{{ product.quantity }}</td>
          <td class="text-right">
            {{ formatCurrency(product.line_total) }}
          </td>
        </tr>
        <tr v-if="!order.products || order.products.length === 0">
          <td
            colspan="4"
            class="text-medium-emphasis text-center"
          >
            No products found in this order.
          </td>
        </tr>
      </tbody>
    </v-table>
  </v-card-text>
</template>

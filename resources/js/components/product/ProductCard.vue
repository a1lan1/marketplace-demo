<script setup lang="ts">
import type { Product } from '@/types'
import { show as showProduct } from '@/routes/products'
import { formatCurrency } from '@/utils/formatters'
import AddToCartBtn from '@/components/cart/AddToCartBtn.vue'

defineProps<{
  product: Product;
}>()
</script>

<template>
  <v-card :href="showProduct(product.id).url">
    <v-img
      :src="product.cover_image"
      height="200px"
      cover
    >
      <v-card-actions>
        <v-spacer />
        <v-chip
          color="primary"
          variant="flat"
        >
          {{ formatCurrency(product.price) }}
        </v-chip>
      </v-card-actions>
    </v-img>

    <v-card-title>{{ product.name }}</v-card-title>
    <v-card-text>{{ product.description }}</v-card-text>

    <v-card-actions>
      <v-chip
        :color="product.stock > 0 ? 'success' : 'error'"
        variant="flat"
        label
        class="text-uppercase"
      >
        {{ product.stock > 0 ? `In Stock: ${product.stock}` : 'Out of Stock' }}
      </v-chip>

      <v-spacer />

      <AddToCartBtn :product />
    </v-card-actions>
  </v-card>
</template>

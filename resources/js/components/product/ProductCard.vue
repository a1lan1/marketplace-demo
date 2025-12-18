<script setup lang="ts">
import AddToCartBtn from '@/components/cart/AddToCartBtn.vue'
import { show as showProduct } from '@/routes/products'
import type { Product } from '@/types'
import { formatCurrency } from '@/utils/formatters'

defineProps<{
  product: Product;
  compact?: boolean;
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
        <v-chip
          :color="product.stock > 0 ? 'success' : 'error'"
          variant="flat"
          size="small"
          label
        >
          {{
            product.stock > 0 ? `In Stock: ${product.stock}` : 'Out of Stock'
          }}
        </v-chip>
        <v-spacer />
        <v-chip
          color="primary"
          variant="flat"
        >
          {{ formatCurrency(Number(product.price)) }}
        </v-chip>
      </v-card-actions>
    </v-img>

    <v-card-title>{{ product.name }}</v-card-title>
    <v-card-text v-if="!compact">
      {{ product.description }}
    </v-card-text>

    <v-card-actions>
      <v-spacer />

      <AddToCartBtn
        :product
        :icon-only="compact"
      />
    </v-card-actions>
  </v-card>
</template>

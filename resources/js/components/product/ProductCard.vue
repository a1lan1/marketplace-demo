<script setup lang="ts">
import AddToCartBtn from '@/components/cart/AddToCartBtn.vue'
import Money from '@/components/common/Money.vue'
import ProductSeller from '@/components/product/ProductSeller.vue'
import ProductStockStatus from '@/components/product/ProductStockStatus.vue'
import { show as showProduct } from '@/routes/products'
import type { Product } from '@/types'

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
        <ProductSeller
          v-if="product.seller"
          :seller="product.seller"
          variant="elevated"
          chip
          with-avatar
        />
        <v-spacer />
        <Money
          :value="product.price"
          size="small"
          chip
          variant="elevated"
        />
      </v-card-actions>
    </v-img>

    <v-card-title>{{ product.name }}</v-card-title>
    <v-card-text v-if="!compact">
      {{ product.description }}
    </v-card-text>

    <v-card-actions>
      <ProductStockStatus :stock="product.stock" />
      <v-spacer />
      <AddToCartBtn
        :product
        :icon-only="compact"
      />
    </v-card-actions>
  </v-card>
</template>

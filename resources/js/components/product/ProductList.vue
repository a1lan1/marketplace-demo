<script setup lang="ts">
import AddToCartBtn from '@/components/cart/AddToCartBtn.vue'
import Money from '@/components/common/Money.vue'
import ProductSeller from '@/components/product/ProductSeller.vue'
import ProductStockStatus from '@/components/product/ProductStockStatus.vue'
import { show as showProduct } from '@/routes/products'
import type { Product } from '@/types'
import { Link } from '@inertiajs/vue3'

defineProps<{
  products: Product[];
}>()
</script>

<template>
  <v-list lines="three">
    <v-list-item
      v-for="product in products"
      :key="product.id"
      :href="showProduct(product.id).url"
      border
    >
      <template #prepend>
        <v-avatar
          rounded="0"
          size="80"
          class="mr-4"
        >
          <v-img
            :src="product.cover_image"
            :alt="product.name"
            cover
          />
        </v-avatar>
      </template>

      <v-list-item-title>
        <div class="flex justify-between align-middle">
          <Link
            :href="showProduct(product.id).url"
            class="text-decoration-none text-primary"
          >
            {{ product.name }}
          </Link>
          <Money
            :value="product.price"
            chip
          />
        </div>
      </v-list-item-title>

      <v-list-item-subtitle class="text-truncate-2-lines text-medium-emphasis">
        {{ product.description }}
      </v-list-item-subtitle>

      <v-list-item-action class="mt-2">
        <ProductSeller
          v-if="product.seller"
          :seller="product.seller"
          chip
          with-avatar
          class="mb-1"
        />
        <v-spacer />
        <ProductStockStatus
          :stock="product.stock"
          class="ml-2"
        />
      </v-list-item-action>

      <template #append>
        <AddToCartBtn
          :product="product"
          class="ml-4"
        />
      </template>
    </v-list-item>
  </v-list>
</template>

<style scoped>
.text-truncate-2-lines {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  white-space: normal;
}
</style>

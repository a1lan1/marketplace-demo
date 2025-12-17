<script setup lang="ts">
import ProductAutocomplete from '@/components/product/ProductAutocomplete.vue'
import ProductCard from '@/components/product/ProductCard.vue'
import RecommendedProducts from '@/components/product/RecommendedProducts.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { BreadcrumbItem, Pagination, Product } from '@/types'
import { Head } from '@inertiajs/vue3'

defineProps<{
  products: Pagination<Product>;
  recommendations: Product[];
}>()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Catalog',
    href: '#'
  }
]
</script>

<template>
  <Head title="Catalog" />

  <AppLayout :breadcrumbs>
    <template #header>
      <h2 class="text-h5">
        Catalog
      </h2>
    </template>

    <v-container>
      <ProductAutocomplete />

      <!-- Product Grid -->
      <v-row v-if="products.data && products.data.length > 0">
        <v-col
          v-for="product in products.data"
          :key="product.id"
          cols="12"
          sm="6"
          md="4"
          lg="3"
        >
          <ProductCard :product="product" />
        </v-col>
      </v-row>

      <v-alert
        v-if="products.data.length === 0"
        type="info"
        variant="tonal"
      >
        No products found.
      </v-alert>

      <!-- Recommendations Section -->
      <RecommendedProducts
        :products="recommendations"
        class="mt-8"
      />
    </v-container>
  </AppLayout>
</template>

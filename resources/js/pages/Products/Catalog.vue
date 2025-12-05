<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import ProductAutocomplete from '@/components/product/ProductAutocomplete.vue'
import ProductCard from '@/components/product/ProductCard.vue'
import type { Product, Pagination, BreadcrumbItem } from '@/types'

defineProps<{
  products: Pagination<Product>;
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
    </v-container>
  </AppLayout>
</template>

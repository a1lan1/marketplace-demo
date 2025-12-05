<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import {
  index as productsIndex,
  update as productUpdate
} from '@/routes/products'
import ProductForm from '@/components/product/ProductForm.vue'
import type { BreadcrumbItem, Product } from '@/types'

const props = defineProps<{
  product: Product;
}>()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'My Products',
    href: productsIndex().url
  },
  {
    title: `Edit Product: ${props.product.name}`,
    href: '#'
  }
]
</script>

<template>
  <Head title="Edit Product" />

  <AppLayout :breadcrumbs>
    <template #header>
      <h2 class="text-h5">
        Edit Product: {{ product.name }}
      </h2>
    </template>

    <v-container>
      <v-card>
        <v-card-text>
          <ProductForm
            :initial-data="product"
            :url="productUpdate(product.id).url"
            method="put"
          />
        </v-card-text>
      </v-card>
    </v-container>
  </AppLayout>
</template>

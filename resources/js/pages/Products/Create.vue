<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { store as productStore } from '@/routes/products'
import ProductForm from '@/components/product/ProductForm.vue'
import type { BreadcrumbItem } from '@/types'
import { index as productsIndex } from '@/routes/products'

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'My Products',
    href: productsIndex().url
  },
  {
    title: 'New Product',
    href: '#'
  }
]
</script>

<template>
  <Head title="Add Product" />

  <AppLayout :breadcrumbs>
    <template #header>
      <h2 class="text-h5">
        Add New Product
      </h2>
    </template>

    <v-container>
      <v-card>
        <v-card-text>
          <ProductForm
            :url="productStore().url"
            method="post"
          />
        </v-card-text>
      </v-card>
    </v-container>
  </AppLayout>
</template>

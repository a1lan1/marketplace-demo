<script setup lang="ts">
import ProductForm from '@/components/product/ProductForm.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import {
  index as productsIndex,
  store as productStore
} from '@/routes/products'
import type { BreadcrumbItem } from '@/types'
import { Head } from '@inertiajs/vue3'

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

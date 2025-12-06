<script setup lang="ts">
import AddToCartBtn from '@/components/cart/AddToCartBtn.vue'
import ProductAutocomplete from '@/components/product/ProductAutocomplete.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { catalog as catalogIndex } from '@/routes/products'
import type { BreadcrumbItem, Product } from '@/types'
import { formatCurrency } from '@/utils/formatters'
import { Head } from '@inertiajs/vue3'

const props = defineProps<{
  product: Product;
}>()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Catalog',
    href: catalogIndex().url
  },
  {
    title: props.product.name,
    href: '#'
  }
]
</script>

<template>
  <Head :title="product.name" />

  <AppLayout :breadcrumbs>
    <template #header>
      <h2 class="text-h5">
        {{ product.name }}
      </h2>
    </template>

    <v-container>
      <ProductAutocomplete />

      <v-row>
        <!-- Product Image -->
        <v-col
          cols="12"
          md="6"
        >
          <v-img
            :src="product.cover_image"
            :alt="product.name"
            aspect-ratio="1.5"
            cover
            class="rounded-lg border"
          />
        </v-col>

        <!-- Product Details -->
        <v-col
          cols="12"
          md="6"
        >
          <v-card flat>
            <v-card-title class="text-h4 font-weight-bold mb-2">
              {{ product.name }}
            </v-card-title>
            <v-card-subtitle class="text-subtitle-1">
              Seller: {{ product.seller?.name || 'N/A' }}
            </v-card-subtitle>

            <v-card-text>
              <p class="text-body-1 mb-4">
                {{ product.description || 'No description available.' }}
              </p>

              <v-divider class="my-4" />

              <div class="align-center mb-4 flex justify-between">
                <span class="text-h5 font-weight-bold">Price: {{ formatCurrency(product.price ?? 0) }}</span>
                <v-chip
                  :color="product.stock > 0 ? 'success' : 'error'"
                  label
                  class="text-uppercase"
                >
                  {{ product.stock > 0 ? `In Stock: ${product.stock}` : 'Out of Stock' }}
                </v-chip>
              </div>

              <AddToCartBtn
                :product
                block
              />
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </AppLayout>
</template>

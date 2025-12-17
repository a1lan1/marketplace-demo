<script setup lang="ts">
import ProductCard from '@/components/product/ProductCard.vue'
import { api } from '@/plugins/axios'
import type { Product } from '@/types'
import { onMounted, ref } from 'vue'

const products = ref<Product[]>([])
const loading = ref(true)

async function fetchRecommendedProducts() {
  try {
    const { data } = await api.get('/products/recommendations')
    products.value = data
  } catch (error) {
    console.error('Error fetching recommended products:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchRecommendedProducts()
})
</script>

<template>
  <v-card
    class="mb-4"
    :loading="loading"
  >
    <v-card-title>
      <v-icon
        start
        icon="mdi-star-face"
        color="primary"
      />
      Recommended for You
    </v-card-title>
    <v-card-text>
      <v-row v-if="!loading && products.length > 0">
        <v-col
          v-for="product in products"
          :key="product.id"
          cols="12"
          sm="6"
          :md="products.length > 3 ? 2 : 4"
        >
          <ProductCard
            :product="product"
            compact
          />
        </v-col>
      </v-row>
      <v-alert
        v-else-if="!loading && products.length === 0"
        type="info"
        variant="tonal"
      >
        We're still learning your preferences. Check out our catalog to see
        personalized recommendations!
      </v-alert>
    </v-card-text>
  </v-card>
</template>

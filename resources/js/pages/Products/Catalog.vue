<script setup lang="ts">
import ProductAutocomplete from '@/components/product/ProductAutocomplete.vue'
import ProductCard from '@/components/product/ProductCard.vue'
import ProductList from '@/components/product/ProductList.vue'
import RecommendedProducts from '@/components/product/RecommendedProducts.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { BreadcrumbItem, Pagination, Product } from '@/types'
import { Head, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'

defineProps<{
  products: Pagination<Product>;
  recommendations: Product[];
}>()

const page = usePage()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Catalog',
    href: '#'
  }
]

type ViewMode = 'grid' | 'list';

const viewMode = ref<ViewMode>('grid')
</script>

<template>
  <Head title="Catalog" />

  <AppLayout :breadcrumbs>
    <v-container>
      <div class="flex gap-2">
        <ProductAutocomplete />
        <VBtnToggle
          v-model="viewMode"
          variant="outlined"
          divided
          density="compact"
          border
          mandatory
        >
          <v-btn
            value="grid"
            icon="mdi-view-grid"
            density="comfortable"
            active-color="success"
          />
          <v-btn
            value="list"
            icon="mdi-view-list"
            density="comfortable"
            active-color="success"
          />
        </VBtnToggle>
      </div>

      <v-container v-if="products?.data.length > 0">
        <v-row v-if="viewMode === 'grid'">
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
        <ProductList
          v-else-if="viewMode === 'list'"
          :products="products.data"
        />
      </v-container>

      <v-alert
        v-if="products.data.length === 0"
        type="info"
        variant="tonal"
      >
        No products found.
      </v-alert>

      <RecommendedProducts
        v-if="page.props.auth.user"
        :products="recommendations"
        class="mt-4"
      />
    </v-container>
  </AppLayout>
</template>

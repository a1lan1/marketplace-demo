<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { watchDebounced } from '@vueuse/core'
import { api } from '@/plugins/axios'
import { show as productShow } from '@/routes/products'
import type { Product, AutocompleteItem } from '@/types'

const searchQuery = ref('')
const items = ref<AutocompleteItem[]>([])
const loading = ref(false)

async function searchProducts() {
  if (searchQuery.value.trim().length < 3) {
    items.value = []

    return
  }

  loading.value = true
  try {
    const { data } = await api.get('/catalog/search', {
      params: { query: searchQuery.value }
    })
    items.value = data.map((product: Product) => ({
      title: product.name,
      value: product.id,
      product: product
    }))
  } catch (error) {
    console.error('Error fetching autocomplete suggestions:', error)
  } finally {
    loading.value = false
  }
}

watchDebounced(searchQuery, searchProducts, { debounce: 300 })

function onSelect(selectedItem: AutocompleteItem | null) {
  if (selectedItem?.product) {
    router.visit(productShow(selectedItem.product.id).url)
  }
}
</script>

<template>
  <v-autocomplete
    v-model:search="searchQuery"
    :items="items"
    :loading="loading"
    :no-data-text="searchQuery ? 'Products not found' : 'Enter a search query'"
    variant="solo"
    item-title="title"
    item-value="value"
    label="Search products..."
    placeholder="Start typing..."
    prepend-inner-icon="mdi-magnify"
    clearable
    hide-no-data
    return-object
    density="compact"
    @update:model-value="onSelect"
  />
</template>

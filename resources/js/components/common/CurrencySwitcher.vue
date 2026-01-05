<script setup lang="ts">
import { useCurrencyStore } from '@/stores/currency'
import { storeToRefs } from 'pinia'
import { onMounted } from 'vue'

const currencyStore = useCurrencyStore()
const { loading, currencies, currentCurrency } = storeToRefs(currencyStore)
const { fetchRates, setCurrency } = currencyStore

onMounted(() => {
  if (!currencies.value.length) {
    fetchRates()
  }
})
</script>

<template>
  <v-menu>
    <template #activator="{ props }">
      <v-btn
        color="primary"
        variant="tonal"
        v-bind="props"
        :loading="loading"
        :disabled="!currencies.length"
        prepend-icon="mdi-cash-multiple"
      >
        {{ currentCurrency }}
      </v-btn>
    </template>

    <v-list
      density="compact"
      max-height="300"
    >
      <v-list-item
        v-for="currency in currencies"
        :key="currency"
        :value="currency"
        :active="currency === currentCurrency"
        @click="setCurrency(currency)"
      >
        <v-list-item-title>{{ currency }}</v-list-item-title>
      </v-list-item>
    </v-list>
  </v-menu>
</template>

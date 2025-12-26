<script setup lang="ts">
import { MoneyData } from '@/types'
import { formatCurrency } from '@/utils/formatters'

defineProps<{
  price: MoneyData | number | string;
  size?: 'small' | 'medium' | 'large' | 'x-large';
  chip?: boolean;
  variant?: 'flat' | 'text' | 'elevated' | 'tonal' | 'outlined' | 'plain';
}>()
</script>

<template>
  <v-chip
    v-if="chip"
    :size
    :variant
    label
    color="success"
    prepend-icon="mdi-currency-usd"
  >
    {{ formatCurrency(price, 'USD', false) }}
  </v-chip>
  <span
    v-else
    :class="[
      'font-weight-bold',
      'text-primary',
      {
        'text-body-2': size === 'small',
        'text-h6': size === 'medium' || !size,
        'text-h5': size === 'large',
        'text-h4': size === 'x-large',
      },
    ]"
  >
    {{ formatCurrency(price) }}
  </span>
</template>

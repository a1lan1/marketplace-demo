<script setup lang="ts">
import Money from '@/components/common/Money.vue'
import { MoneyData } from '@/types'

defineProps<{
  balance: MoneyData;
}>()
</script>

<template>
  <v-chip
    label
    color="success"
  >
    Balance:
    <Money
      :value="balance"
      size="small"
      variant="text"
      class="ml-1"
      color="white"
    />
  </v-chip>
</template>

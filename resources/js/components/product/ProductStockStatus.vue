<script setup lang="ts">
withDefaults(
  defineProps<{
    stock: number;
    variant?: 'flat' | 'text' | 'elevated' | 'tonal' | 'outlined' | 'plain';
    color?: string;
  }>(),
  {
    variant: undefined,
    color: 'success'
  }
)
</script>

<template>
  <v-chip
    :color="stock > 0 ? color : 'error'"
    :variant="variant"
    size="small"
    label
  >
    {{ stock > 0 ? `In Stock: ${stock}` : 'Out of Stock' }}
  </v-chip>
</template>

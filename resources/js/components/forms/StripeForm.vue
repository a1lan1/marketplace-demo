<script setup lang="ts">
import { useStripe } from '@/composables/useStripe'
import { onMounted } from 'vue'

const { initStripe, createPaymentMethod } = useStripe()

onMounted(() => {
  initStripe('#stripe-card-element')
})

// Expose the createPaymentMethod function to the parent component
defineExpose({
  createPaymentMethod
})
</script>

<template>
  <div class="pa-4 my-4 rounded border">
    <div id="stripe-card-element" />
  </div>
</template>

<style scoped>
#stripe-card-element {
  padding: 12px;
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  background-color: white;
}
</style>

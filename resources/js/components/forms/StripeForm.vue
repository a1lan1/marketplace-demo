<script setup lang="ts">
import { useStripe } from '@/composables/useStripe'
import { onMounted } from 'vue'

const props = defineProps({
  elementId: {
    type: String,
    default: 'stripe-card-element'
  },
  options: {
    type: Object,
    default: () => ({
      style: {
        base: {
          color: '#32325d',
          fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
          fontSmoothing: 'antialiased',
          fontSize: '16px',
          '::placeholder': {
            color: '#aab7c4'
          }
        },
        invalid: {
          color: '#fa755a',
          iconColor: '#fa755a'
        }
      }
    })
  }
})

const { initStripe, createPaymentMethod } = useStripe()

onMounted(() => {
  initStripe('#' + props.elementId, props.options)
})

// Expose the createPaymentMethod function to the parent component
defineExpose({
  createPaymentMethod
})
</script>

<template>
  <div class="stripe-container pa-4 my-4 rounded border">
    <div :id="elementId" />
  </div>
</template>

<style scoped>
.stripe-container {
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  background-color: white;
}
</style>

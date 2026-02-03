<script setup lang="ts">
import OrderDetails from '@/components/order/OrderDetails.vue'
import { useOrderStore } from '@/stores/order'
import { watch } from 'vue'

const props = defineProps<{
  modelValue: boolean;
  orderId: number | null;
}>()

const emit = defineEmits(['update:modelValue'])

const orderStore = useOrderStore()
const { fetchOrder, clearActiveOrder } = orderStore

watch(
  () => props.modelValue,
  async(isOpen) => {
    if (isOpen && props.orderId) {
      await fetchOrder(props.orderId)
    } else if (!isOpen) {
      clearActiveOrder()
    }
  }
)

const closeDialog = () => {
  emit('update:modelValue', false)
}
</script>

<template>
  <v-dialog
    :model-value="modelValue"
    max-width="800"
    @update:model-value="closeDialog"
  >
    <v-card v-if="orderId">
      <v-card-title> Order #{{ orderId }} Details </v-card-title>

      <OrderDetails />

      <v-card-actions>
        <v-spacer />
        <v-btn
          color="primary"
          variant="text"
          @click="closeDialog"
        >
          Close
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup lang="ts">
import ChatOrderList from '@/components/chat/ChatOrderList.vue'
import ChatPanel from '@/components/chat/ChatPanel.vue'
import type { Order, Pagination } from '@/types'

defineProps<{
  orders?: Pagination<Order>;
}>()
</script>

<template>
  <div class="flex h-full flex-row gap-4">
    <ChatOrderList :orders />
    <ChatPanel />
  </div>
</template>

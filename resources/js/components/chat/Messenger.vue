<script setup lang="ts">
import ChatOrderList from '@/components/chat/ChatOrderList.vue'
import ChatPanel from '@/components/chat/ChatPanel.vue'
import type { Order } from '@/types'

defineProps<{
  orders?: Order[];
}>()
</script>

<template>
  <v-card elevation="20">
    <v-container
      fluid
      class="m-0 flex h-full flex-col p-0"
      style="height: 100%"
    >
      <div class="flex min-h-0 flex-grow">
        <ChatOrderList :orders />

        <div class="flex min-h-0 flex-grow flex-col">
          <ChatPanel />
        </div>
      </div>
    </v-container>
  </v-card>
</template>

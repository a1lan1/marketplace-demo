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
      class="h-full p-0 m-0 flex flex-col"
      style="height: 100%;"
    >
      <div class="flex flex-grow min-h-0">
        <ChatOrderList :orders />

        <div class="flex flex-col flex-grow min-h-0">
          <ChatPanel />
        </div>
      </div>
    </v-container>
  </v-card>
</template>

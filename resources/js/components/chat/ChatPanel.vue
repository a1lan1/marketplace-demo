<script setup lang="ts">
import { ref, watch, nextTick, computed, onUnmounted } from 'vue'
import { echo } from '@laravel/echo-vue'
import { usePage } from '@inertiajs/vue3'
import { storeToRefs } from 'pinia'
import { useScroll } from '@vueuse/core'
import { z } from 'zod'
import { useZodValidation } from '@/composables/useZodValidation'
import { useOrderStore } from '@/stores/order'
import { useChatStore } from '@/stores/chat'
import { snackbar } from '@/plugins/snackbar'
import ChatMessage from './ChatMessage.vue'
import type { Message, User } from '@/types'

const orderStore = useOrderStore()
const { activeOrder } = storeToRefs(orderStore)

const chatStore = useChatStore()
const { loading, storing, messages, currentMessage } = storeToRefs(chatStore)
const { fetchMessages, storeMessage, addMessage } = chatStore

const page = usePage()
const currentUser = page.props.auth.user as User

const chatContainer = ref<HTMLElement | null>(null)

const { y } = useScroll(chatContainer, { behavior: 'smooth' })

const messageSchema = z.object({
  message: z.string().min(1, 'Message cannot be empty')
})

const formDataForValidation = computed(() => ({
  message: currentMessage.value
}))

const { validate, errors: validationErrors } = useZodValidation(messageSchema, formDataForValidation)

const sendMessage = async() => {
  if (!activeOrder.value || !validate()) {
    return
  }

  try {
    await storeMessage(activeOrder.value.id)
  } catch (e: any) {
    snackbar.error({
      text: e?.message || 'Error posting message'
    })
  }
}

const scrollToBottom = () => {
  nextTick(() => {
    if (chatContainer.value) {
      y.value = chatContainer.value.scrollHeight
    }
  })
}

watch(activeOrder, (newOrder, oldOrder) => {
  if (oldOrder) {
    echo().leave(`chat.${oldOrder.id}`)
  }

  if (newOrder) {
    fetchMessages(newOrder.id)

    echo().private(`chat.${newOrder.id}`)
      .listen('.order.message.sent', (e: { message: Message }) => {
        addMessage(e.message)
        scrollToBottom()
      })
  }
}, { immediate: true })

watch(() => messages.value.length, scrollToBottom)

onUnmounted(() => {
  if (activeOrder.value) {
    echo().leave(`chat.${activeOrder.value.id}`)
  }

  chatStore.$reset()
  orderStore.$reset()
})
</script>

<template>
  <div
    v-if="activeOrder"
    class="grow p-0 flex flex-col h-full"
  >
    <!-- Header -->
    <v-sheet
      color="surface-variant"
      class="p-4 flex-shrink-0"
    >
      <h2 class="text-h6">
        Chat for Order #{{ activeOrder.id }}
      </h2>
    </v-sheet>

    <!-- Messages -->
    <div
      ref="chatContainer"
      class="grow p-4 overflow-y-auto flex flex-col"
      style="height: calc(100vh - 260px)"
    >
      <ChatMessage
        v-for="msg in messages"
        :key="msg.id"
        :message="msg"
        :current-user="currentUser"
      />
      <div
        v-if="loading"
        class="text-center mt-4"
      >
        <v-progress-circular
          indeterminate
          color="primary"
        />
      </div>
    </div>

    <!-- Message Input -->
    <v-sheet class="p-4 border-t flex-shrink-0">
      <v-form @submit.prevent="sendMessage">
        <v-text-field
          v-model="currentMessage"
          label="Type your message..."
          variant="outlined"
          append-inner-icon="mdi-send"
          :error-messages="validationErrors.message"
          :loading="storing"
          hide-details="auto"
          autofocus
          @focus="validationErrors.message = ''"
          @click:append-inner="sendMessage"
          @keydown.enter.prevent="sendMessage"
        />
      </v-form>
    </v-sheet>
  </div>
  <div
    v-else
    class="flex grow justify-center items-center"
  >
    <div class="text-center text-grey">
      <v-icon
        size="64"
        class="mb-2"
      >
        mdi-message-text-outline
      </v-icon>
      <p>Select an order to start chatting</p>
    </div>
  </div>
</template>

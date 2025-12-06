<script setup lang="ts">
import { useZodValidation } from '@/composables/useZodValidation'
import { snackbar } from '@/plugins/snackbar'
import { useChatStore } from '@/stores/chat'
import { useOrderStore } from '@/stores/order'
import type { Message, User } from '@/types'
import { usePage } from '@inertiajs/vue3'
import { echo } from '@laravel/echo-vue'
import { useScroll } from '@vueuse/core'
import { storeToRefs } from 'pinia'
import { computed, nextTick, onUnmounted, ref, watch } from 'vue'
import { z } from 'zod'
import ChatMessage from './ChatMessage.vue'

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

const { validate, errors: validationErrors } = useZodValidation(
  messageSchema,
  formDataForValidation
)

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

watch(
  activeOrder,
  (newOrder, oldOrder) => {
    if (oldOrder) {
      echo().leave(`chat.${oldOrder.id}`)
    }

    if (newOrder) {
      fetchMessages(newOrder.id)

      echo()
        .private(`chat.${newOrder.id}`)
        .listen('.order.message.sent', (e: { message: Message }) => {
          addMessage(e.message)
          scrollToBottom()
        })
    }
  },
  { immediate: true }
)

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
    class="flex h-full grow flex-col p-0"
  >
    <!-- Header -->
    <v-sheet
      color="surface-variant"
      class="flex-shrink-0 p-4"
    >
      <h2 class="text-h6">
        Chat for Order #{{ activeOrder.id }}
      </h2>
    </v-sheet>

    <!-- Messages -->
    <div
      ref="chatContainer"
      class="flex grow flex-col overflow-y-auto p-4"
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
        class="mt-4 text-center"
      >
        <v-progress-circular
          indeterminate
          color="primary"
        />
      </div>
    </div>

    <!-- Message Input -->
    <v-sheet class="flex-shrink-0 border-t p-4">
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
    class="flex grow items-center justify-center"
  >
    <div class="text-grey text-center">
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

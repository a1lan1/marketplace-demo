import { defineStore } from 'pinia'
import  { Message, Pagination } from '@/types'

interface State {
  loading: boolean;
  storing: boolean;
  messages: Message[];
  currentMessage: string;
}

export const useChatStore = defineStore('chat', {
  state: (): State => ({
    loading: false,
    storing: false,
    messages: [],
    currentMessage: ''
  }),

  actions: {
    async fetchMessages(orderId: number): Promise<void> {
      this.loading = true
      this.resetMessages()

      try {
        const { data } = await this.$axios.get<Pagination<Message>>(`/chat/${orderId}/messages`)
        this.messages = data.data.reverse()
      } catch (e: any) {
        this.$snackbar.error({
          text: e.message || 'An error occurred while fetching messages.'
        })
      } finally {
        this.loading = false
      }
    },
    async storeMessage(orderId: number): Promise<void> {
      this.storing = true
      // Optimistic update message
      const tempMessage = this.currentMessage
      this.currentMessage = ''

      try {
        await this.$axios.post(`/chat/${orderId}`, {
          message: tempMessage
        })
      } catch (e: any) {
        this.currentMessage = tempMessage
        this.$snackbar.error({
          text: e.message || 'An error occurred while creating message.'
        })
        throw e
      } finally {
        this.storing = false
      }
    },
    addMessage(message: Message) {
      if (!this.messages.some(({ id }) => id === message.id)) {
        this.messages.push(message)
      }
    },
    resetMessages() {
      this.messages = []
    }
  }
})

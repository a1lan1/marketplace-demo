<script setup lang="ts">
import type { Message, User } from '@/types'

const props = defineProps<{
  message: Message;
  currentUser: User;
}>()

const isCurrentUser = props.message.user.id === props.currentUser.id
</script>

<template>
  <div
    class="flex mb-4"
    :class="isCurrentUser ? 'justify-end' : 'justify-start'"
  >
    <v-avatar
      size="36"
      class="mr-2"
    >
      <v-img :src="message.user.avatar" />
    </v-avatar>

    <v-card
      :color="isCurrentUser ? 'primary' : 'success'"
      class="rounded-lg p-5"
      max-width="70%"
      flat
    >
      <div class="px-2 py-1">
        <div class="flex justify-between items-baseline align-center mb-1">
          <span class="font-weight-bold text-caption">
            {{ isCurrentUser ? 'You' : message.user.name }}
          </span>

          <v-chip
            size="x-small"
            class="ml-2"
          >
            {{ new Date(message.created_at).toLocaleTimeString() }}
          </v-chip>
        </div>
        <p class="text-body-2">
          {{ message.message }}
        </p>
      </div>
    </v-card>
  </div>
</template>

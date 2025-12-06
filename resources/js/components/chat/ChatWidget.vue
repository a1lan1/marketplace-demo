<script setup lang="ts">
import Messenger from '@/components/chat/Messenger.vue'
import type { User } from '@/types'
import { usePage } from '@inertiajs/vue3'
import { ref } from 'vue'

const page = usePage()
const currentUser = page.props.auth.user as User | null

const dialog = ref(false)
</script>

<template>
  <v-btn
    v-if="currentUser"
    class="fixed right-4 bottom-4 z-50"
    color="primary"
    icon="mdi-message-text"
    size="large"
    @click="dialog = true"
  />

  <v-dialog
    v-model="dialog"
    :scrim="false"
    width="70%"
    transition="dialog-bottom-transition"
  >
    <v-card>
      <v-toolbar
        color="primary"
        density="compact"
      >
        <v-toolbar-title>Chat</v-toolbar-title>

        <v-spacer />

        <v-btn
          icon
          dark
          @click="dialog = false"
        >
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-toolbar>

      <Messenger />
    </v-card>
  </v-dialog>
</template>

<style scoped>
.fixed {
  position: fixed;
}
.bottom-4 {
  bottom: 1rem;
}
.right-4 {
  right: 1rem;
}
.z-50 {
  z-index: 50;
}
</style>

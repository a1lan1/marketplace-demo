<script setup lang="ts">
import ChatWidget from '@/components/chat/ChatWidget.vue'
import { SidebarProvider } from '@/components/ui/sidebar'
import { useEchoSubscriptions } from '@/composables/useEchoSubscriptions'
import { useFlashMessages } from '@/composables/useFlashMessages'
import type { AppPageProps } from '@/types'
import { usePage } from '@inertiajs/vue3'

interface Props {
  variant?: 'header' | 'sidebar';
}

defineProps<Props>()

const page = usePage<AppPageProps>()
const isOpen = page.props.sidebarOpen

useEchoSubscriptions()
useFlashMessages()
</script>

<template>
  <div
    v-if="variant === 'header'"
    class="flex min-h-screen w-full flex-col"
  >
    <slot />
    <ChatWidget v-if="page.props.auth.user" />
  </div>
  <SidebarProvider
    v-else
    :default-open="isOpen"
  >
    <slot />
    <ChatWidget v-if="page.props.auth.user" />
  </SidebarProvider>
</template>

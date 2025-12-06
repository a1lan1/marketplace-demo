<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Messenger from '@/components/chat/Messenger.vue'
import type { BreadcrumbItem, Order } from '@/types'

defineProps<{
  orders: Order[];
}>()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Chat',
    href: '#'
  }
]
</script>

<template>
  <Head title="Chat" />

  <AppLayout :breadcrumbs>
    <Messenger :orders />
  </AppLayout>
</template>

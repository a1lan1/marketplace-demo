<script setup lang="ts">
import Messenger from '@/components/chat/Messenger.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { BreadcrumbItem, Order, Pagination } from '@/types'
import { Head } from '@inertiajs/vue3'

defineProps<{
  orders: Pagination<Order>;
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

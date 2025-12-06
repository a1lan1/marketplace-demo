<script setup lang="ts">
import AppContent from '@/components/theme/AppContent.vue'
import AppShell from '@/components/theme/AppShell.vue'
import AppSidebar from '@/components/theme/AppSidebar.vue'
import AppSidebarHeader from '@/components/theme/AppSidebarHeader.vue'
import Snackbar from '@/components/theme/Snackbar.vue'
import type { BreadcrumbItemType } from '@/types'

interface Props {
  breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
  breadcrumbs: () => []
})
</script>

<template>
  <AppShell variant="sidebar">
    <Snackbar />
    <AppSidebar />
    <AppContent
      variant="sidebar"
      class="overflow-x-hidden"
    >
      <AppSidebarHeader :breadcrumbs="breadcrumbs" />
      <slot />
    </AppContent>
  </AppShell>
</template>

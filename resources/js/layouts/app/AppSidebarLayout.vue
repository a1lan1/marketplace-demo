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
    <AppSidebar v-if="$page.props.auth.user" />
    <div class="flex flex-1 flex-col overflow-x-hidden overflow-y-auto">
      <AppSidebarHeader
        :breadcrumbs="breadcrumbs"
        class="dark:border-gray-1000 sticky top-0 z-10 flex-shrink-0 backdrop-blur dark:bg-gray-900/10"
      />
      <AppContent variant="sidebar">
        <slot />
      </AppContent>
    </div>
  </AppShell>
</template>

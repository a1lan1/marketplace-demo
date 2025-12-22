<script setup lang="ts">
import AuthButtons from '@/components/AuthButtons.vue'
import CartWidget from '@/components/cart/CartWidget.vue'
import Breadcrumbs from '@/components/theme/Breadcrumbs.vue'
import { SidebarTrigger } from '@/components/ui/sidebar'
import type { BreadcrumbItemType } from '@/types'
import { formatCurrency } from '@/utils/formatters'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

withDefaults(
  defineProps<{
    breadcrumbs?: BreadcrumbItemType[];
  }>(),
  {
    breadcrumbs: () => []
  }
)

const page = usePage()
const userBalance = computed(() =>
  formatCurrency(page.props.auth.user?.balance ?? 0)
)
</script>

<template>
  <header
    class="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
  >
    <div class="flex items-center gap-2">
      <SidebarTrigger class="-ml-1" />

      <Breadcrumbs
        v-if="breadcrumbs && breadcrumbs.length > 0"
        :breadcrumbs="breadcrumbs"
      />
    </div>

    <div class="flex items-center gap-4">
      <v-chip
        v-if="page.props.auth.user"
        class="mr-2"
        color="primary"
      >
        Balance: {{ userBalance }}
      </v-chip>
      <AuthButtons v-else />
      <CartWidget />
    </div>
  </header>
</template>

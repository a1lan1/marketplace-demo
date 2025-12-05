<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { formatCurrency } from '@/utils/formatters'
import { SidebarTrigger } from '@/components/ui/sidebar'
import Breadcrumbs from '@/components/theme/Breadcrumbs.vue'
import CartWidget from '@/components/cart/CartWidget.vue'
import type { BreadcrumbItemType } from '@/types'

withDefaults(
  defineProps<{
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
  {
    breadcrumbs: () => []
  }
)

const page = usePage()
const userBalance = computed(() => formatCurrency(page.props.auth.user?.balance ?? 0))
</script>

<template>
  <header class="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
    <div class="flex items-center gap-2">
      <SidebarTrigger class="-ml-1" />

      <Breadcrumbs
        v-if="breadcrumbs && breadcrumbs.length > 0"
        :breadcrumbs="breadcrumbs"
      />
    </div>

    <div>
      <v-chip
        class="mr-2"
        color="primary"
      >
        Balance: {{ userBalance }}
      </v-chip>
      <CartWidget />
    </div>
  </header>
</template>

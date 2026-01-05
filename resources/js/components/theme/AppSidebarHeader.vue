<script setup lang="ts">
import AuthButtons from '@/components/AuthButtons.vue'
import CartWidget from '@/components/cart/CartWidget.vue'
import CurrencySwitcher from '@/components/common/CurrencySwitcher.vue'
import UserBalanceChip from '@/components/common/UserBalanceChip.vue'
import Breadcrumbs from '@/components/theme/Breadcrumbs.vue'
import { SidebarTrigger } from '@/components/ui/sidebar'
import type { BreadcrumbItemType } from '@/types'
import { usePage } from '@inertiajs/vue3'

withDefaults(
  defineProps<{
    breadcrumbs?: BreadcrumbItemType[];
  }>(),
  {
    breadcrumbs: () => []
  }
)

const page = usePage()
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
      <CurrencySwitcher />
      <UserBalanceChip
        v-if="page.props.auth.user"
        :balance="page.props.auth.user.balance"
        class="mr-2"
      />
      <AuthButtons v-else />
      <CartWidget />
    </div>
  </header>
</template>

<script setup lang="ts">
import NavFooter from '@/components/theme/NavFooter.vue'
import NavMain from '@/components/theme/NavMain.vue'
import NavUser from '@/components/theme/NavUser.vue'
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem
} from '@/components/ui/sidebar'
import { home, telescope } from '@/routes'
import { index as chatIndex } from '@/routes/chat'
import { dashboard as filament } from '@/routes/filament/admin/pages'
import { index as geoLocations } from '@/routes/geo/locations'
import { index as horizon } from '@/routes/horizon'
import { index as logViewer } from '@/routes/log-viewer'
import { index as ordersIndex } from '@/routes/orders'
import {
  catalog as catalogIndex,
  index as productsIndex
} from '@/routes/products'
import { defaultMethod as prometheus } from '@/routes/prometheus'
import type { NavItem } from '@/types'
import { Link } from '@inertiajs/vue3'
import {
  Folder,
  LayoutGrid,
  ListOrdered,
  MapPin,
  MessageSquare,
  Package,
  ShoppingCart
} from 'lucide-vue-next'
import AppLogo from './AppLogo.vue'

const mainNavItems: NavItem[] = [
  {
    title: 'Catalog',
    href: catalogIndex(),
    icon: ShoppingCart
  },
  {
    title: 'Orders',
    href: ordersIndex(),
    icon: ListOrdered
  },
  {
    title: 'My Products',
    href: productsIndex(),
    icon: Package
  },
  {
    title: 'Support',
    href: chatIndex(),
    icon: MessageSquare
  }
]

const geoNavItems: NavItem[] = [
  {
    title: 'My Locations',
    href: geoLocations(),
    icon: MapPin
  }
]

const devNavItems: NavItem[] = [
  {
    title: 'Filament',
    href: filament(),
    icon: LayoutGrid
  },
  {
    title: 'Horizon',
    href: horizon(),
    icon: LayoutGrid
  },
  {
    title: 'Telescope',
    href: telescope(),
    icon: LayoutGrid
  },
  {
    title: 'Log Viewer',
    href: logViewer(),
    icon: LayoutGrid
  },
  {
    title: 'Meilisearch',
    href: 'http://localhost:7700',
    icon: LayoutGrid
  },
  {
    title: 'Mailpit',
    href: 'http://localhost:8025',
    icon: LayoutGrid
  },
  {
    title: 'Grafana',
    href: 'http://localhost:3000/dashboards',
    icon: LayoutGrid
  },
  {
    title: 'Prometheus',
    href: 'http://localhost:9090',
    icon: LayoutGrid
  },
  {
    title: 'Prometheus Metrics',
    href: prometheus(),
    icon: LayoutGrid
  }
]

const footerNavItems: NavItem[] = [
  {
    title: 'Github Repo',
    href: 'https://github.com/a1lan1/marketplace-demo',
    icon: Folder
  }
]
</script>

<template>
  <Sidebar
    collapsible="icon"
    variant="inset"
  >
    <SidebarHeader>
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton
            size="lg"
            as-child
          >
            <Link :href="home()">
              <AppLogo />
            </Link>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
      <NavMain
        title="Platform"
        :items="mainNavItems"
      />
      <NavMain
        title="GeoInsight"
        :items="geoNavItems"
        class="mt-4"
      />
      <NavMain
        title="Development"
        :items="devNavItems"
        class="mt-4"
      />
    </SidebarContent>

    <SidebarFooter>
      <NavFooter :items="footerNavItems" />
      <NavUser />
    </SidebarFooter>
  </Sidebar>
  <slot />
</template>

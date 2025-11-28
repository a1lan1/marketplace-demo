<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue'
import NavMain from '@/components/NavMain.vue'
import NavUser from '@/components/NavUser.vue'
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem
} from '@/components/ui/sidebar'
import { dashboard, telescope } from '@/routes'
import { dashboard as filament } from '@/routes/filament/admin/pages'
import { index as horizon } from '@/routes/horizon'
import { index as logViewer } from '@/routes/log-viewer'
import { defaultMethod as prometheus } from '@/routes/prometheus'
import { Link } from '@inertiajs/vue3'
import { Folder, LayoutGrid } from 'lucide-vue-next'
import AppLogo from './AppLogo.vue'
import type { NavItem } from '@/types'

const mainNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: dashboard(),
    icon: LayoutGrid
  },
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
    href: 'https://github.com/a1lan1/laravel-vuetify-starter',
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
            <Link :href="dashboard()">
              <AppLogo />
            </Link>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
      <NavMain :items="mainNavItems" />
    </SidebarContent>

    <SidebarFooter>
      <NavFooter :items="footerNavItems" />
      <NavUser />
    </SidebarFooter>
  </Sidebar>
  <slot />
</template>

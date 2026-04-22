<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { LayoutGrid, FileText, Calendar, PlusCircle, CheckSquare, ClipboardCheck } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const page = usePage();

const mainNavItems = computed<NavItem[]>(() => {
  const roles = page.props.auth?.user?.roles || [];
  
  const baseItems: NavItem[] = [
    {
      title: 'Dashboard',
      href: dashboard(),
      icon: LayoutGrid,
    },
  ];

  if (roles.includes('Mahasiswa')) {
    return [
      ...baseItems,
      {
        title: 'New Submission',
        href: '/pengajuan/baru',
        icon: PlusCircle,
      },
      {
        title: 'Submission History',
        href: '/pengajuan',
        icon: FileText,
      },
      {
        title: 'Availability',
        href: '/ketersediaan',
        icon: Calendar,
      },
    ];
  }

  if (roles.includes('Kepala Laboratorium')) {
    return [
      ...baseItems,
      {
        title: 'Incoming Submissions',
        href: '/kepala-lab',
        icon: ClipboardCheck,
      },
      {
        title: 'Availability',
        href: '/ketersediaan',
        icon: Calendar,
      },
    ];
  }

  if (roles.includes('Petugas Laboran')) {
    return [
      ...baseItems,
      {
        title: 'Validation',
        href: '/laboran',
        icon: CheckSquare,
      },
      {
        title: 'Availability',
        href: '/ketersediaan',
        icon: Calendar,
      },
    ];
  }

  return baseItems;
});

const footerNavItems: NavItem[] = [];
</script>

<template>
  <Sidebar collapsible="icon" variant="sidebar">
    <SidebarHeader>
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton size="lg" as-child>
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

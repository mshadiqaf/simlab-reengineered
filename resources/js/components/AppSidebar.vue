<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { LayoutGrid, FileText, Calendar, PlusCircle, CheckSquare, ClipboardCheck, DoorOpen, Wrench, FlaskConical } from 'lucide-vue-next';
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
  // getRoleNames() from Spatie can serialize as an object; normalise to plain array
  const rawRoles = page.props.auth?.user?.roles ?? [];
  const roles: string[] = Array.isArray(rawRoles)
    ? rawRoles
    : Object.values(rawRoles as Record<string, string>);

  // Temporary debug — remove after confirming sidebar works
  if (import.meta.env.DEV) {
    console.log('[AppSidebar] auth.user:', page.props.auth?.user, '| roles:', roles);
  }

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
      {
        title: 'Ruangan',
        href: '/master-data/ruangan',
        icon: DoorOpen,
      },
      {
        title: 'Alat',
        href: '/master-data/alat',
        icon: Wrench,
      },
      {
        title: 'Jenis Pengujian',
        href: '/master-data/pengujian',
        icon: FlaskConical,
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
      {
        title: 'Ruangan',
        href: '/master-data/ruangan',
        icon: DoorOpen,
      },
      {
        title: 'Alat',
        href: '/master-data/alat',
        icon: Wrench,
      },
      {
        title: 'Jenis Pengujian',
        href: '/master-data/pengujian',
        icon: FlaskConical,
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

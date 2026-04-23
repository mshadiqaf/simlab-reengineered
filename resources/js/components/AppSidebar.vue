<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { LayoutGrid, FileText, Calendar, CheckSquare, ClipboardCheck, DoorOpen, Wrench, FlaskConical } from 'lucide-vue-next';
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
import type { NavGroup, NavItem } from '@/types';

const page = usePage();

const mainNavItems = computed<NavGroup[]>(() => {
  const rawRoles = page.props.auth?.user?.roles ?? [];
  const roles: string[] = Array.isArray(rawRoles)
    ? rawRoles
    : Object.values(rawRoles as Record<string, string>);

  const dasborGroup: NavGroup = {
    title: 'Dasbor',
    items: [
      {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
      },
    ]
  };

  if (roles.includes('Mahasiswa')) {
    return [
      dasborGroup,
      {
        title: 'Layanan',
        items: [
          {
            title: 'Pengajuan',
            icon: FileText,
            items: [
              {
                title: 'New Submission',
                href: '/pengajuan/baru',
              },
              {
                title: 'Submission History',
                href: '/pengajuan',
              }
            ]
          },
          {
            title: 'Availability Check',
            href: '/ketersediaan',
            icon: Calendar,
          },
        ]
      }
    ];
  }

  if (roles.includes('Kepala Laboratorium')) {
    return [
      dasborGroup,
      {
        title: 'Manajemen',
        items: [
          {
            title: 'Incoming Submissions',
            href: '/kepala-lab',
            icon: ClipboardCheck,
          },
          {
            title: 'Availability Check',
            href: '/ketersediaan',
            icon: Calendar,
          },
        ]
      },
      {
        title: 'Master Data',
        items: [
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
        ]
      }
    ];
  }

  if (roles.includes('Petugas Laboran')) {
    return [
      dasborGroup,
      {
        title: 'Operasional',
        items: [
          {
            title: 'Validation',
            href: '/laboran',
            icon: CheckSquare,
          },
          {
            title: 'Availability Check',
            href: '/ketersediaan',
            icon: Calendar,
          },
        ]
      },
      {
        title: 'Master Data',
        items: [
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
        ]
      }
    ];
  }

  return [dasborGroup];
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
      <NavMain :groups="mainNavItems" />
    </SidebarContent>

    <SidebarFooter>
      <NavFooter :items="footerNavItems" />
      <NavUser />
    </SidebarFooter>
  </Sidebar>
  <slot />
</template>

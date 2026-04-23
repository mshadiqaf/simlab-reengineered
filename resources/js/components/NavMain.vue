<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
  SidebarGroup,
  SidebarGroupLabel,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarMenuSub,
  SidebarMenuSubButton,
  SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import type { NavGroup } from '@/types';

defineProps<{
  groups: NavGroup[];
}>();

const { isCurrentUrl } = useCurrentUrl();
</script>

<template>
  <SidebarGroup v-for="(group, index) in groups" :key="index" class="px-2 py-0">
    <SidebarGroupLabel v-if="group.title">{{ $t(group.title) }}</SidebarGroupLabel>
    <SidebarMenu>
      <SidebarMenuItem v-for="item in group.items" :key="item.title">
        <Collapsible v-if="item.items && item.items.length > 0" asChild :defaultOpen="item.isActive" class="group/collapsible">
          <SidebarMenuItem>
            <CollapsibleTrigger asChild>
              <SidebarMenuButton :tooltip="$t(item.title)">
                <component :is="item.icon" v-if="item.icon" />
                <span>{{ $t(item.title) }}</span>
                <ChevronRight class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
              </SidebarMenuButton>
            </CollapsibleTrigger>
            <CollapsibleContent>
              <SidebarMenuSub>
                <SidebarMenuSubItem v-for="subItem in item.items" :key="subItem.title">
                  <SidebarMenuSubButton asChild :isActive="isCurrentUrl(subItem.href || '')">
                    <Link :href="subItem.href || '#'">
                      <span>{{ $t(subItem.title) }}</span>
                    </Link>
                  </SidebarMenuSubButton>
                </SidebarMenuSubItem>
              </SidebarMenuSub>
            </CollapsibleContent>
          </SidebarMenuItem>
        </Collapsible>

        <SidebarMenuButton v-else as-child :is-active="isCurrentUrl(item.href || '')" :tooltip="$t(item.title)">
          <Link :href="item.href || '#'">
            <component :is="item.icon" v-if="item.icon" />
            <span>{{ $t(item.title) }}</span>
          </Link>
        </SidebarMenuButton>
      </SidebarMenuItem>
    </SidebarMenu>
  </SidebarGroup>
</template>

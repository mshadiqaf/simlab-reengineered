import type { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export type BreadcrumbItem = {
  title: string;
  href: NonNullable<InertiaLinkProps['href']>;
};

export type NavItem = {
  title: string;
  href?: NonNullable<InertiaLinkProps['href']>;
  icon?: LucideIcon;
  isActive?: boolean;
  items?: Omit<NavItem, 'icon' | 'items'>[];
};

export type NavGroup = {
  title?: string;
  items: NavItem[];
};

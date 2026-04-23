<script setup lang="ts" generic="T">
import { InboxIcon } from 'lucide-vue-next';
import { computed } from 'vue';
import { Skeleton } from '@/components/ui/skeleton';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import EmptyState from './EmptyState.vue';

export interface Column<T> {
  key: string;
  label: string;
  render?: (row: T) => any;
  class?: string;
}

const props = defineProps<{
  columns: Column<T>[];
  data: T[] | null;
  loading?: boolean;
  emptyTitle?: string;
  emptyDescription?: string;
}>();

const emit = defineEmits<{
  (e: 'row-click', row: T): void;
}>();

const isEmpty = computed(() => !props.loading && (!props.data || props.data.length === 0));

const handleRowClick = (row: T) => {
  emit('row-click', row);
};
</script>

<template>
  <div class="rounded-md border bg-card">
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead 
            v-for="col in columns" 
            :key="col.key" 
            :class="col.class"
          >
            {{ $t(col.label) }}
          </TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <template v-if="loading">
          <TableRow v-for="i in 5" :key="i">
            <TableCell v-for="col in columns" :key="col.key" :class="col.class">
              <Skeleton class="h-5 w-full" />
            </TableCell>
          </TableRow>
        </template>
        <template v-else-if="!isEmpty && data">
          <TableRow 
            v-for="(row, index) in data" 
            :key="index"
            class="cursor-pointer hover:bg-muted/50 transition-colors"
            @click="handleRowClick(row)"
          >
            <TableCell v-for="col in columns" :key="col.key" :class="col.class">
              <slot :name="`cell-${col.key}`" :row="row" :value="(row as any)[col.key]">
                {{ (row as any)[col.key] }}
              </slot>
            </TableCell>
          </TableRow>
        </template>
        <TableRow v-else>
          <TableCell :colspan="columns.length" class="h-24 text-center">
            <EmptyState 
              :title="emptyTitle || 'No data available'" 
              :description="emptyDescription || 'There are no records to display at the moment.'" 
              :icon="InboxIcon" 
              class="border-0 bg-transparent min-h-[200px]"
            />
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>
</template>

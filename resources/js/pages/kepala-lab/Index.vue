<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import { dashboard } from '@/routes';
import { Card, CardContent } from '@/components/ui/card';
import { useApi } from '@/composables/useApi';
import type { Pengajuan } from '@/types/simlab';
import DataTable, { type Column } from '@/components/DataTable.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { format } from 'date-fns';
import { id } from 'date-fns/locale';

defineOptions({
  layout: {
    breadcrumbs: [
      { title: 'Dashboard', href: dashboard() },
      { title: 'Verifikasi Pengajuan', href: '/kepala-lab' },
    ],
  },
});

const { data: submissions, loading, execute } = useApi<Pengajuan[]>('/api/kepala-lab/pengajuan');

onMounted(() => {
  execute('');
});

const columns: Column<Pengajuan>[] = [
  { key: 'tipe_pengajuan', label: 'Type' },
  { key: 'judul_proyek', label: 'Title', class: 'max-w-[300px] truncate' },
  { 
    key: 'pengaju', 
    label: 'Submitter',
    render: (row) => row.pengaju?.name || '-'
  },
  { key: 'status', label: 'Status' },
  { 
    key: 'dibuat_pada', 
    label: 'Date',
    render: (row) => format(new Date(row.dibuat_pada), 'dd MMM yyyy', { locale: id })
  },
];
</script>

<template>
  <Head title="Verifikasi Pengajuan" />

  <div class="flex flex-col gap-6 p-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">
          Verifikasi Pengajuan
        </h1>
        <p class="text-muted-foreground">
          Daftar pengajuan laboratorium yang memerlukan verifikasi Kepala Laboratorium
        </p>
      </div>
    </div>

    <Card>
      <CardContent class="p-0 sm:p-6">
        <DataTable 
          :columns="columns" 
          :data="submissions" 
          :loading="loading"
          @row-click="(row) => $inertia.visit(`/kepala-lab/${row.id}`)"
        >
          <template #cell-tipe_pengajuan="{ row }">
            <span class="font-medium">
              {{ row.tipe_pengajuan === 'ruangan' ? $t('Room Loan') 
               : row.tipe_pengajuan === 'alat' ? $t('Equipment Loan') 
               : $t('Test Service') }}
            </span>
          </template>
          <template #cell-pengaju="{ row }">
            <div>
              <p class="font-medium text-sm">{{ row.pengaju?.name }}</p>
              <p class="text-xs text-muted-foreground">{{ row.pengaju?.nim || row.pengaju?.email }}</p>
            </div>
          </template>
          <template #cell-status="{ row }">
            <StatusBadge :status="row.status" />
          </template>
          <template #cell-dibuat_pada="{ row }">
            {{ format(new Date(row.dibuat_pada), 'dd MMM yyyy', { locale: id }) }}
          </template>
        </DataTable>
      </CardContent>
    </Card>
  </div>
</template>

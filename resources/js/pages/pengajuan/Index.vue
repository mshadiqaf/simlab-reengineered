<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { format } from 'date-fns';
import { id } from 'date-fns/locale';
import { PlusCircle } from 'lucide-vue-next';
import { onMounted } from 'vue';
import DataTable from '@/components/DataTable.vue';
import type { Column } from '@/components/DataTable.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { useApi } from '@/composables/useApi';
import { dashboard } from '@/routes';
import type { Pengajuan } from '@/types/simlab';

defineOptions({
  layout: {
    breadcrumbs: [
      { title: 'Dashboard', href: dashboard() },
      { title: 'Riwayat Pengajuan', href: '/pengajuan' },
    ],
  },
});

// Assuming the API returns a paginated response with data array.
// For now, useApi treats the top level as data, but Laravel paginates data under `.data` or similar.
// If the API returns direct array for index, we use it directly.
const { data: submissions, loading, execute } = useApi<Pengajuan[]>('/api/pengajuan');

onMounted(() => {
  execute();
});

const columns: Column<Pengajuan>[] = [
  { key: 'tipe_pengajuan', label: 'Type' },
  { key: 'judul_proyek', label: 'Title', class: 'max-w-[300px] truncate' },
  { key: 'status', label: 'Status' },
  { 
    key: 'dibuat_pada', 
    label: 'Date',
    render: (row) => format(new Date(row.dibuat_pada), 'dd MMM yyyy', { locale: id })
  },
];
</script>

<template>
  <Head title="Riwayat Pengajuan" />

  <div class="flex flex-col gap-6 p-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">
          {{ $t('Submission History') }}
        </h1>
        <p class="text-muted-foreground">
          Kelola dan pantau status pengajuan peminjaman laboratorium Anda
        </p>
      </div>
      <Button @click="$inertia.visit('/pengajuan/baru')">
        <PlusCircle class="w-4 h-4 mr-2" />
        {{ $t('New Submission') }}
      </Button>
    </div>

    <Card>
      <CardContent class="p-0 sm:p-6">
        <DataTable 
          :columns="columns" 
          :data="submissions" 
          :loading="loading"
          @row-click="(row) => $inertia.visit(`/pengajuan/${row.id}`)"
        >
          <template #cell-tipe_pengajuan="{ row }">
            <span class="font-medium">
              {{ row.tipe_pengajuan === 'ruangan' ? $t('Room Loan') 
               : row.tipe_pengajuan === 'alat' ? $t('Equipment Loan') 
               : $t('Test Service') }}
            </span>
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

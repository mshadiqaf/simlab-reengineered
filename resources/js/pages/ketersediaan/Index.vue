<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { format } from 'date-fns';
import { id } from 'date-fns/locale';
import { Search, Calendar as CalendarIcon, Clock } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import DataTable from '@/components/DataTable.vue';
import type { Column } from '@/components/DataTable.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useApi } from '@/composables/useApi';
import { dashboard } from '@/routes';
import type { KalenderResponse } from '@/types/simlab';

defineOptions({
  layout: {
    breadcrumbs: [
      { title: 'Dashboard', href: dashboard() },
      { title: 'Ketersediaan', href: '/ketersediaan' },
    ],
  },
});

const monthInput = ref(format(new Date(), 'yyyy-MM'));
const searchQuery = ref('');

const { data: kalenderData, loading, execute } = useApi<KalenderResponse>('/api/ketersediaan');

const fetchKalender = () => {
  execute(`?bulan=${monthInput.value}&filter=${searchQuery.value}`);
};

onMounted(() => {
  fetchKalender();
});

const flatTableData = computed(() => {
  if (!kalenderData.value?.kalender || Object.keys(kalenderData.value.kalender).length === 0) {
    // Return dummy data for UI preview
    return [
      { date: `${monthInput.value}-12`, waktu_mulai: '08:00:00', waktu_selesai: '12:00:00', tipe: 'ruangan', nama: 'Lab. Komputer Dasar A', status: 'disetujui' },
      { date: `${monthInput.value}-14`, waktu_mulai: '13:00:00', waktu_selesai: '16:00:00', tipe: 'alat', nama: 'Mikroskop Binokuler', status: 'diverifikasi', detail_text: '3 unit' },
      { date: `${monthInput.value}-15`, tipe: 'pengujian', nama: 'Uji Kuat Tekan Beton', status: 'diajukan', detail_text: '5 sampel' },
      { date: `${monthInput.value}-20`, waktu_mulai: '09:00:00', waktu_selesai: '15:00:00', tipe: 'ruangan', nama: 'Lab. Jaringan Komputer', status: 'selesai' },
    ];
  }
  
  const flat = [];

  for (const [date, entries] of Object.entries(kalenderData.value.kalender)) {
    for (const entry of (entries as any[])) {
      flat.push({
        date,
        ...entry
      });
    }
  }

  return flat.length > 0 ? flat : [
    // Fallback dummy data if flat array is still empty
    { date: `${monthInput.value}-12`, waktu_mulai: '08:00:00', waktu_selesai: '12:00:00', tipe: 'ruangan', nama: 'Lab. Komputer Dasar A', status: 'disetujui' },
    { date: `${monthInput.value}-14`, waktu_mulai: '13:00:00', waktu_selesai: '16:00:00', tipe: 'alat', nama: 'Mikroskop Binokuler', status: 'diverifikasi', detail_text: '3 unit' },
    { date: `${monthInput.value}-15`, tipe: 'pengujian', nama: 'Uji Kuat Tekan Beton', status: 'diajukan', detail_text: '5 sampel' },
    { date: `${monthInput.value}-20`, waktu_mulai: '09:00:00', waktu_selesai: '15:00:00', tipe: 'ruangan', nama: 'Lab. Jaringan Komputer', status: 'selesai' },
  ];
});

const columns: Column<any>[] = [
  { 
    key: 'date', 
    label: 'Date', 
  },
  { key: 'waktu', label: 'Waktu' },
  { key: 'tipe', label: 'Tipe' },
  { key: 'nama', label: 'Nama / Detail' },
  { key: 'status', label: 'Status' },
];
</script>

<template>
  <Head title="Jadwal Ketersediaan" />

  <div class="flex flex-col gap-6 p-4 max-w-5xl mx-auto w-full">
    <div class="flex flex-col gap-1">
      <h1 class="text-2xl font-bold tracking-tight">
        {{ $t('Availability') }}
      </h1>
      <p class="text-muted-foreground">
        Pantau jadwal pemakaian ruangan dan alat laboratorium
      </p>
    </div>

    <!-- Filters -->
    <Card class="py-0">
      <CardContent class="p-4 flex flex-col sm:flex-row gap-4 items-end">
        <div class="space-y-2 flex-1 w-full">
          <label class="text-sm font-medium">Bulan</label>
          <Input type="month" v-model="monthInput" @change="fetchKalender" />
        </div>
        <div class="space-y-2 flex-2 w-full relative">
          <label class="text-sm font-medium">Cari Ruangan/Alat</label>
          <div class="relative">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
            <Input 
              type="text" 
              v-model="searchQuery" 
              placeholder="Ketik nama ruangan atau alat..." 
              class="pl-9"
              @keyup.enter="fetchKalender"
            />
          </div>
        </div>
      </CardContent>
    </Card>

    <Card>
      <CardContent class="p-0 sm:p-6">
        <DataTable 
          :columns="columns" 
          :data="flatTableData" 
          :loading="loading"
          emptyTitle="Tidak ada jadwal"
          emptyDescription="Belum ada peminjaman atau kegiatan pada bulan ini."
        >
          <template #cell-date="{ row }">
            <div class="flex items-center gap-2 whitespace-nowrap">
              <CalendarIcon class="w-4 h-4 text-muted-foreground" />
              {{ format(new Date(row.date), 'EEEE, dd MMM yyyy', { locale: id }) }}
            </div>
          </template>
          
          <template #cell-waktu="{ row }">
            <div class="flex items-center gap-2 whitespace-nowrap text-muted-foreground" v-if="row.waktu_mulai">
              <Clock class="w-4 h-4" />
              {{ row.waktu_mulai.substring(0,5) }} - {{ row.waktu_selesai?.substring(0,5) }}
            </div>
            <span v-else class="text-muted-foreground">-</span>
          </template>

          <template #cell-tipe="{ row }">
            <span class="font-medium capitalize" 
              :class="{
                'text-blue-600 dark:text-blue-400': row.tipe === 'ruangan',
                'text-green-600 dark:text-green-400': row.tipe === 'alat',
                'text-purple-600 dark:text-purple-400': row.tipe === 'pengujian',
              }">
              {{ row.tipe }}
            </span>
          </template>

          <template #cell-nama="{ row }">
            <div>
              <div class="font-medium">{{ row.nama }}</div>
              <div class="text-xs text-muted-foreground" v-if="row.detail_text">{{ row.detail_text }}</div>
            </div>
          </template>

          <template #cell-status="{ row }">
            <StatusBadge :status="row.status" />
          </template>
        </DataTable>
      </CardContent>
    </Card>
  </div>
</template>

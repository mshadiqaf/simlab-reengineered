<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { dashboard } from '@/routes';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useApi } from '@/composables/useApi';
import type { KalenderResponse, KalenderEntry } from '@/types/simlab';
import { format } from 'date-fns';
import { id } from 'date-fns/locale';
import { Search, Calendar as CalendarIcon } from 'lucide-vue-next';
import EmptyState from '@/components/EmptyState.vue';
import StatusBadge from '@/components/StatusBadge.vue';

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
    <Card>
      <CardContent class="p-4 flex flex-col sm:flex-row gap-4 items-end">
        <div class="space-y-2 flex-1 w-full">
          <label class="text-sm font-medium">Bulan</label>
          <Input type="month" v-model="monthInput" @change="fetchKalender" />
        </div>
        <div class="space-y-2 flex-[2] w-full relative">
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

    <div v-if="loading" class="flex justify-center p-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
    </div>

    <template v-else-if="kalenderData && Object.keys(kalenderData.kalender).length > 0">
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <Card v-for="(entries, date) in kalenderData.kalender" :key="date" class="flex flex-col">
          <CardHeader class="pb-3 border-b bg-muted/50">
            <CardTitle class="text-base flex items-center gap-2">
              <CalendarIcon class="w-4 h-4" />
              {{ format(new Date(date), 'EEEE, dd MMMM yyyy', { locale: id }) }}
            </CardTitle>
          </CardHeader>
          <CardContent class="pt-4 flex-1">
            <div class="space-y-4">
              <div v-for="(entry, idx) in entries" :key="idx" class="relative pl-4 border-l-2" 
                :class="{
                  'border-blue-500': entry.tipe === 'ruangan',
                  'border-green-500': entry.tipe === 'alat',
                  'border-purple-500': entry.tipe === 'pengujian',
                }"
              >
                <div class="flex items-center justify-between mb-1">
                  <span class="text-xs font-semibold capitalize text-muted-foreground">{{ entry.tipe }}</span>
                  <StatusBadge :status="entry.status" />
                </div>
                <h4 class="font-medium text-sm leading-tight mb-1">{{ entry.nama }}</h4>
                <p class="text-xs text-muted-foreground" v-if="entry.detail_text">
                  {{ entry.detail_text }}
                </p>
                <div class="flex items-center gap-2 mt-2 text-xs text-muted-foreground" v-if="entry.waktu_mulai">
                  <Clock class="w-3 h-3" />
                  {{ entry.waktu_mulai.substring(0,5) }} - {{ entry.waktu_selesai?.substring(0,5) }}
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </template>

    <Card v-else>
      <CardContent class="p-0">
        <EmptyState 
          title="Tidak ada jadwal" 
          description="Belum ada peminjaman atau kegiatan pada bulan ini."
          :icon="CalendarIcon"
        />
      </CardContent>
    </Card>
  </div>
</template>

<script lang="ts">
import { Clock } from 'lucide-vue-next';
</script>

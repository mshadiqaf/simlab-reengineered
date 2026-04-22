<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { format } from 'date-fns';
import id from 'date-fns/locale/id';
import { PlusCircle, History } from 'lucide-vue-next';
import { computed, onMounted } from 'vue';
import DataTable from '@/components/DataTable.vue';
import type {Column} from '@/components/DataTable.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useApi } from '@/composables/useApi';
import { dashboard } from '@/routes';
import type { Pengajuan } from '@/types/simlab';

defineOptions({
  layout: {
    breadcrumbs: [
      {
        title: 'Dashboard',
        href: dashboard(),
      },
    ],
  },
});

const page = usePage<any>();
const user = computed(() => page.props.auth.user);
const roles = computed(() => user.value.roles || []);

const isMahasiswa = computed(() => roles.value.includes('Mahasiswa'));

// API call for Recent Submissions
const { data: recentSubmissions, loading, execute } = useApi<Pengajuan[]>('/api/pengajuan');

onMounted(() => {
  if (isMahasiswa.value) {
    execute('?per_page=5');
  }
});

const columns: Column<Pengajuan>[] = [
  { key: 'tipe_pengajuan', label: 'Type' },
  { key: 'judul_proyek', label: 'Title', class: 'max-w-[200px] truncate' },
  { key: 'status', label: 'Status' },
  { 
    key: 'dibuat_pada', 
    label: 'Date',
    render: (row) => format(new Date(row.dibuat_pada), 'dd MMM yyyy', { locale: id })
  },
];
</script>

<template>
  <Head title="Dashboard" />

  <div class="flex flex-col gap-6 p-4">
    <!-- Greeting Section -->
    <div class="flex flex-col gap-1">
      <h1 class="text-2xl font-bold tracking-tight">
        {{ $t('Welcome back, :name').replace(':name', user.name) }}
      </h1>
      <p class="text-muted-foreground">
        Simlab - Sistem Informasi Manajemen Laboratorium ITK
      </p>
    </div>

    <!-- Mahasiswa Dashboard -->
    <template v-if="isMahasiswa">
      <!-- Quick Actions -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card class="hover:border-primary/50 transition-colors cursor-pointer" @click="$inertia.visit('/pengajuan/baru')">
          <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
            <CardTitle class="text-sm font-medium">{{ $t('New Submission') }}</CardTitle>
            <PlusCircle class="w-4 h-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">Buat</div>
            <p class="text-xs text-muted-foreground mt-1">Mulai pengajuan baru</p>
          </CardContent>
        </Card>
        
        <Card class="hover:border-primary/50 transition-colors cursor-pointer" @click="$inertia.visit('/pengajuan')">
          <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
            <CardTitle class="text-sm font-medium">{{ $t('Submission History') }}</CardTitle>
            <History class="w-4 h-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">Riwayat</div>
            <p class="text-xs text-muted-foreground mt-1">Lihat status pengajuan Anda</p>
          </CardContent>
        </Card>

        <!-- Dynamic stats could go here later based on API response -->
      </div>

      <!-- Recent Submissions -->
      <Card>
        <CardHeader class="flex flex-row items-center justify-between">
          <div>
            <CardTitle>{{ $t('Recent Submissions') }}</CardTitle>
            <CardDescription>
              5 pengajuan terakhir Anda
            </CardDescription>
          </div>
          <Button variant="outline" size="sm" @click="$inertia.visit('/pengajuan')">
            Lihat Semua
          </Button>
        </CardHeader>
        <CardContent>
          <DataTable 
            :columns="columns" 
            :data="recentSubmissions" 
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
    </template>

    <!-- Placeholder for other roles -->
    <template v-else>
      <Card>
        <CardContent class="flex flex-col items-center justify-center py-10">
          <p class="text-muted-foreground">Dashboard content for your role is being prepared.</p>
        </CardContent>
      </Card>
    </template>
  </div>
</template>

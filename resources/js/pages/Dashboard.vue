<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { format } from 'date-fns';
import { id as idLocale } from 'date-fns/locale';
import {
  Bot,
  Calendar,
  CalendarClock,
  ClipboardCheck,
  ClipboardList,
  FlaskConical,
  Loader2,
} from 'lucide-vue-next';
import { computed, onMounted } from 'vue';
import ChatWidget from '@/components/ChatWidget.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { useApi } from '@/composables/useApi';
import { dashboard } from '@/routes';
import type { Pengajuan } from '@/types/simlab';

defineOptions({
  layout: {
    breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
  },
});

const page = usePage<any>();
const user = computed(() => page.props.auth.user);
const roles = computed(() => user.value.roles || []);
const isMahasiswa = computed(() => roles.value.includes('Mahasiswa'));
const isKepalaLab = computed(() => roles.value.includes('Kepala Laboratorium'));

// ── Recent Submissions ─────────────────────────────────────────────────────
const { data: recentSubmissions, loading, execute } = useApi<Pengajuan[]>('/api/pengajuan');

onMounted(() => execute('?per_page=5'));

// ── Stats ──────────────────────────────────────────────────────────────────
const stats = computed(() => {
  if (isMahasiswa.value) {
    return [
      { label: 'Total Pengajuan',       value: recentSubmissions.value?.length ?? 0,                                                     icon: ClipboardList,  color: 'text-blue-500' },
      { label: 'Menunggu Verifikasi',   value: recentSubmissions.value?.filter(p => p.status === 'diajukan').length ?? 0,                 icon: ClipboardCheck, color: 'text-amber-500' },
      { label: 'Disetujui',             value: recentSubmissions.value?.filter(p => p.status === 'disetujui').length ?? 0,                icon: FlaskConical,   color: 'text-emerald-500' },
      { label: 'Selesai',               value: recentSubmissions.value?.filter(p => p.status === 'selesai').length ?? 0,                  icon: CalendarClock,  color: 'text-violet-500' },
    ];
  }
  return [
    { label: 'Total Pengajuan Masuk',   value: 0, icon: ClipboardList,  color: 'text-blue-500' },
    { label: 'Perlu Tindakan',          value: 0, icon: ClipboardCheck, color: 'text-amber-500' },
    { label: 'Disetujui Bulan Ini',     value: 0, icon: FlaskConical,   color: 'text-emerald-500' },
    { label: 'Jadwal Hari Ini',         value: 0, icon: CalendarClock,  color: 'text-violet-500' },
  ];
});

const roleLabel = computed(() => {
  if (isMahasiswa.value)  return 'Mahasiswa';
  if (isKepalaLab.value)  return 'Kepala Laboratorium';
  return roles.value[0] ?? 'User';
});

const greeting = computed(() => {
  const h = new Date().getHours();
  if (h < 11) return 'Selamat Pagi';
  if (h < 15) return 'Selamat Siang';
  if (h < 18) return 'Selamat Sore';
  return 'Selamat Malam';
});

const today = format(new Date(), 'EEEE, dd MMMM yyyy', { locale: idLocale });
</script>

<template>
  <Head title="Dashboard" />

  <div class="flex flex-col flex-1">

    <!-- ── HERO: Greeting + AI Chat ─────────────────────────────────────── -->
    <div class="flex flex-col items-center justify-center px-6 py-10 gap-6 bg-linear-to-b from-background to-muted/30 border-b">
      <!-- Date badge -->
      <div class="flex items-center gap-2 text-xs text-muted-foreground bg-muted/60 border px-3 py-1 rounded-full">
        <Calendar class="w-3 h-3" />
        {{ today }}
      </div>

      <!-- Greeting -->
      <div class="text-center space-y-2">
        <h1 class="text-3xl md:text-4xl font-bold tracking-tight">
          {{ greeting }}, <span class="text-primary">{{ user.name }}</span>!
        </h1>
        <p class="text-muted-foreground text-base max-w-xl mx-auto">
          Akses sumber daya lab, jadwalkan layanan pengujian, dan kelola peminjaman alat dengan bantuan AI.
        </p>
        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-primary/80 bg-primary/10 border border-primary/20 rounded-full px-3 py-0.5">
          <Bot class="w-3 h-3" />
          {{ roleLabel }}
        </span>
      </div>

      <!-- AI Chat Widget (embedded) -->
      <div class="w-full max-w-2xl">
        <ChatWidget mode="embedded" />
      </div>
    </div>

    <!-- ── MAIN CONTENT ──────────────────────────────────────────────────── -->
    <div class="flex flex-col gap-6 p-6">

      <!-- Stats -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <Card v-for="stat in stats" :key="stat.label" class="hover:shadow-md transition-shadow">
          <CardContent class="p-4 flex items-center gap-4">
            <div class="p-2 rounded-xl bg-muted">
              <component :is="stat.icon" class="w-5 h-5" :class="stat.color" />
            </div>
            <div>
              <p class="text-xs text-muted-foreground leading-tight">{{ stat.label }}</p>
              <p class="text-2xl font-bold leading-tight">{{ loading ? '–' : stat.value }}</p>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Recent Submissions -->
      <Card>
        <CardHeader class="flex flex-row items-center justify-between pb-3">
          <div>
            <CardTitle class="text-base">{{ $t('Recent Booking Status') }}</CardTitle>
            <CardDescription class="text-xs">
              {{ isMahasiswa ? $t('Your last 5 submissions') : $t('Latest submissions received') }}
            </CardDescription>
          </div>
          <Button
            variant="outline"
            size="sm"
            class="text-xs"
            @click="$inertia.visit(isMahasiswa ? '/pengajuan' : (isKepalaLab ? '/kepala-lab' : '/laboran'))"
          >
            {{ $t('View All') }}
          </Button>
        </CardHeader>
        <CardContent class="p-0">
          <div v-if="loading" class="flex justify-center py-10">
            <Loader2 class="w-6 h-6 animate-spin text-muted-foreground" />
          </div>

          <template v-else-if="recentSubmissions && recentSubmissions.length > 0">
            <Table>
              <TableHeader>
                <TableRow class="bg-muted/30">
                  <TableHead class="text-xs uppercase tracking-wide">{{ $t('Resource') }}</TableHead>
                  <TableHead class="text-xs uppercase tracking-wide">{{ $t('Date') }}</TableHead>
                  <TableHead class="text-xs uppercase tracking-wide">{{ $t('Status') }}</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow
                  v-for="item in recentSubmissions"
                  :key="item.id"
                  class="cursor-pointer hover:bg-muted/50 transition-colors"
                  @click="$inertia.visit(`/pengajuan/${item.id}`)"
                >
                  <TableCell>
                    <div class="flex items-center gap-2">
                      <div class="w-7 h-7 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                        <FlaskConical v-if="item.tipe_pengajuan === 'pengujian'" class="w-3.5 h-3.5 text-primary" />
                        <ClipboardList v-else class="w-3.5 h-3.5 text-primary" />
                      </div>
                      <div class="min-w-0">
                        <p class="font-medium text-sm truncate max-w-[160px]">{{ item.judul_proyek }}</p>
                        <p class="text-xs text-muted-foreground capitalize">
                          {{ item.tipe_pengajuan === 'ruangan' ? $t('Room Loan')
                            : item.tipe_pengajuan === 'alat' ? $t('Equipment Loan')
                            : $t('Test Service') }}
                        </p>
                      </div>
                    </div>
                  </TableCell>
                  <TableCell class="text-xs text-muted-foreground whitespace-nowrap">
                    {{ format(new Date(item.dibuat_pada), 'dd MMM yyyy', { locale: idLocale }) }}
                  </TableCell>
                  <TableCell>
                    <StatusBadge :status="item.status" />
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </template>

          <div v-else class="flex flex-col items-center gap-2 py-12 text-center px-6">
            <ClipboardList class="w-8 h-8 text-muted-foreground/40" />
            <p class="text-sm font-medium">{{ $t('No submissions yet') }}</p>
            <p class="text-xs text-muted-foreground">{{ $t('Start a new submission through AI assistant or sidebar.') }}</p>
            <Button size="sm" class="mt-2" @click="$inertia.visit('/pengajuan/baru')">
              {{ $t('New Submission') }}
            </Button>
          </div>
        </CardContent>
      </Card>

    </div>
  </div>
</template>

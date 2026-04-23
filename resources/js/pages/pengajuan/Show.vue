<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { format } from 'date-fns';
import { id as idLocale } from 'date-fns/locale';
import { ArrowLeft, Calendar, CheckCircle2, FileText, User } from 'lucide-vue-next';
import { onMounted } from 'vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useApi } from '@/composables/useApi';
import { dashboard } from '@/routes';
import type { Pengajuan } from '@/types/simlab';

defineOptions({
  layout: {
    breadcrumbs: [
      { title: 'Dashboard', href: dashboard() },
      { title: 'Riwayat Pengajuan', href: '/pengajuan' },
      { title: 'Detail Pengajuan', href: '#' },
    ],
  },
});

const props = defineProps<{
  id: string;
}>();

const { data: pengajuan, loading, execute } = useApi<Pengajuan>(`/api/pengajuan/${props.id}`);

onMounted(() => {
  execute();
});
</script>

<template>
  <Head title="Detail Pengajuan" />

  <div class="flex flex-col gap-6 p-4 max-w-5xl mx-auto w-full">
    <div class="flex items-center gap-4">
      <Button variant="ghost" size="icon" @click="$inertia.visit('/pengajuan')">
        <ArrowLeft class="w-5 h-5" />
      </Button>
      <div>
        <h1 class="text-2xl font-bold tracking-tight">Detail Pengajuan</h1>
        <p class="text-muted-foreground" v-if="pengajuan">
          Dibuat pada {{ format(new Date(pengajuan.dibuat_pada), 'dd MMMM yyyy', { locale: idLocale }) }}
        </p>
      </div>
      <div class="ml-auto" v-if="pengajuan">
        <StatusBadge :status="pengajuan.status" class="text-base px-3 py-1" />
      </div>
    </div>

    <div v-if="loading" class="flex justify-center p-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
    </div>

    <template v-else-if="pengajuan">
      <!-- General Data -->
      <Card>
        <CardHeader>
          <CardTitle>{{ $t('General Data') }}</CardTitle>
        </CardHeader>
        <CardContent class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-1">
            <span class="text-sm text-muted-foreground flex items-center gap-2">
              <FileText class="w-4 h-4" /> {{ $t('Type') }}
            </span>
            <p class="font-medium capitalize">{{ pengajuan.tipe_pengajuan }}</p>
          </div>
          <div class="space-y-1">
            <span class="text-sm text-muted-foreground flex items-center gap-2">
              <FileText class="w-4 h-4" /> {{ $t('Title') }}
            </span>
            <p class="font-medium">{{ pengajuan.judul_proyek }}</p>
          </div>
          <div class="space-y-1" v-if="pengajuan.tujuan_penggunaan">
            <span class="text-sm text-muted-foreground flex items-center gap-2">
              <FileText class="w-4 h-4" /> Tujuan Penggunaan
            </span>
            <p class="font-medium">{{ pengajuan.tujuan_penggunaan }}</p>
          </div>
          <div class="space-y-1" v-if="pengajuan.dosen_pembimbing">
            <span class="text-sm text-muted-foreground flex items-center gap-2">
              <User class="w-4 h-4" /> Dosen Pembimbing
            </span>
            <p class="font-medium">{{ pengajuan.dosen_pembimbing }} ({{ pengajuan.email_dosen }})</p>
          </div>
        </CardContent>
      </Card>

      <!-- Specific Details -->
      <Card v-if="pengajuan.tipe_pengajuan === 'ruangan' && pengajuan.detail_ruangan">
        <CardHeader>
          <CardTitle>{{ $t('Specific Detail') }} - Ruangan</CardTitle>
        </CardHeader>
        <CardContent class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-1">
            <span class="text-sm text-muted-foreground flex items-center gap-2">
              <CheckCircle2 class="w-4 h-4" /> Ruangan
            </span>
            <p class="font-medium">{{ pengajuan.detail_ruangan.ruangan }}</p>
          </div>
          <div class="space-y-1">
            <span class="text-sm text-muted-foreground flex items-center gap-2">
              <User class="w-4 h-4" /> Jumlah Pengguna
            </span>
            <p class="font-medium">{{ pengajuan.detail_ruangan.jumlah_pengguna }} Orang</p>
          </div>
          <div class="space-y-1" v-if="pengajuan.detail_ruangan.tanggal_mulai">
            <span class="text-sm text-muted-foreground flex items-center gap-2">
              <Calendar class="w-4 h-4" /> Waktu Pelaksanaan
            </span>
            <p class="font-medium">
              {{ format(new Date(pengajuan.detail_ruangan.tanggal_mulai), 'dd MMM yyyy') }}
              - 
              {{ format(new Date(pengajuan.detail_ruangan.tanggal_selesai!), 'dd MMM yyyy') }}
            </p>
          </div>
        </CardContent>
      </Card>

      <Card v-if="pengajuan.tipe_pengajuan === 'alat' && pengajuan.detail_alat">
        <CardHeader>
          <CardTitle>{{ $t('Specific Detail') }} - Alat</CardTitle>
        </CardHeader>
        <CardContent class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-1">
            <span class="text-sm text-muted-foreground flex items-center gap-2">
              <CheckCircle2 class="w-4 h-4" /> Alat
            </span>
            <p class="font-medium">{{ pengajuan.detail_alat.nama_alat }}</p>
          </div>
          <div class="space-y-1">
            <span class="text-sm text-muted-foreground flex items-center gap-2">
              <CheckCircle2 class="w-4 h-4" /> Jumlah Dipinjam
            </span>
            <p class="font-medium">{{ pengajuan.detail_alat.jumlah_dipinjam }} Unit</p>
          </div>
        </CardContent>
      </Card>

      <!-- Reviewer Notes -->
      <Card v-if="pengajuan.catatan_reviewer" class="border-amber-200 dark:border-amber-900 bg-amber-50 dark:bg-amber-950/20">
        <CardHeader>
          <CardTitle class="text-amber-800 dark:text-amber-500">Catatan Reviewer</CardTitle>
        </CardHeader>
        <CardContent>
          <p class="text-amber-900 dark:text-amber-400">{{ pengajuan.catatan_reviewer }}</p>
        </CardContent>
      </Card>
    </template>
  </div>
</template>

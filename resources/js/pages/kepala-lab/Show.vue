<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { dashboard } from '@/routes';
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { ArrowLeft, Check, X, Loader2, User, FileText, Calendar, CheckCircle2 } from 'lucide-vue-next';
import { useApi } from '@/composables/useApi';
import type { Pengajuan } from '@/types/simlab';
import StatusBadge from '@/components/StatusBadge.vue';
import { format } from 'date-fns';
import { id } from 'date-fns/locale';

defineOptions({
  layout: {
    breadcrumbs: [
      { title: 'Dashboard', href: dashboard() },
      { title: 'Verifikasi Pengajuan', href: '/kepala-lab' },
      { title: 'Detail', href: '#' },
    ],
  },
});

const page = usePage<any>();
const props = defineProps<{
  id: string;
}>();

const { data: pengajuan, loading, execute } = useApi<Pengajuan>(`/api/kepala-lab/pengajuan/${props.id}`);
const { loading: submitting, execute: verifyAction, error } = useApi(`/api/kepala-lab/pengajuan/${props.id}/verifikasi`);

onMounted(() => {
  execute('');
});

const isRejecting = ref(false);
const catatan = ref('');

const onVerify = async (status: 'diverifikasi' | 'ditolak') => {
  if (status === 'ditolak' && !isRejecting.value) {
    isRejecting.value = true;
    return;
  }
  
  if (status === 'ditolak' && !catatan.value) {
    alert('Catatan penolakan wajib diisi.');
    return;
  }

  const result = await verifyAction('', {
    method: 'PATCH',
    body: JSON.stringify({
      status,
      catatan_reviewer: catatan.value
    })
  });

  if (result) {
    page.props.inertia.visit('/kepala-lab');
  }
};
</script>

<template>
  <Head title="Verifikasi Detail" />

  <div class="flex flex-col gap-6 p-4 max-w-4xl mx-auto w-full">
    <div class="flex items-center gap-4">
      <Button variant="ghost" size="icon" @click="$inertia.visit('/kepala-lab')">
        <ArrowLeft class="w-5 h-5" />
      </Button>
      <div>
        <h1 class="text-2xl font-bold tracking-tight">Detail Pengajuan</h1>
        <p class="text-muted-foreground" v-if="pengajuan">
          Diajukan oleh {{ pengajuan.pengaju?.name }}
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

      <!-- Actions -->
      <Card v-if="pengajuan.status === 'diajukan'" class="border-primary/50 shadow-md">
        <CardHeader>
          <CardTitle>Aksi Verifikasi</CardTitle>
        </CardHeader>
        <CardContent>
          <div v-if="error" class="mb-4 p-3 bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 rounded-md text-sm">
            {{ error }}
          </div>
          
          <div v-if="isRejecting" class="space-y-4 mb-4">
            <div class="space-y-2">
              <Label>Catatan Penolakan <span class="text-red-500">*</span></Label>
              <Textarea v-model="catatan" placeholder="Jelaskan alasan penolakan..." rows="3" />
            </div>
            <div class="flex gap-2">
              <Button variant="outline" @click="isRejecting = false">Batal</Button>
              <Button variant="destructive" @click="onVerify('ditolak')" :disabled="submitting || !catatan">
                <Loader2 v-if="submitting" class="w-4 h-4 mr-2 animate-spin" />
                Konfirmasi Tolak
              </Button>
            </div>
          </div>

          <div v-else class="flex flex-col sm:flex-row gap-4">
            <Button variant="destructive" class="flex-1" @click="onVerify('ditolak')">
              <X class="w-4 h-4 mr-2" /> {{ $t('Reject') }}
            </Button>
            <Button class="flex-1" @click="onVerify('diverifikasi')" :disabled="submitting">
              <Loader2 v-if="submitting" class="w-4 h-4 mr-2 animate-spin" />
              <Check v-else class="w-4 h-4 mr-2" /> {{ $t('Verify') }}
            </Button>
          </div>
        </CardContent>
      </Card>
      
      <Card v-else-if="pengajuan.catatan_reviewer" class="border-amber-200 dark:border-amber-900 bg-amber-50 dark:bg-amber-950/20">
        <CardHeader>
          <CardTitle class="text-amber-800 dark:text-amber-500">Catatan Verifikasi</CardTitle>
        </CardHeader>
        <CardContent>
          <p class="text-amber-900 dark:text-amber-400">{{ pengajuan.catatan_reviewer }}</p>
        </CardContent>
      </Card>
    </template>
  </div>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ArrowLeft, Check, CheckCircle2, FileText, Loader2, X } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useApi } from '@/composables/useApi';
import { dashboard } from '@/routes';
import type { Pengajuan } from '@/types/simlab';

defineOptions({
  layout: {
    breadcrumbs: [
      { title: 'Dashboard', href: dashboard() },
      { title: 'Validasi Laboran', href: '/laboran' },
      { title: 'Detail', href: '#' },
    ],
  },
});

const props = defineProps<{
  id: string;
}>();

const { data: pengajuan, loading, execute } = useApi<Pengajuan>(`/api/laboran/pengajuan/${props.id}`);
const { loading: submittingValidasi, execute: validateAction, error: validateError } = useApi(`/api/laboran/pengajuan/${props.id}/validasi`);
const { loading: submittingSelesai, execute: selesaiAction, error: selesaiError } = useApi(`/api/laboran/pengajuan/${props.id}/selesai`);

onMounted(() => {
  execute();
});

const catatan = ref('');
const isRejecting = ref(false);

const error = computed(() => validateError.value || selesaiError.value);
const submitting = computed(() => submittingValidasi.value || submittingSelesai.value);

const onValidate = async (status: 'disetujui' | 'ditolak') => {
  if (status === 'ditolak' && !isRejecting.value) {
    isRejecting.value = true;

    return;
  }

  if (status === 'ditolak' && !catatan.value) {
    alert('Catatan penolakan wajib diisi.');

    return;
  }

  await validateAction('', {
    method: 'PATCH',
    body: JSON.stringify({
      status,
      catatan_reviewer: catatan.value
    })
  });
};

const onSelesai = async () => {
  await selesaiAction('', {
    method: 'PATCH',
  });
};
</script>

<template>
  <Head title="Validasi Detail" />

  <div class="flex flex-col gap-6 p-4 max-w-4xl mx-auto w-full">
    <div class="flex items-center gap-4">
      <Button variant="ghost" size="icon" @click="$inertia.visit('/laboran')">
        <ArrowLeft class="w-5 h-5" />
      </Button>
      <div>
        <h1 class="text-2xl font-bold tracking-tight">Detail Pengajuan Laboran</h1>
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
        </CardContent>
      </Card>

      <!-- Actions -->
      <Card v-if="pengajuan.status === 'diverifikasi' || pengajuan.status === 'disetujui'" class="border-primary/50 shadow-md">
        <CardHeader>
          <CardTitle>Aksi Laboran</CardTitle>
        </CardHeader>
        <CardContent>
          <div v-if="error" class="mb-4 p-3 bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 rounded-md text-sm">
            {{ error }}
          </div>

          <div class="space-y-4">
            <template v-if="pengajuan.status === 'diverifikasi'">
              <div v-if="isRejecting" class="space-y-4 mb-4">
                <div class="space-y-2">
                  <Label>Catatan Penolakan <span class="text-red-500">*</span></Label>
                  <Textarea v-model="catatan" placeholder="Jelaskan alasan penolakan..." rows="3" />
                </div>
                <div class="flex gap-2">
                  <Button variant="outline" @click="isRejecting = false">Batal</Button>
                  <Button variant="destructive" @click="onValidate('ditolak')" :disabled="submitting || !catatan">
                    <Loader2 v-if="submitting" class="w-4 h-4 mr-2 animate-spin" />
                    Konfirmasi Tolak
                  </Button>
                </div>
              </div>

              <div v-else class="flex flex-col sm:flex-row gap-4">
                <Button variant="destructive" class="flex-1" @click="onValidate('ditolak')">
                  <X class="w-4 h-4 mr-2" /> {{ $t('Reject') }}
                </Button>
                <Button class="flex-1" @click="onValidate('disetujui')" :disabled="submitting">
                  <Loader2 v-if="submitting" class="w-4 h-4 mr-2 animate-spin" />
                  <Check v-else class="w-4 h-4 mr-2" /> Setujui & Mulai
                </Button>
              </div>
            </template>

            <div v-if="pengajuan.status === 'disetujui'" class="flex flex-col gap-4">
              <p class="text-sm text-muted-foreground">
                Tandai sebagai selesai jika alat sudah dikembalikan atau ruangan selesai digunakan.
              </p>
              <Button class="w-full" @click="onSelesai" :disabled="submitting">
                <Loader2 v-if="submitting" class="w-4 h-4 mr-2 animate-spin" />
                <CheckCircle2 v-else class="w-4 h-4 mr-2" /> Tandai Selesai
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card v-if="pengajuan.catatan_reviewer" class="border-blue-200 dark:border-blue-900 bg-blue-50 dark:bg-blue-950/20">
        <CardHeader>
          <CardTitle class="text-blue-800 dark:text-blue-500">Catatan Reviewer</CardTitle>
        </CardHeader>
        <CardContent>
          <p class="text-blue-900 dark:text-blue-400">{{ pengajuan.catatan_reviewer }}</p>
        </CardContent>
      </Card>
    </template>
  </div>
</template>

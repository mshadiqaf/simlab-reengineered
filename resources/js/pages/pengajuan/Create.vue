<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Loader2, Save } from 'lucide-vue-next';
import { ref, computed, onMounted } from 'vue';
import StepIndicator from '@/components/StepIndicator.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useApi } from '@/composables/useApi';
import { dashboard } from '@/routes';
import type { Ruangan, Alat, JenisPengujian } from '@/types/simlab';

defineOptions({
  layout: {
    breadcrumbs: [
      { title: 'Dashboard', href: dashboard() },
      { title: 'Riwayat Pengajuan', href: '/pengajuan' },
      { title: 'Pengajuan Baru', href: '/pengajuan/baru' },
    ],
  },
});

const page = usePage<any>();

const steps = ['Data Umum', 'Detail Spesifik', 'Review & Submit'];
const currentStep = ref(0);

// Master Data
const { data: ruanganList, execute: fetchRuangan } = useApi<Ruangan[]>('/api/ruangan');
const { data: alatList, execute: fetchAlat } = useApi<Alat[]>('/api/alat');
const { data: pengujianList, execute: fetchPengujian } = useApi<JenisPengujian[]>('/api/pengujian');

onMounted(() => {
  fetchRuangan();
  fetchAlat();
  fetchPengujian();
});

// Form Data
const form = ref({
  tipe_pengajuan: '',
  judul_proyek: '',
  tujuan_penggunaan: '',
  dosen_pembimbing: '',
  email_dosen: '',

  // Ruangan
  ruangan_id: '',
  tanggal_mulai: '',
  tanggal_selesai: '',
  waktu_mulai: '',
  waktu_selesai: '',
  jumlah_pengguna: 1,
  catatan_alat_bahan: '',

  // Alat
  alat_id: '',
  jumlah_dipinjam: 1,
  keperluan_spesifik: '',

  // Pengujian
  jenis_pengujian_id: '',
  nama_sampel: '',
  jumlah_sampel: 1,
  keterangan_tambahan: '',
});

const { loading: submitting, execute: submitForm, error: submitError } = useApi('/api/pengajuan');

const isStepValid = computed(() => {
  if (currentStep.value === 0) {
    return form.value.tipe_pengajuan && form.value.judul_proyek;
  }

  if (currentStep.value === 1) {
    if (form.value.tipe_pengajuan === 'ruangan') {
      return form.value.ruangan_id && form.value.tanggal_mulai && form.value.tanggal_selesai;
    } else if (form.value.tipe_pengajuan === 'alat') {
      return form.value.alat_id && form.value.tanggal_mulai && form.value.tanggal_selesai && form.value.jumlah_dipinjam > 0;
    } else if (form.value.tipe_pengajuan === 'pengujian') {
      return form.value.jenis_pengujian_id && form.value.nama_sampel && form.value.jumlah_sampel > 0;
    }
  }

  return true;
});

const nextStep = () => {
  if (currentStep.value < steps.length - 1 && isStepValid.value) {
    currentStep.value++;
  }
};

const prevStep = () => {
  if (currentStep.value > 0) {
    currentStep.value--;
  }
};

const onSubmit = async () => {
  // Construct payload based on type
  const payload: any = {
    tipe_pengajuan: form.value.tipe_pengajuan,
    judul_proyek: form.value.judul_proyek,
    tujuan_penggunaan: form.value.tujuan_penggunaan,
    dosen_pembimbing: form.value.dosen_pembimbing,
    email_dosen: form.value.email_dosen,
  };

  if (form.value.tipe_pengajuan === 'ruangan') {
    payload.ruangan_id = parseInt(form.value.ruangan_id);
    payload.tanggal_mulai = form.value.tanggal_mulai;
    payload.tanggal_selesai = form.value.tanggal_selesai;
    payload.waktu_mulai = form.value.waktu_mulai;
    payload.waktu_selesai = form.value.waktu_selesai;
    payload.jumlah_pengguna = form.value.jumlah_pengguna;
    payload.catatan_alat_bahan = form.value.catatan_alat_bahan;
  } else if (form.value.tipe_pengajuan === 'alat') {
    payload.alat_id = parseInt(form.value.alat_id);
    payload.tanggal_mulai = form.value.tanggal_mulai;
    payload.tanggal_selesai = form.value.tanggal_selesai;
    payload.jumlah_dipinjam = form.value.jumlah_dipinjam;
    payload.keperluan_spesifik = form.value.keperluan_spesifik;
  } else if (form.value.tipe_pengajuan === 'pengujian') {
    payload.jenis_pengujian_id = parseInt(form.value.jenis_pengujian_id);
    payload.nama_sampel = form.value.nama_sampel;
    payload.jumlah_sampel = form.value.jumlah_sampel;
    payload.keterangan_tambahan = form.value.keterangan_tambahan;
  }

  const result = await submitForm('', {
    method: 'POST',
    body: JSON.stringify(payload)
  });

  if (result) {
    page.props.inertia.visit('/pengajuan');
  }
};

// Summary labels
const typeLabel = computed(() => {
  if (form.value.tipe_pengajuan === 'ruangan') {
return 'Peminjaman Ruangan';
}

  if (form.value.tipe_pengajuan === 'alat') {
return 'Peminjaman Alat';
}

  if (form.value.tipe_pengajuan === 'pengujian') {
return 'Layanan Pengujian';
}

  return '-';
});

const resourceLabel = computed(() => {
  if (form.value.tipe_pengajuan === 'ruangan') {
    const r = ruanganList.value?.find(x => x.id === parseInt(form.value.ruangan_id));

    return r ? r.nama_ruangan : '-';
  }

  if (form.value.tipe_pengajuan === 'alat') {
    const a = alatList.value?.find(x => x.id === parseInt(form.value.alat_id));

    return a ? a.nama_alat : '-';
  }

  if (form.value.tipe_pengajuan === 'pengujian') {
    const p = pengujianList.value?.find(x => x.id === parseInt(form.value.jenis_pengujian_id));

    return p ? p.nama_pengujian : '-';
  }

  return '-';
});
</script>

<template>
  <Head title="Pengajuan Baru" />

  <div class="flex flex-col gap-6 p-4 max-w-5xl mx-auto w-full">
    <div class="flex items-center gap-4">
      <Button variant="ghost" size="icon" @click="$inertia.visit('/pengajuan')">
        <ArrowLeft class="w-5 h-5" />
      </Button>
      <div>
        <h1 class="text-2xl font-bold tracking-tight">{{ $t('New Submission') }}</h1>
        <p class="text-muted-foreground">Isi formulir berikut untuk mengajukan layanan laboratorium.</p>
      </div>
    </div>

    <StepIndicator :steps="steps" :current-step="currentStep" class="my-4" />

    <Card>
      <CardContent class="">
        <!-- STEP 0: Data Umum -->
        <div v-show="currentStep === 0" class="space-y-4">
          <div class="space-y-2">
            <Label>Tipe Pengajuan <span class="text-red-500">*</span></Label>
            <Select v-model="form.tipe_pengajuan">
              <SelectTrigger class="w-full">
                <SelectValue placeholder="Pilih tipe layanan" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectItem value="ruangan">{{ $t('Room Loan') }}</SelectItem>
                  <SelectItem value="alat">{{ $t('Equipment Loan') }}</SelectItem>
                  <SelectItem value="pengujian">{{ $t('Test Service') }}</SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
          </div>

          <div class="space-y-2">
            <Label>Judul Proyek / Penelitian <span class="text-red-500">*</span></Label>
            <Input v-model="form.judul_proyek" placeholder="Masukkan judul penelitian/tugas akhir" />
          </div>

          <div class="space-y-2">
            <Label>Tujuan Penggunaan</Label>
            <Textarea v-model="form.tujuan_penggunaan" placeholder="Jelaskan tujuan penggunaan secara singkat" rows="3" />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label>Dosen Pembimbing</Label>
              <Input v-model="form.dosen_pembimbing" placeholder="Nama lengkap dosen" />
            </div>
            <div class="space-y-2">
              <Label>Email Dosen</Label>
              <Input v-model="form.email_dosen" type="email" placeholder="Email aktif dosen" />
            </div>
          </div>
        </div>

        <!-- STEP 1: Detail Spesifik -->
        <div v-show="currentStep === 1" class="space-y-4">

          <!-- Detail Ruangan -->
          <template v-if="form.tipe_pengajuan === 'ruangan'">
            <div class="space-y-2">
              <Label>Ruangan <span class="text-red-500">*</span></Label>
              <Select v-model="form.ruangan_id">
                <SelectTrigger class="w-full">
                  <SelectValue placeholder="Pilih ruangan laboratorium" />
                </SelectTrigger>
                <SelectContent>
                  <SelectGroup>
                    <SelectItem v-for="r in ruanganList" :key="r.id" :value="r.id.toString()">
                      {{ r.nama_ruangan }} (Kap: {{ r.kapasitas }})
                    </SelectItem>
                  </SelectGroup>
                </SelectContent>
              </Select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-2">
                <Label>Tanggal Mulai <span class="text-red-500">*</span></Label>
                <Input type="date" v-model="form.tanggal_mulai" />
              </div>
              <div class="space-y-2">
                <Label>Tanggal Selesai <span class="text-red-500">*</span></Label>
                <Input type="date" v-model="form.tanggal_selesai" />
              </div>
              <div class="space-y-2">
                <Label>Waktu Mulai</Label>
                <Input type="time" v-model="form.waktu_mulai" />
              </div>
              <div class="space-y-2">
                <Label>Waktu Selesai</Label>
                <Input type="time" v-model="form.waktu_selesai" />
              </div>
              <div class="space-y-2 md:col-span-2">
                <Label>Jumlah Pengguna</Label>
                <Input type="number" v-model="form.jumlah_pengguna" min="1" />
              </div>
            </div>

            <div class="space-y-2">
              <Label>Catatan Alat & Bahan</Label>
              <Textarea v-model="form.catatan_alat_bahan" placeholder="Alat atau bahan bawaan/yang dibutuhkan" rows="3" />
            </div>
          </template>

          <!-- Detail Alat -->
          <template v-if="form.tipe_pengajuan === 'alat'">
            <div class="space-y-2">
              <Label>Alat <span class="text-red-500">*</span></Label>
              <Select v-model="form.alat_id">
                <SelectTrigger class="w-full">
                  <SelectValue placeholder="Pilih alat laboratorium" />
                </SelectTrigger>
                <SelectContent>
                  <SelectGroup>
                    <SelectItem v-for="a in alatList" :key="a.id" :value="a.id.toString()">
                      {{ a.nama_alat }} (Tersedia: {{ a.total_stok }} {{ a.satuan }})
                    </SelectItem>
                  </SelectGroup>
                </SelectContent>
              </Select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-2">
                <Label>Tanggal Mulai <span class="text-red-500">*</span></Label>
                <Input type="date" v-model="form.tanggal_mulai" />
              </div>
              <div class="space-y-2">
                <Label>Tanggal Selesai <span class="text-red-500">*</span></Label>
                <Input type="date" v-model="form.tanggal_selesai" />
              </div>
              <div class="space-y-2 md:col-span-2">
                <Label>Jumlah Dipinjam <span class="text-red-500">*</span></Label>
                <Input type="number" v-model="form.jumlah_dipinjam" min="1" />
              </div>
            </div>

            <div class="space-y-2">
              <Label>Keperluan Spesifik</Label>
              <Textarea v-model="form.keperluan_spesifik" placeholder="Penjelasan tambahan terkait penggunaan alat" rows="3" />
            </div>
          </template>

          <!-- Detail Pengujian -->
          <template v-if="form.tipe_pengajuan === 'pengujian'">
            <div class="space-y-2">
              <Label>Jenis Pengujian <span class="text-red-500">*</span></Label>
              <Select v-model="form.jenis_pengujian_id">
                <SelectTrigger class="w-full">
                  <SelectValue placeholder="Pilih jenis pengujian" />
                </SelectTrigger>
                <SelectContent>
                  <SelectGroup>
                    <SelectItem v-for="p in pengujianList" :key="p.id" :value="p.id.toString()">
                      {{ p.nama_pengujian }}
                    </SelectItem>
                  </SelectGroup>
                </SelectContent>
              </Select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-2">
                <Label>Nama Sampel <span class="text-red-500">*</span></Label>
                <Input v-model="form.nama_sampel" placeholder="Contoh: Tanah Gambut" />
              </div>
              <div class="space-y-2">
                <Label>Jumlah Sampel <span class="text-red-500">*</span></Label>
                <Input type="number" v-model="form.jumlah_sampel" min="1" />
              </div>
            </div>

            <div class="space-y-2">
              <Label>Keterangan Tambahan</Label>
              <Textarea v-model="form.keterangan_tambahan" placeholder="Instruksi khusus pengujian" rows="3" />
            </div>
          </template>
        </div>

        <!-- STEP 2: Review & Submit -->
        <div v-show="currentStep === 2" class="space-y-6">

          <div class="bg-muted p-4 rounded-lg space-y-4">
            <h3 class="font-semibold text-lg border-b pb-2">Ringkasan Pengajuan</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-3">
              <div>
                <p class="text-sm text-muted-foreground">Tipe Pengajuan</p>
                <p class="font-medium">{{ typeLabel }}</p>
              </div>
              <div>
                <p class="text-sm text-muted-foreground">Pilihan (Ruangan/Alat/Pengujian)</p>
                <p class="font-medium">{{ resourceLabel }}</p>
              </div>
              <div class="md:col-span-2">
                <p class="text-sm text-muted-foreground">Judul Proyek</p>
                <p class="font-medium">{{ form.judul_proyek }}</p>
              </div>
              <div v-if="form.tujuan_penggunaan" class="md:col-span-2">
                <p class="text-sm text-muted-foreground">Tujuan Penggunaan</p>
                <p class="font-medium">{{ form.tujuan_penggunaan }}</p>
              </div>

              <template v-if="form.tipe_pengajuan === 'ruangan' || form.tipe_pengajuan === 'alat'">
                <div>
                  <p class="text-sm text-muted-foreground">Tanggal Mulai</p>
                  <p class="font-medium">{{ form.tanggal_mulai }}</p>
                </div>
                <div>
                  <p class="text-sm text-muted-foreground">Tanggal Selesai</p>
                  <p class="font-medium">{{ form.tanggal_selesai }}</p>
                </div>
              </template>
            </div>
          </div>

          <div v-if="submitError" class="p-3 bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 rounded-md text-sm">
            {{ submitError }}
          </div>

          <p class="text-sm text-muted-foreground">
            Dengan mengirimkan pengajuan ini, Anda menyetujui seluruh tata tertib dan prosedur penggunaan fasilitas laboratorium ITK.
          </p>
        </div>
      </CardContent>

      <CardFooter class="flex justify-between border-t p-6">
        <Button variant="outline" @click="prevStep" :disabled="currentStep === 0 || submitting">
          Kembali
        </Button>
        <Button v-if="currentStep < steps.length - 1" @click="nextStep" :disabled="!isStepValid">
          Selanjutnya
        </Button>
        <Button v-else @click="onSubmit" :disabled="submitting">
          <Loader2 v-if="submitting" class="w-4 h-4 mr-2 animate-spin" />
          <Save v-else class="w-4 h-4 mr-2" />
          Kirim Pengajuan
        </Button>
      </CardFooter>
    </Card>
  </div>
</template>

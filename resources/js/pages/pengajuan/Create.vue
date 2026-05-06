<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Loader2, Save, DoorOpen, Wrench, FlaskConical, MousePointerClick, FileText, LayoutList, CheckSquare, Sparkles, X } from 'lucide-vue-next';
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
import { clearOriginalPrompt, clearSessionFormData, loadFormDataFromSession, loadOriginalPrompt } from '@/composables/useChat';
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

const steps = [
  { title: 'Tipe Layanan', icon: MousePointerClick },
  { title: 'Data Umum', icon: FileText },
  { title: 'Detail Spesifik', icon: LayoutList },
  { title: 'Review & Submit', icon: CheckSquare }
];
const currentStep   = ref(0);
const showAiBanner  = ref(false);
const originalPrompt = ref<string | null>(null);

// Master Data
const { data: ruanganList, execute: fetchRuangan } = useApi<Ruangan[]>('/api/ruangan');
const { data: alatList, execute: fetchAlat } = useApi<Alat[]>('/api/alat');
const { data: pengujianList, execute: fetchPengujian } = useApi<JenisPengujian[]>('/api/pengujian');

onMounted(async () => {
  await Promise.all([fetchRuangan(), fetchAlat(), fetchPengujian()]);
  applyAiPrefill();
});

function applyAiPrefill() {
  const aiData = loadFormDataFromSession();
  if (!aiData) return;
  clearSessionFormData();

  form.value.tipe_pengajuan = aiData.tipe_pengajuan ?? '';
  form.value.nomor_hp = aiData.nomor_hp ?? '';
  form.value.judul_proyek = aiData.judul_proyek ?? '';
  form.value.tujuan_penggunaan = aiData.tujuan_penggunaan ?? '';
  form.value.dosen_pembimbing = aiData.dosen_pembimbing ?? '';
  form.value.email_dosen = aiData.email_dosen ?? '';

  if (aiData.tipe_pengajuan === 'ruangan') {
    form.value.ruangan_id = aiData.ruangan_id ? String(aiData.ruangan_id) : '';
    form.value.tanggal_mulai = aiData.tanggal_mulai ?? '';
    form.value.tanggal_selesai = aiData.tanggal_selesai ?? '';
    form.value.waktu_mulai = aiData.waktu_mulai ?? '';
    form.value.waktu_selesai = aiData.waktu_selesai ?? '';
    form.value.jumlah_pengguna = aiData.jumlah_pengguna ?? 1;
    form.value.catatan_alat_bahan = aiData.catatan_alat_bahan ?? '';
  } else if (aiData.tipe_pengajuan === 'alat') {
    form.value.alat_id = aiData.alat_id ? String(aiData.alat_id) : '';
    form.value.tanggal_mulai = aiData.tanggal_mulai_alat ?? '';
    form.value.tanggal_selesai = aiData.tanggal_selesai_alat ?? '';
    form.value.jumlah_dipinjam = aiData.jumlah_dipinjam ?? 1;
    form.value.keperluan_spesifik = aiData.keperluan_spesifik ?? '';
  } else if (aiData.tipe_pengajuan === 'pengujian') {
    form.value.jenis_pengujian_id = aiData.jenis_pengujian_id ? String(aiData.jenis_pengujian_id) : '';
    form.value.nama_sampel = aiData.nama_sampel ?? '';
    form.value.jumlah_sampel = aiData.jumlah_sampel ?? 1;
    form.value.keterangan_tambahan = aiData.keterangan_tambahan ?? '';
  }

  // Load the original prompt for the AI banner quote
  originalPrompt.value = loadOriginalPrompt();
  clearOriginalPrompt();

  // Jump straight to Review step
  currentStep.value = steps.length - 1;
  showAiBanner.value = true;
}

// Form Data
const form = ref({
  tipe_pengajuan: '',
  nomor_hp: '',
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
    return !!form.value.tipe_pengajuan;
  }

  if (currentStep.value === 1) {
    return !!form.value.judul_proyek && !!form.value.nomor_hp;
  }

  if (currentStep.value === 2) {
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
    nomor_hp: form.value.nomor_hp,
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

// Build ordered summary rows for Step 3, resolving IDs to names
const summaryLines = computed(() => {
  const f = form.value;
  type Row = { label: string; value: string; required: boolean };
  const rows: Row[] = [];

  const req = (label: string, value: string | number | undefined | null): Row =>
    ({ label, value: value ? String(value) : '', required: true });
  const opt = (label: string, value: string | number | undefined | null): Row =>
    ({ label, value: value ? String(value) : '', required: false });

  if (f.tipe_pengajuan === 'ruangan') {
    const r = ruanganList.value?.find(x => x.id === parseInt(f.ruangan_id));
    rows.push(req('Ruangan', r?.nama_ruangan));
    rows.push(req('Tanggal Mulai', f.tanggal_mulai));
    rows.push(req('Tanggal Selesai', f.tanggal_selesai));
    rows.push(opt('Waktu', f.waktu_mulai ? `${f.waktu_mulai} – ${f.waktu_selesai}` : ''));
    rows.push(req('Jumlah Pengguna', f.jumlah_pengguna ? `${f.jumlah_pengguna} orang` : ''));
    rows.push(opt('Catatan Alat & Bahan', f.catatan_alat_bahan));
  } else if (f.tipe_pengajuan === 'alat') {
    const a = alatList.value?.find(x => x.id === parseInt(f.alat_id));
    rows.push(req('Alat', a?.nama_alat));
    rows.push(req('Jumlah Dipinjam', f.jumlah_dipinjam ? `${f.jumlah_dipinjam} unit` : ''));
    rows.push(req('Tanggal Mulai', f.tanggal_mulai));
    rows.push(req('Tanggal Selesai', f.tanggal_selesai));
    rows.push(opt('Keperluan Spesifik', f.keperluan_spesifik));
  } else if (f.tipe_pengajuan === 'pengujian') {
    const p = pengujianList.value?.find(x => x.id === parseInt(f.jenis_pengujian_id));
    rows.push(req('Jenis Pengujian', p?.nama_pengujian));
    rows.push(req('Nama Sampel', f.nama_sampel));
    rows.push(req('Jumlah Sampel', f.jumlah_sampel ? `${f.jumlah_sampel} sampel` : ''));
    rows.push(opt('Keterangan Tambahan', f.keterangan_tambahan));
  }

  rows.push(req('Judul Proyek', f.judul_proyek));
  rows.push(opt('Tujuan Penggunaan', f.tujuan_penggunaan));
  rows.push(opt('Dosen Pembimbing', f.dosen_pembimbing));
  rows.push(opt('Email Dosen', f.email_dosen));

  return rows;
});

// Submit is only allowed when all required summary lines have a value
const isReadyToSubmit = computed(() =>
  !!form.value.tipe_pengajuan &&
  summaryLines.value.filter(r => r.required).every(r => r.value.trim() !== '')
);
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

    <!-- AI Prefill Banner -->
    <div
      v-if="showAiBanner"
      class="flex items-start gap-3 bg-primary/5 border border-primary/20 rounded-xl px-4 py-3"
    >
      <Sparkles class="w-4 h-4 text-primary mt-0.5 shrink-0" />
      <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-primary">Formulir diisi oleh AI</p>
        <p class="text-xs text-muted-foreground mt-0.5">Data di bawah dihasilkan berdasarkan permintaan Anda. Periksa dan edit jika perlu sebelum mengirim.</p>
      </div>
      <button type="button" @click="showAiBanner = false" class="text-muted-foreground hover:text-foreground shrink-0">
        <X class="w-4 h-4" />
      </button>
    </div>

    <StepIndicator :steps="steps" :current-step="currentStep" class="my-4" />

    <Card>
      <CardContent class="">
        <!-- STEP 0: Tipe Layanan -->
        <div v-show="currentStep === 0" class="space-y-4">
          <Label>Pilih Tipe Layanan <span class="text-red-500">*</span></Label>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Peminjaman Ruangan -->
            <div 
              @click="form.tipe_pengajuan = 'ruangan'"
              class="border-2 rounded-xl p-6 cursor-pointer transition-all hover:border-primary/50"
              :class="form.tipe_pengajuan === 'ruangan' ? 'border-primary bg-primary/5' : 'border-border'"
            >
              <div class="flex flex-col items-center text-center space-y-3">
                <div class="p-3 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full">
                  <DoorOpen class="w-8 h-8" />
                </div>
                <h3 class="font-semibold text-lg">{{ $t('Room Loan') }}</h3>
                <p class="text-sm text-muted-foreground">Peminjaman ruangan laboratorium untuk kegiatan praktikum, penelitian, atau lainnya.</p>
              </div>
            </div>

            <!-- Peminjaman Alat -->
            <div 
              @click="form.tipe_pengajuan = 'alat'"
              class="border-2 rounded-xl p-6 cursor-pointer transition-all hover:border-primary/50"
              :class="form.tipe_pengajuan === 'alat' ? 'border-primary bg-primary/5' : 'border-border'"
            >
              <div class="flex flex-col items-center text-center space-y-3">
                <div class="p-3 bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 rounded-full">
                  <Wrench class="w-8 h-8" />
                </div>
                <h3 class="font-semibold text-lg">{{ $t('Equipment Loan') }}</h3>
                <p class="text-sm text-muted-foreground">Peminjaman peralatan dan perlengkapan laboratorium untuk menunjang kegiatan.</p>
              </div>
            </div>

            <!-- Layanan Pengujian -->
            <div 
              @click="form.tipe_pengajuan = 'pengujian'"
              class="border-2 rounded-xl p-6 cursor-pointer transition-all hover:border-primary/50"
              :class="form.tipe_pengajuan === 'pengujian' ? 'border-primary bg-primary/5' : 'border-border'"
            >
              <div class="flex flex-col items-center text-center space-y-3">
                <div class="p-3 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-full">
                  <FlaskConical class="w-8 h-8" />
                </div>
                <h3 class="font-semibold text-lg">{{ $t('Test Service') }}</h3>
                <p class="text-sm text-muted-foreground">Permintaan layanan pengujian sampel bahan atau material di laboratorium.</p>
              </div>
            </div>
          </div>
        </div>

        <!-- STEP 1: Data Umum -->
        <div v-show="currentStep === 1" class="space-y-4">
          <div class="space-y-2">
            <Label>Judul Proyek / Penelitian <span class="text-red-500">*</span></Label>
            <Input v-model="form.judul_proyek" placeholder="Masukkan judul penelitian/tugas akhir" />
          </div>

          <div class="space-y-2">
            <Label>Nomor HP <span class="text-red-500">*</span></Label>
            <Input v-model="form.nomor_hp" type="tel" placeholder="Contoh: 08123456789" />
          </div>

          <div class="space-y-2">
            <Label>Tujuan Penggunaan <span class="text-muted-foreground font-normal">(Opsional)</span></Label>
            <Textarea v-model="form.tujuan_penggunaan" class="min-h-32" placeholder="Jelaskan tujuan penggunaan secara singkat" />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label>Dosen Pembimbing <span class="text-muted-foreground font-normal">(Opsional)</span></Label>
              <Input v-model="form.dosen_pembimbing" placeholder="Nama lengkap dosen" />
            </div>
            <div class="space-y-2">
              <Label>Email Dosen <span class="text-muted-foreground font-normal">(Opsional)</span></Label>
              <Input v-model="form.email_dosen" type="email" placeholder="Email aktif dosen" />
            </div>
          </div>
        </div>

        <!-- STEP 2: Detail Spesifik -->
        <div v-show="currentStep === 2" class="space-y-4">

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
                <Label>Waktu Mulai <span class="text-muted-foreground font-normal">(Opsional)</span></Label>
                <Input type="time" v-model="form.waktu_mulai" />
              </div>
              <div class="space-y-2">
                <Label>Waktu Selesai <span class="text-muted-foreground font-normal">(Opsional)</span></Label>
                <Input type="time" v-model="form.waktu_selesai" />
              </div>
              <div class="space-y-2 md:col-span-2">
                <Label>Jumlah Pengguna</Label>
                <Input type="number" v-model="form.jumlah_pengguna" min="1" />
              </div>
            </div>

            <div class="space-y-2">
              <Label>Catatan Alat & Bahan <span class="text-muted-foreground font-normal">(Opsional)</span></Label>
              <Textarea v-model="form.catatan_alat_bahan" class="min-h-32" placeholder="Alat atau bahan bawaan/yang dibutuhkan" />
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
              <Label>Keperluan Spesifik <span class="text-muted-foreground font-normal">(Opsional)</span></Label>
              <Textarea v-model="form.keperluan_spesifik" class="min-h-32" placeholder="Penjelasan tambahan terkait penggunaan alat" />
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
              <Label>Keterangan Tambahan <span class="text-muted-foreground font-normal">(Opsional)</span></Label>
              <Textarea v-model="form.keterangan_tambahan" class="min-h-32" placeholder="Instruksi khusus pengujian" />
            </div>
          </template>
        </div>

        <!-- STEP 3: Konfirmasi (read-only summary) -->
        <div v-show="currentStep === 3" class="space-y-5">

          <!-- AI banner with original prompt quote -->
          <div v-if="showAiBanner" class="flex items-start gap-3 rounded-xl border border-primary/20 bg-primary/5 px-4 py-3">
            <Sparkles class="w-4 h-4 text-primary mt-0.5 shrink-0" />
            <div class="min-w-0">
              <p class="text-sm font-medium text-primary">Diisi oleh SIMLAB AI</p>
              <p v-if="originalPrompt" class="mt-0.5 text-xs text-muted-foreground italic truncate">
                "{{ originalPrompt }}"
              </p>
              <p class="mt-1 text-xs text-muted-foreground">
                Periksa ringkasan di bawah. Klik <strong>Kembali &amp; Edit</strong> jika ada yang perlu diubah.
              </p>
            </div>
          </div>

          <!-- Type badge -->
          <div class="text-center">
            <span class="inline-block rounded-full bg-muted px-4 py-1 text-sm font-semibold">
              {{ typeLabel || '—' }}
            </span>
          </div>

          <!-- Summary bullet list -->
          <ul class="space-y-2.5">
            <li
              v-for="row in summaryLines"
              :key="row.label"
              class="flex items-start gap-3 text-sm"
            >
              <span class="mt-0.5 text-muted-foreground/60">•</span>
              <span class="w-36 shrink-0 text-muted-foreground">{{ row.label }}</span>
              <span
                v-if="row.value"
                class="font-medium break-words"
              >{{ row.value }}</span>
              <span
                v-else-if="row.required"
                class="flex items-center gap-1 text-amber-600 dark:text-amber-400 font-medium"
              >
                ⚠ Perlu diisi
              </span>
              <span v-else class="text-muted-foreground/50">—</span>
            </li>
          </ul>

          <!-- Warning if fields are missing -->
          <p v-if="!isReadyToSubmit" class="rounded-lg bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 px-4 py-2.5 text-xs text-amber-700 dark:text-amber-400">
            Beberapa field wajib belum diisi. Klik <strong>Kembali &amp; Edit</strong> untuk melengkapi data.
          </p>

          <div v-if="submitError" class="rounded-md bg-red-100 dark:bg-red-900/30 px-3 py-2 text-sm text-red-800 dark:text-red-400">
            {{ submitError }}
          </div>

          <p class="text-xs text-muted-foreground">
            Dengan mengirimkan pengajuan ini, Anda menyetujui seluruh tata tertib dan prosedur penggunaan fasilitas laboratorium ITK.
          </p>
        </div>
      </CardContent>

      <CardFooter class="flex justify-between border-t pt-6">
        <!-- Step 3: show "Kembali & Edit" → goes to step 0, keeping prefilled data -->
        <Button
          v-if="currentStep === steps.length - 1"
          variant="outline"
          @click="currentStep = 0"
          :disabled="submitting"
        >
          ← Kembali &amp; Edit
        </Button>
        <Button
          v-else
          variant="outline"
          @click="prevStep"
          :disabled="currentStep === 0 || submitting"
        >
          Kembali
        </Button>

        <Button v-if="currentStep < steps.length - 1" @click="nextStep" :disabled="!isStepValid">
          Selanjutnya
        </Button>
        <Button v-else @click="onSubmit" :disabled="submitting || !isReadyToSubmit">
          <Loader2 v-if="submitting" class="w-4 h-4 mr-2 animate-spin" />
          <Save v-else class="w-4 h-4 mr-2" />
          Kirim Pengajuan
        </Button>
      </CardFooter>
    </Card>
  </div>
</template>

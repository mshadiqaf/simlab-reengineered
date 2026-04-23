<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { DoorOpen, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';
import EmptyState from '@/components/EmptyState.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import { useApi } from '@/composables/useApi';
import { dashboard } from '@/routes';
import type { Ruangan } from '@/types/simlab';

defineOptions({
  layout: {
    breadcrumbs: [
      { title: 'Dashboard', href: dashboard() },
      { title: 'Master Data', href: '#' },
      { title: 'Ruangan', href: '/master-data/ruangan' },
    ],
  },
});

const { data: ruanganList, loading, execute: fetchRuangan } = useApi<Ruangan[]>('/api/ruangan');

onMounted(() => {
  fetchRuangan();
});

// Dialog state
const showDialog = ref(false);
const editMode = ref(false);
const formData = ref({
  id: null as number | null,
  nama_ruangan: '',
  kapasitas: 1,
  deskripsi: '',
});

const openAddDialog = () => {
  editMode.value = false;
  formData.value = { id: null, nama_ruangan: '', kapasitas: 1, deskripsi: '' };
  showDialog.value = true;
};

const openEditDialog = (ruangan: Ruangan) => {
  editMode.value = true;
  formData.value = {
    id: ruangan.id,
    nama_ruangan: ruangan.nama_ruangan,
    kapasitas: ruangan.kapasitas,
    deskripsi: ruangan.deskripsi ?? '',
  };
  showDialog.value = true;
};

// Placeholder save action (API not implemented for write yet)
const saving = ref(false);
const onSave = async () => {
  saving.value = true;
  // TODO: call POST/PUT /api/master-data/ruangan when backend is ready
  await new Promise((r) => setTimeout(r, 800)); // simulate API delay
  saving.value = false;
  showDialog.value = false;
  fetchRuangan();
};
</script>

<template>
  <Head title="Master Data Ruangan" />

  <div class="flex flex-col gap-6 p-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">{{ $t('Room Data') }}</h1>
        <p class="text-muted-foreground">Kelola data ruangan laboratorium yang tersedia</p>
      </div>
      <Button @click="openAddDialog">
        <Plus class="w-4 h-4 mr-2" />
        {{ $t('Add Room') }}
      </Button>
    </div>

    <!-- Table Card -->
    <Card>
      <CardHeader>
        <CardTitle class="flex items-center gap-2">
          <DoorOpen class="w-5 h-5" />
          Daftar Ruangan
        </CardTitle>
        <CardDescription>
          {{ ruanganList?.length ?? 0 }} ruangan terdaftar
        </CardDescription>
      </CardHeader>
      <CardContent class="p-0">
        <div v-if="loading" class="flex justify-center p-12">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
        </div>

        <template v-else-if="ruanganList && ruanganList.length > 0">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>#</TableHead>
                <TableHead>Nama Ruangan</TableHead>
                <TableHead class="text-center">Kapasitas</TableHead>
                <TableHead>Deskripsi</TableHead>
                <TableHead class="w-24">Aksi</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="(ruangan, idx) in ruanganList" :key="ruangan.id" class="hover:bg-muted/50">
                <TableCell class="text-muted-foreground text-sm">{{ idx + 1 }}</TableCell>
                <TableCell class="font-medium">{{ ruangan.nama_ruangan }}</TableCell>
                <TableCell class="text-center">{{ ruangan.kapasitas }} Orang</TableCell>
                <TableCell class="text-muted-foreground text-sm max-w-xs truncate">
                  {{ ruangan.deskripsi ?? '-' }}
                </TableCell>
                <TableCell>
                  <div class="flex gap-1">
                    <Button variant="ghost" size="icon" @click="openEditDialog(ruangan)">
                      <Pencil class="w-4 h-4" />
                    </Button>
                    <Button variant="ghost" size="icon" class="text-destructive hover:text-destructive">
                      <Trash2 class="w-4 h-4" />
                    </Button>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </template>

        <EmptyState
          v-else
          title="Belum ada ruangan"
          description="Tambahkan data ruangan laboratorium pertama Anda."
          :icon="DoorOpen"
        />
      </CardContent>
    </Card>
  </div>

  <!-- Add/Edit Dialog -->
  <Dialog v-model:open="showDialog">
    <DialogContent class="sm:max-w-md">
      <DialogHeader>
        <DialogTitle>{{ editMode ? $t('Edit Room') : $t('Add Room') }}</DialogTitle>
        <DialogDescription>
          {{ editMode ? 'Perbarui informasi ruangan laboratorium.' : 'Tambahkan ruangan laboratorium baru ke sistem.' }}
        </DialogDescription>
      </DialogHeader>

      <div class="flex flex-col gap-4 py-2">
        <div class="space-y-2">
          <Label>Nama Ruangan <span class="text-destructive">*</span></Label>
          <Input v-model="formData.nama_ruangan" placeholder="Contoh: Lab Fisika Dasar A (R202)" />
        </div>
        <div class="space-y-2">
          <Label>Kapasitas (Orang) <span class="text-destructive">*</span></Label>
          <Input v-model="formData.kapasitas" type="number" min="1" placeholder="40" />
        </div>
        <div class="space-y-2">
          <Label>Deskripsi</Label>
          <Textarea v-model="formData.deskripsi" placeholder="Deskripsi singkat fasilitas ruangan..." rows="3" />
        </div>
      </div>

      <DialogFooter>
        <Button variant="outline" @click="showDialog = false">Batal</Button>
        <Button @click="onSave" :disabled="saving || !formData.nama_ruangan">
          <span v-if="saving" class="w-4 h-4 mr-2 animate-spin rounded-full border-2 border-current border-t-transparent" />
          {{ editMode ? 'Simpan Perubahan' : 'Tambah Ruangan' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

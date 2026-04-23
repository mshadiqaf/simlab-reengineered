<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2, Wrench } from 'lucide-vue-next';
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
import type { Alat } from '@/types/simlab';

defineOptions({
  layout: {
    breadcrumbs: [
      { title: 'Dashboard', href: dashboard() },
      { title: 'Master Data', href: '#' },
      { title: 'Alat', href: '/master-data/alat' },
    ],
  },
});

const { data: alatList, loading, execute: fetchAlat } = useApi<Alat[]>('/api/alat');

onMounted(() => {
  fetchAlat();
});

// Dialog state
const showDialog = ref(false);
const editMode = ref(false);
const formData = ref({
  id: null as number | null,
  nama_alat: '',
  total_stok: 1,
  satuan: '',
  deskripsi: '',
});

const openAddDialog = () => {
  editMode.value = false;
  formData.value = { id: null, nama_alat: '', total_stok: 1, satuan: 'Unit', deskripsi: '' };
  showDialog.value = true;
};

const openEditDialog = (alat: Alat) => {
  editMode.value = true;
  formData.value = {
    id: alat.id,
    nama_alat: alat.nama_alat,
    total_stok: alat.total_stok,
    satuan: alat.satuan,
    deskripsi: '',
  };
  showDialog.value = true;
};

// Placeholder save action
const saving = ref(false);
const onSave = async () => {
  saving.value = true;
  // TODO: call POST/PUT /api/master-data/alat when backend is ready
  await new Promise((r) => setTimeout(r, 800));
  saving.value = false;
  showDialog.value = false;
  fetchAlat();
};
</script>

<template>
  <Head title="Master Data Alat" />

  <div class="flex flex-col gap-6 p-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">{{ $t('Equipment Data') }}</h1>
        <p class="text-muted-foreground">Kelola data alat laboratorium yang tersedia untuk dipinjam</p>
      </div>
      <Button @click="openAddDialog">
        <Plus class="w-4 h-4 mr-2" />
        {{ $t('Add Equipment') }}
      </Button>
    </div>

    <!-- Table Card -->
    <Card>
      <CardHeader>
        <CardTitle class="flex items-center gap-2">
          <Wrench class="w-5 h-5" />
          Daftar Alat
        </CardTitle>
        <CardDescription>
          {{ alatList?.length ?? 0 }} alat terdaftar
        </CardDescription>
      </CardHeader>
      <CardContent class="p-0">
        <div v-if="loading" class="flex justify-center p-12">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
        </div>

        <template v-else-if="alatList && alatList.length > 0">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>#</TableHead>
                <TableHead>Nama Alat</TableHead>
                <TableHead class="text-center">Stok</TableHead>
                <TableHead class="text-center">Satuan</TableHead>
                <TableHead class="w-24">Aksi</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="(alat, idx) in alatList" :key="alat.id" class="hover:bg-muted/50">
                <TableCell class="text-muted-foreground text-sm">{{ idx + 1 }}</TableCell>
                <TableCell class="font-medium">{{ alat.nama_alat }}</TableCell>
                <TableCell class="text-center">
                  <span :class="alat.total_stok > 0 ? 'text-green-600 dark:text-green-400 font-semibold' : 'text-destructive font-semibold'">
                    {{ alat.total_stok }}
                  </span>
                </TableCell>
                <TableCell class="text-center text-muted-foreground">{{ alat.satuan }}</TableCell>
                <TableCell>
                  <div class="flex gap-1">
                    <Button variant="ghost" size="icon" @click="openEditDialog(alat)">
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
          title="Belum ada alat"
          description="Tambahkan data alat laboratorium pertama Anda."
          :icon="Wrench"
        />
      </CardContent>
    </Card>
  </div>

  <!-- Add/Edit Dialog -->
  <Dialog v-model:open="showDialog">
    <DialogContent class="sm:max-w-md">
      <DialogHeader>
        <DialogTitle>{{ editMode ? $t('Edit Equipment') : $t('Add Equipment') }}</DialogTitle>
        <DialogDescription>
          {{ editMode ? 'Perbarui informasi alat laboratorium.' : 'Tambahkan alat laboratorium baru ke sistem.' }}
        </DialogDescription>
      </DialogHeader>

      <div class="flex flex-col gap-4 py-2">
        <div class="space-y-2">
          <Label>Nama Alat <span class="text-destructive">*</span></Label>
          <Input v-model="formData.nama_alat" placeholder="Contoh: Osiloskop Digital" />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label>Total Stok <span class="text-destructive">*</span></Label>
            <Input v-model="formData.total_stok" type="number" min="0" placeholder="10" />
          </div>
          <div class="space-y-2">
            <Label>Satuan <span class="text-destructive">*</span></Label>
            <Input v-model="formData.satuan" placeholder="Unit / Buah / Set" />
          </div>
        </div>
        <div class="space-y-2">
          <Label>Deskripsi</Label>
          <Textarea v-model="formData.deskripsi" placeholder="Spesifikasi atau keterangan singkat alat..." rows="3" />
        </div>
      </div>

      <DialogFooter>
        <Button variant="outline" @click="showDialog = false">Batal</Button>
        <Button @click="onSave" :disabled="saving || !formData.nama_alat || !formData.satuan">
          <span v-if="saving" class="w-4 h-4 mr-2 animate-spin rounded-full border-2 border-current border-t-transparent" />
          {{ editMode ? 'Simpan Perubahan' : 'Tambah Alat' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

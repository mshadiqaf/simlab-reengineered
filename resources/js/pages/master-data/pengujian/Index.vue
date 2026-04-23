<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { FlaskConical, Pencil, Plus, Trash2 } from 'lucide-vue-next';
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
import type { JenisPengujian } from '@/types/simlab';

defineOptions({
  layout: {
    breadcrumbs: [
      { title: 'Dashboard', href: dashboard() },
      { title: 'Master Data', href: '#' },
      { title: 'Jenis Pengujian', href: '/master-data/pengujian' },
    ],
  },
});

const { data: pengujianList, loading, execute: fetchPengujian } = useApi<JenisPengujian[]>('/api/jenis-pengujian');

onMounted(() => {
  fetchPengujian();
});

// Dialog state
const showDialog = ref(false);
const editMode = ref(false);
const formData = ref({
  id: null as number | null,
  nama_pengujian: '',
  deskripsi: '',
});

const openAddDialog = () => {
  editMode.value = false;
  formData.value = { id: null, nama_pengujian: '', deskripsi: '' };
  showDialog.value = true;
};

const openEditDialog = (item: JenisPengujian) => {
  editMode.value = true;
  formData.value = {
    id: item.id,
    nama_pengujian: item.nama_pengujian,
    deskripsi: item.deskripsi ?? '',
  };
  showDialog.value = true;
};

// Placeholder save action
const saving = ref(false);
const onSave = async () => {
  saving.value = true;
  // TODO: call POST/PUT /api/master-data/pengujian when backend is ready
  await new Promise((r) => setTimeout(r, 800));
  saving.value = false;
  showDialog.value = false;
  fetchPengujian();
};
</script>

<template>
  <Head title="Master Data Jenis Pengujian" />

  <div class="flex flex-col gap-6 p-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">{{ $t('Test Service Data') }}</h1>
        <p class="text-muted-foreground">Kelola jenis layanan pengujian yang disediakan laboratorium</p>
      </div>
      <Button @click="openAddDialog">
        <Plus class="w-4 h-4 mr-2" />
        {{ $t('Add Test Type') }}
      </Button>
    </div>

    <!-- Table Card -->
    <Card>
      <CardHeader>
        <CardTitle class="flex items-center gap-2">
          <FlaskConical class="w-5 h-5" />
          Daftar Jenis Pengujian
        </CardTitle>
        <CardDescription>
          {{ pengujianList?.length ?? 0 }} jenis pengujian terdaftar
        </CardDescription>
      </CardHeader>
      <CardContent class="p-0">
        <div v-if="loading" class="flex justify-center p-12">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
        </div>

        <template v-else-if="pengujianList && pengujianList.length > 0">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>#</TableHead>
                <TableHead>Nama Pengujian</TableHead>
                <TableHead>Deskripsi</TableHead>
                <TableHead class="w-24">Aksi</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="(item, idx) in pengujianList" :key="item.id" class="hover:bg-muted/50">
                <TableCell class="text-muted-foreground text-sm">{{ idx + 1 }}</TableCell>
                <TableCell class="font-medium">{{ item.nama_pengujian }}</TableCell>
                <TableCell class="text-muted-foreground text-sm max-w-xs truncate">
                  {{ item.deskripsi ?? '-' }}
                </TableCell>
                <TableCell>
                  <div class="flex gap-1">
                    <Button variant="ghost" size="icon" @click="openEditDialog(item)">
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
          title="Belum ada jenis pengujian"
          description="Tambahkan jenis layanan pengujian laboratorium pertama Anda."
          :icon="FlaskConical"
        />
      </CardContent>
    </Card>
  </div>

  <!-- Add/Edit Dialog -->
  <Dialog v-model:open="showDialog">
    <DialogContent class="sm:max-w-md">
      <DialogHeader>
        <DialogTitle>{{ editMode ? $t('Edit Test Type') : $t('Add Test Type') }}</DialogTitle>
        <DialogDescription>
          {{ editMode ? 'Perbarui informasi jenis pengujian.' : 'Tambahkan jenis layanan pengujian baru ke sistem.' }}
        </DialogDescription>
      </DialogHeader>

      <div class="flex flex-col gap-4 py-2">
        <div class="space-y-2">
          <Label>Nama Pengujian <span class="text-destructive">*</span></Label>
          <Input v-model="formData.nama_pengujian" placeholder="Contoh: Uji Tarik Material" />
        </div>
        <div class="space-y-2">
          <Label>Deskripsi</Label>
          <Textarea v-model="formData.deskripsi" placeholder="Keterangan singkat tentang jenis pengujian ini..." rows="3" />
        </div>
      </div>

      <DialogFooter>
        <Button variant="outline" @click="showDialog = false">Batal</Button>
        <Button @click="onSave" :disabled="saving || !formData.nama_pengujian">
          <span v-if="saving" class="w-4 h-4 mr-2 animate-spin rounded-full border-2 border-current border-t-transparent" />
          {{ editMode ? 'Simpan Perubahan' : 'Tambah Jenis Pengujian' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

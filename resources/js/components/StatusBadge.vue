<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';

const props = defineProps<{
  status: string;
}>();

const variant = computed(() => {
  switch (props.status?.toLowerCase()) {
    case 'diajukan':
      return 'outline';
    case 'diverifikasi':
      return 'secondary';
    case 'disetujui':
      return 'default';
    case 'ditolak':
      return 'destructive';
    case 'selesai':
      return 'default';
    default:
      return 'outline';
  }
});

const label = computed(() => {
  switch (props.status?.toLowerCase()) {
    case 'diajukan':
      return 'Diajukan';
    case 'diverifikasi':
      return 'Diverifikasi';
    case 'disetujui':
      return 'Disetujui';
    case 'ditolak':
      return 'Ditolak';
    case 'selesai':
      return 'Selesai';
    default:
      return props.status || 'Unknown';
  }
});
</script>

<template>
  <Badge :variant="variant" class="font-medium px-2 py-0.5 whitespace-nowrap capitalize">
    {{ $t(label) }}
  </Badge>
</template>

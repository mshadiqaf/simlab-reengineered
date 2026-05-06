<script setup lang="ts">
import { ArrowRight, Loader2, Sparkles } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import type { ChatFormData, ChatMessage, Suggestion } from '@/composables/useChat';

const props = defineProps<{
  messages: ChatMessage[];
  loading: boolean;
  pendingFormData: ChatFormData | null;
  currentSuggestions: Suggestion | null;
  hasStarted: boolean;
}>();

const emit = defineEmits<{
  suggestion: [label: string];
  skip: [];
  goToForm: [];
}>();

// Local state for the dropdown selection — resets whenever suggestions change
const selectedValue = ref('');
watch(() => props.currentSuggestions, () => { selectedValue.value = ''; });

const confirmSelection = () => {
  if (!selectedValue.value) return;
  emit('suggestion', selectedValue.value);
  selectedValue.value = '';
};

const tipeLabel = (tipe?: string) => {
  const map: Record<string, string> = {
    ruangan: 'Peminjaman Ruangan',
    alat: 'Peminjaman Alat',
    pengujian: 'Layanan Pengujian',
  };
  return map[tipe ?? ''] ?? tipe ?? '—';
};
</script>

<template>
  <!-- Messages -->
  <div
    v-for="(msg, idx) in messages"
    :key="idx"
    :class="['flex gap-2 items-start', msg.role === 'user' ? 'flex-row-reverse' : 'flex-row']"
  >
    <div
      :class="[
        'shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold',
        msg.role === 'user' ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground',
      ]"
    >
      {{ msg.role === 'user' ? 'U' : 'AI' }}
    </div>
    <div
      :class="[
        'rounded-2xl px-3 py-2 text-sm max-w-[82%] whitespace-pre-wrap leading-relaxed',
        msg.role === 'user'
          ? 'bg-primary text-primary-foreground rounded-tr-sm'
          : 'bg-muted text-foreground rounded-tl-sm',
      ]"
    >
      {{ msg.content }}
    </div>
  </div>

  <!-- Typing indicator -->
  <div v-if="loading" class="flex gap-2 items-start">
    <div class="shrink-0 w-7 h-7 rounded-full bg-muted flex items-center justify-center text-xs font-bold text-muted-foreground">AI</div>
    <div class="bg-muted rounded-2xl rounded-tl-sm px-4 py-3 flex gap-1 items-center">
      <span class="w-1.5 h-1.5 rounded-full bg-muted-foreground/60 animate-bounce [animation-delay:0ms]" />
      <span class="w-1.5 h-1.5 rounded-full bg-muted-foreground/60 animate-bounce [animation-delay:150ms]" />
      <span class="w-1.5 h-1.5 rounded-full bg-muted-foreground/60 animate-bounce [animation-delay:300ms]" />
    </div>
  </div>

  <!-- Summary card — AI has enough data -->
  <div v-if="pendingFormData && !loading" class="ml-9 mt-1">
    <div class="border border-primary/30 bg-primary/5 rounded-2xl rounded-tl-sm p-4 space-y-3 max-w-[82%]">
      <div class="flex items-center gap-2">
        <Sparkles class="w-4 h-4 text-primary shrink-0" />
        <span class="text-sm font-semibold text-primary">Formulir siap dibuat</span>
      </div>
      <p class="text-xs text-muted-foreground">
        Tipe: <span class="font-medium text-foreground">{{ tipeLabel(pendingFormData.tipe_pengajuan) }}</span>
      </p>
      <p class="text-xs text-muted-foreground leading-relaxed">
        Data yang sudah dikumpulkan akan diisi otomatis. Anda bisa mengedit sebelum mengirim.
      </p>
      <Button size="sm" class="w-full gap-2" @click="emit('goToForm')">
        Buat Pengajuan <ArrowRight class="w-3.5 h-3.5" />
      </Button>
    </div>
  </div>

  <!-- Interactive inputs — shown after first exchange, while no formData yet -->
  <div v-if="hasStarted && !pendingFormData && !loading" class="ml-9 space-y-2">

    <!-- Options: proper Select dropdown for large lists (rooms, equipment, test types) -->
    <div v-if="currentSuggestions?.type === 'options'" class="flex items-center gap-2 max-w-[85%]">
      <Select v-model="selectedValue">
        <SelectTrigger class="flex-1 text-sm h-9">
          <SelectValue placeholder="Pilih salah satu..." />
        </SelectTrigger>
        <SelectContent>
          <SelectItem
            v-for="item in currentSuggestions.items"
            :key="item.value"
            :value="item.label"
          >
            {{ item.label }}
          </SelectItem>
        </SelectContent>
      </Select>
      <Button size="sm" class="h-9 shrink-0" :disabled="!selectedValue" @click="confirmSelection">
        Pilih
      </Button>
    </div>

    <!-- Confirm: two buttons (only 2 options, buttons are appropriate here) -->
    <div v-else-if="currentSuggestions?.type === 'confirm'" class="flex gap-2">
      <Button
        v-for="item in currentSuggestions.items"
        :key="item.value"
        size="sm"
        variant="outline"
        class="text-xs"
        @click="emit('suggestion', item.label)"
      >
        {{ item.label }}
      </Button>
    </div>

    <!-- Always-visible skip button -->
    <Button
      type="button"
      variant="outline"
      size="sm"
      class="w-full max-w-[85%] text-xs"
      @click="emit('skip')"
    >
      Lanjut ke Formulir
    </Button>
  </div>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { RotateCcw, Send, Sparkles, X } from 'lucide-vue-next';
import { nextTick, ref, watch } from 'vue';
import ChatMessages from '@/components/ChatMessages.vue';
import { Button } from '@/components/ui/button';
import { saveFormDataToSession, saveOriginalPrompt, useChat } from '@/composables/useChat';

const props = withDefaults(defineProps<{ mode?: 'embedded' | 'floating' }>(), {
  mode: 'floating',
});

// ── State ──────────────────────────────────────────────────────────────────────
const isOpen       = ref(false); // floating panel open/close
const chatInput    = ref('');
const chatContainer = ref<HTMLElement | null>(null);

const {
  messages,
  loading,
  error,
  pendingFormData,
  currentSuggestions,
  hasStarted,
  sendMessage,
  clearChat,
} = useChat();

// ── Scroll ─────────────────────────────────────────────────────────────────────
const scrollToBottom = async () => {
  await nextTick();
  if (chatContainer.value) {
    chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
  }
};

watch(messages, scrollToBottom, { deep: true });

// ── Actions ────────────────────────────────────────────────────────────────────
const send = async (text?: string) => {
  const msg = text ?? chatInput.value.trim();
  if (!msg || loading.value) return;
  chatInput.value = '';
  await sendMessage(msg);
};

const handleSuggestion = (label: string) => send(label);

// Pure client-side: take whatever partial data exists and go straight to the form.
// No AI call. The summary page will flag any missing required fields.
const handleSkip = () => {
  const firstUser = messages.value.find(m => m.role === 'user');
  if (firstUser) saveOriginalPrompt(firstUser.content);
  if (pendingFormData.value) saveFormDataToSession(pendingFormData.value);
  router.visit('/pengajuan/baru');
};

const goToForm = () => {
  // Save the first user message as the original prompt for the banner
  const firstUser = messages.value.find(m => m.role === 'user');
  if (firstUser) saveOriginalPrompt(firstUser.content);
  if (pendingFormData.value) saveFormDataToSession(pendingFormData.value);
  router.visit('/pengajuan/baru');
};

const handleClose = () => {
  isOpen.value = false;
  clearChat();
};

// ── Quick prompts (shown before user starts) ───────────────────────────────────
const quickPrompts = [
  'Saya ingin meminjam ruangan lab',
  'Saya butuh meminjam alat laboratorium',
  'Saya ingin mengajukan layanan pengujian',
];
</script>

<template>
  <!-- ══════════════════════════════════════════════════════════════════
       FLOATING MODE
  ══════════════════════════════════════════════════════════════════ -->
  <template v-if="mode === 'floating'">
    <!-- FAB button -->
    <button
      v-if="!isOpen"
      @click="isOpen = true"
      class="fixed bottom-5 right-5 z-50 w-10 h-10 rounded-full bg-primary text-primary-foreground shadow-md hover:shadow-lg hover:scale-105 transition-all flex items-center justify-center"
      aria-label="Buka SIMLAB AI"
    >
      <Sparkles class="w-4 h-4" />
    </button>

    <!-- Floating panel -->
    <div
      v-if="isOpen"
      class="fixed bottom-6 right-6 z-50 w-[380px] max-h-[560px] flex flex-col bg-background border rounded-2xl shadow-2xl overflow-hidden"
    >
      <!-- Header -->
      <div class="flex items-center justify-between px-4 py-3 border-b bg-muted/30">
        <div class="flex items-center gap-2">
          <Sparkles class="w-4 h-4 text-primary" />
          <span class="font-semibold text-sm">SIMLAB AI</span>
        </div>
        <div class="flex items-center gap-1">
          <button @click="clearChat" class="p-1.5 rounded-lg hover:bg-muted text-muted-foreground hover:text-foreground transition-colors" title="Mulai ulang">
            <RotateCcw class="w-4 h-4" />
          </button>
          <button @click="handleClose" class="p-1.5 rounded-lg hover:bg-muted text-muted-foreground hover:text-foreground transition-colors" title="Tutup">
            <X class="w-4 h-4" />
          </button>
        </div>
      </div>

      <!-- Messages -->
      <div ref="chatContainer" class="flex-1 overflow-y-auto p-4 space-y-3 min-h-0">
        <ChatMessages
          :messages="messages"
          :loading="loading"
          :pending-form-data="pendingFormData"
          :current-suggestions="currentSuggestions"
          :has-started="hasStarted"
          @suggestion="handleSuggestion"
          @skip="handleSkip"
          @go-to-form="goToForm"
        />
      </div>

      <!-- Error -->
      <p v-if="error" class="px-4 py-1.5 text-xs text-red-500 border-t bg-red-50 dark:bg-red-950/20">{{ error }}</p>

      <!-- Input -->
      <div class="border-t p-3">
        <form @submit.prevent="send()" class="flex items-center gap-2 bg-muted/50 rounded-xl px-3 py-2">
          <input
            v-model="chatInput"
            placeholder="Ketik pesan..."
            class="flex-1 bg-transparent outline-none text-sm placeholder:text-muted-foreground"
            :disabled="loading || !!pendingFormData"
          />
          <button
            type="submit"
            :disabled="loading || !chatInput.trim() || !!pendingFormData"
            class="text-primary disabled:text-muted-foreground/50 transition-colors"
          >
            <Send class="w-4 h-4" />
          </button>
        </form>
      </div>
    </div>
  </template>

  <!-- ══════════════════════════════════════════════════════════════════
       EMBEDDED MODE (Dashboard hero)
  ══════════════════════════════════════════════════════════════════ -->
  <template v-else>
    <!-- Hero input bar -->
    <form
      @submit.prevent="send()"
      class="flex items-center gap-3 bg-background border-2 border-primary/30 hover:border-primary/60 focus-within:border-primary rounded-2xl px-4 py-3 shadow-lg shadow-primary/5 transition-all"
    >
      <Sparkles class="w-5 h-5 text-primary shrink-0 animate-pulse" />
      <input
        v-model="chatInput"
        placeholder="Deskripsikan kebutuhan Anda (mis: pinjam ruangan besok jam 10)"
        class="flex-1 bg-transparent outline-none text-sm text-foreground placeholder:text-muted-foreground"
        :disabled="loading || !!pendingFormData"
      />
      <Button type="submit" size="sm" :disabled="loading || !chatInput.trim() || !!pendingFormData" class="rounded-xl px-4">
        <span>Kirim</span>
      </Button>
    </form>

    <!-- Quick prompts — only before user starts -->
    <div v-if="!hasStarted" class="flex flex-wrap gap-2 mt-3 justify-center">
      <button
        v-for="prompt in quickPrompts"
        :key="prompt"
        type="button"
        @click="send(prompt)"
        class="text-xs bg-muted hover:bg-muted/80 border rounded-full px-3 py-1 transition-colors text-muted-foreground hover:text-foreground"
      >
        {{ prompt }}
      </button>
    </div>

    <!-- Chat expansion panel -->
    <div v-if="hasStarted" class="mt-4 bg-background border rounded-2xl shadow-md overflow-hidden">
      <!-- Messages -->
      <div ref="chatContainer" class="p-4 max-h-80 overflow-y-auto space-y-3">
        <ChatMessages
          :messages="messages"
          :loading="loading"
          :pending-form-data="pendingFormData"
          :current-suggestions="currentSuggestions"
          :has-started="hasStarted"
          @suggestion="handleSuggestion"
          @skip="handleSkip"
          @go-to-form="goToForm"
        />
      </div>

      <!-- Footer -->
      <div class="border-t px-4 py-2 flex justify-between items-center">
        <span v-if="error" class="text-xs text-red-500">{{ error }}</span>
        <span v-else class="text-xs text-muted-foreground">Berikan detail sebanyak mungkin untuk hasil terbaik</span>
        <button type="button" @click="clearChat" class="text-xs text-muted-foreground hover:text-foreground underline">
          Mulai ulang
        </button>
      </div>
    </div>
  </template>
</template>

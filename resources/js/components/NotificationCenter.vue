<script setup lang="ts">
import { Bell, CheckCheck, ClipboardCheck, FlaskConical, Info } from 'lucide-vue-next';
import { useTemplateRef } from 'vue';
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Button } from '@/components/ui/button';

interface Notification {
  id: number;
  type: 'submission' | 'approval' | 'info';
  title: string;
  message: string;
  time: string;
  read: boolean;
}

// Placeholder notifications — will be wired to API when backend is ready
const notifications = ref<Notification[]>([
  {
    id: 1,
    type: 'approval',
    title: 'Pengajuan Disetujui',
    message: 'Pengajuan ruangan untuk "Riset Material Komposit" telah disetujui.',
    time: '5 menit lalu',
    read: false,
  },
  {
    id: 2,
    type: 'submission',
    title: 'Pengajuan Baru Masuk',
    message: 'Ada pengajuan baru dari Budi Santoso — Alat: Osiloskop Digital.',
    time: '1 jam lalu',
    read: false,
  },
  {
    id: 3,
    type: 'info',
    title: 'Pemeliharaan Terjadwal',
    message: 'Lab akan tutup pada 26 Apr 2026 untuk pemeliharaan rutin.',
    time: '2 jam lalu',
    read: true,
  },
]);

const open = ref(false);
const panelRef = useTemplateRef<HTMLElement>('panelRef');
const triggerRef = useTemplateRef<HTMLElement>('triggerRef');

const unreadCount = computed(() => notifications.value.filter((n) => !n.read).length);

const markAllRead = () => {
  notifications.value = notifications.value.map((n) => ({ ...n, read: true }));
};

const markRead = (id: number) => {
  const notif = notifications.value.find((n) => n.id === id);

  if (notif) {
 notif.read = true; 
}
};

const iconFor = (type: Notification['type']) => {
  if (type === 'approval') {
 return ClipboardCheck; 
}

  if (type === 'submission') {
 return FlaskConical; 
}

  return Info;
};

const colorFor = (type: Notification['type']) => {
  if (type === 'approval') {
 return 'text-emerald-500 bg-emerald-500/10'; 
}

  if (type === 'submission') {
 return 'text-blue-500 bg-blue-500/10'; 
}

  return 'text-amber-500 bg-amber-500/10';
};

// Close when clicking outside
const onClickOutside = (e: MouseEvent) => {
  const target = e.target as Node;

  if (
    panelRef.value && !panelRef.value.contains(target) &&
    triggerRef.value && !triggerRef.value.contains(target)
  ) {
    open.value = false;
  }
};

onMounted(() => document.addEventListener('mousedown', onClickOutside));
onBeforeUnmount(() => document.removeEventListener('mousedown', onClickOutside));
</script>

<template>
  <div class="relative">
    <!-- Bell trigger -->
    <button
      ref="triggerRef"
      type="button"
      class="relative inline-flex items-center justify-center w-9 h-9 rounded-lg hover:bg-muted transition-colors"
      :aria-label="$t('Notifications')"
      @click="open = !open"
    >
      <Bell class="w-5 h-5 text-muted-foreground" />
      <!-- Unread badge -->
      <span
        v-if="unreadCount > 0"
        class="absolute top-1 right-1 inline-flex items-center justify-center w-4 h-4 text-[10px] font-bold rounded-full bg-destructive text-destructive-foreground leading-none"
      >
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <!-- Dropdown panel -->
    <Transition
      enter-active-class="transition-all duration-200 ease-out"
      enter-from-class="opacity-0 scale-95 translate-y-1"
      enter-to-class="opacity-100 scale-100 translate-y-0"
      leave-active-class="transition-all duration-150 ease-in"
      leave-from-class="opacity-100 scale-100 translate-y-0"
      leave-to-class="opacity-0 scale-95 translate-y-1"
    >
      <div
        v-if="open"
        ref="panelRef"
        class="absolute right-0 top-full mt-2 w-80 z-50 rounded-xl border bg-popover shadow-xl shadow-black/10 overflow-hidden"
      >
        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 border-b bg-muted/30">
          <div>
            <h3 class="text-sm font-semibold">{{ $t('Notifications') }}</h3>
            <p class="text-xs text-muted-foreground">
              {{ unreadCount > 0 ? `${unreadCount} belum dibaca` : 'Semua sudah dibaca' }}
            </p>
          </div>
          <Button
            v-if="unreadCount > 0"
            variant="ghost"
            size="sm"
            class="h-7 text-xs gap-1 text-primary"
            @click="markAllRead"
          >
            <CheckCheck class="w-3.5 h-3.5" />
            Tandai semua
          </Button>
        </div>

        <!-- Notification list -->
        <ul class="max-h-72 overflow-y-auto divide-y">
          <li
            v-for="notif in notifications"
            :key="notif.id"
            class="flex gap-3 px-4 py-3 cursor-pointer transition-colors"
            :class="notif.read ? 'bg-background hover:bg-muted/40' : 'bg-primary/5 hover:bg-primary/10'"
            @click="markRead(notif.id)"
          >
            <!-- Icon -->
            <div
              class="flex items-center justify-center w-8 h-8 rounded-full shrink-0 mt-0.5"
              :class="colorFor(notif.type)"
            >
              <component :is="iconFor(notif.type)" class="w-4 h-4" />
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between gap-2">
                <p class="text-xs font-semibold truncate">{{ notif.title }}</p>
                <span
                  v-if="!notif.read"
                  class="w-2 h-2 rounded-full bg-primary shrink-0"
                />
              </div>
              <p class="text-xs text-muted-foreground mt-0.5 leading-snug line-clamp-2">{{ notif.message }}</p>
              <p class="text-[10px] text-muted-foreground/70 mt-1">{{ notif.time }}</p>
            </div>
          </li>

          <li v-if="notifications.length === 0" class="px-4 py-8 text-center">
            <Bell class="w-6 h-6 text-muted-foreground/30 mx-auto mb-2" />
            <p class="text-xs text-muted-foreground">Tidak ada notifikasi</p>
          </li>
        </ul>

        <!-- Footer -->
        <div class="border-t px-4 py-2 bg-muted/20">
          <button
            type="button"
            class="w-full text-xs text-center text-primary hover:underline py-1 transition-colors"
          >
            Lihat semua notifikasi
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

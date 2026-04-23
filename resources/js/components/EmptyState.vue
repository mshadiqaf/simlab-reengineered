<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
  title: string;
  description?: string;
  icon?: any; // Component type
  imageSrc?: string;
}>();

const hasImage = computed(() => !!props.imageSrc);
const hasIcon = computed(() => !!props.icon && !hasImage.value);
</script>

<template>
  <div class="flex flex-col items-center justify-center p-8 text-center min-h-72 border border-dashed rounded-xl bg-card">
    <div v-if="hasImage" class="mb-4">
      <img :src="imageSrc" alt="" class="w-48 h-auto object-contain opacity-80" />
    </div>
    <div v-else-if="hasIcon" class="mb-4 p-4 rounded-full bg-muted">
      <component :is="icon" class="w-10 h-10 text-muted-foreground" />
    </div>
    
    <h3 class="text-lg font-semibold tracking-tight">{{ $t(title) }}</h3>
    
    <p v-if="description" class="text-sm text-muted-foreground mt-1 max-w-sm">
      {{ $t(description) }}
    </p>

    <div class="mt-6" v-if="$slots.action">
      <slot name="action" />
    </div>
  </div>
</template>

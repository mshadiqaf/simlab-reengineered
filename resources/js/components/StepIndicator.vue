<script setup lang="ts">
import { Check } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
  steps: string[];
  currentStep: number;
}>();
</script>

<template>
  <div class="w-full py-4">
    <div class="flex items-center justify-between relative">
      <!-- Background line -->
      <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-muted rounded-full"></div>
      
      <!-- Progress line -->
      <div 
        class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-primary rounded-full transition-all duration-300"
        :style="{ width: `${(currentStep / (steps.length - 1)) * 100}%` }"
      ></div>

      <div 
        v-for="(step, index) in steps" 
        :key="index"
        class="relative flex flex-col items-center group z-10"
      >
        <div 
          class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300"
          :class="[
            index < currentStep 
              ? 'bg-primary border-primary text-primary-foreground' 
              : index === currentStep 
                ? 'bg-background border-primary text-primary shadow-sm shadow-primary/20' 
                : 'bg-background border-muted text-muted-foreground'
          ]"
        >
          <Check v-if="index < currentStep" class="w-5 h-5" />
          <span v-else class="text-sm font-semibold">{{ index + 1 }}</span>
        </div>
        <span 
          class="absolute top-12 text-xs font-medium whitespace-nowrap transition-colors duration-300"
          :class="[
            index <= currentStep ? 'text-foreground' : 'text-muted-foreground'
          ]"
        >
          {{ $t(step) }}
        </span>
      </div>
    </div>
    <div class="h-8"></div> <!-- Spacer for absolute text -->
  </div>
</template>

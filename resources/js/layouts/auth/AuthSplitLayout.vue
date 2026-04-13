<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import Autoplay from 'embla-carousel-autoplay';
import { computed, ref, onMounted } from 'vue';
import { Carousel, CarouselContent, CarouselItem } from '@/components/ui/carousel';
import type { UnwrapRefCarouselApi } from '@/components/ui/carousel/interface';
import { carouselImages, carouselQuotes as quotes } from '@/constants/auth';

const plugin = Autoplay({
  delay: 4000,
  stopOnInteraction: false,
});

const page = usePage();
const isRegister = computed(() => page.component === 'auth/Register');

const randomQuote = ref(quotes[0]);

onMounted(() => {
  randomQuote.value = quotes[Math.floor(Math.random() * quotes.length)];
});

const selectedIndex = ref(0);
let carouselApi: UnwrapRefCarouselApi | null = null;

function setApi(api: UnwrapRefCarouselApi) {
  carouselApi = api;

  if (!api) {
    return;
  }

  api.on('select', () => {
    selectedIndex.value = api.selectedScrollSnap();
  });
}

function scrollTo(index: number) {
  carouselApi?.scrollTo(index);
}

defineProps<{
  title?: string;
  description?: string;
}>();
</script>

<template>
  <div
    class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0"
  >
    <div :class="['lg:p-8', isRegister ? 'order-last' : '']">
      <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
        <div class="flex flex-col space-y-2 text-center">
          <h1 class="text-xl font-medium tracking-tight" v-if="title">
            {{ title }}
          </h1>
          <p class="text-sm text-muted-foreground" v-if="description">
            {{ description }}
          </p>
        </div>
        <slot />
      </div>
    </div>
    <div
      :class="[
        'relative hidden h-full flex-col p-10 text-white lg:flex',
        isRegister ? 'order-first dark:border-r' : 'dark:border-l',
      ]"
    >
      <div class="absolute inset-3 overflow-clip rounded-3xl bg-zinc-900 select-none">
        <Carousel
          class="h-full w-full **:data-[slot=carousel-content]:h-full"
          :opts="{ loop: true }"
          :plugins="[plugin]"
          @init-api="setApi"
        >
          <CarouselContent class="ml-0 h-full">
            <CarouselItem v-for="image in carouselImages" :key="image.id" class="pl-0 h-full">
              <div class="relative h-full w-full">
                <img
                  :src="image.src"
                  :alt="image.alt"
                  class="h-full w-full object-cover object-center pointer-events-none"
                  draggable="false"
                />
                <!-- Subtle overlay for text readability -->
                <div class="absolute inset-0 bg-zinc-900/30"></div>
              </div>
            </CarouselItem>
          </CarouselContent>

          <div class="pointer-events-none absolute bottom-20 left-0 right-0 z-10 px-10 text-center drop-shadow-md">
            <blockquote class="space-y-4">
              <p class="text-xl font-medium tracking-tight italic text-zinc-100">
                &ldquo;{{ randomQuote.text }}&rdquo;
              </p>
              <footer class="text-sm font-medium text-zinc-300">
                &mdash; {{ randomQuote.author }}
              </footer>
            </blockquote>
          </div>

          <div class="absolute bottom-8 left-1/2 flex -translate-x-1/2 gap-2 z-10">
            <button
              v-for="(_, index) in carouselImages"
              :key="'dot-' + index"
              @click="scrollTo(index)"
              :class="[
                'h-2 rounded-full transition-all duration-300',
                selectedIndex === index ? 'w-6 bg-primary shadow-[0_0_8px_#3b82f6]' : 'w-2 bg-white/50 hover:bg-white/75'
              ]"
              :aria-label="`Go to slide ${index + 1}`"
            />
          </div>
        </Carousel>
      </div>
    </div>
  </div>
</template>

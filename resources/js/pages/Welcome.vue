<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';

withDefaults(
  defineProps<{
    canRegister: boolean;
  }>(),
  {
    canRegister: true,
  },
);
</script>

<template>
  <Head title="Welcome" />
  <div class="flex min-h-screen flex-col items-center bg-background p-6 text-foreground lg:justify-center lg:p-8">
    <header class="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl">
      <nav class="flex items-center justify-end gap-4">
        <Link
          v-if="$page.props.auth.user"
          :href="dashboard()"
          class="inline-block rounded-sm border px-5 py-1.5 text-sm leading-normal hover:bg-muted"
        >
          {{ $t('Dashboard') }}
        </Link>
        <template v-else>
          <Link
            :href="login()"
            class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal hover:bg-muted"
          >
            {{ $t('Log in') }}
          </Link>
          <Link
            v-if="canRegister"
            :href="register()"
            class="inline-block rounded-sm border px-5 py-1.5 text-sm leading-normal hover:bg-muted"
          >
            {{ $t('Register') }}
          </Link>
        </template>
      </nav>
    </header>

    <div class="flex w-full flex-col items-center justify-center opacity-100 transition-opacity duration-750 lg:grow">
      <main class="flex w-full max-w-[335px] flex-col items-center justify-center overflow-hidden rounded-lg lg:max-w-4xl">
        <div class="flex flex-col items-center justify-center p-12 text-center lg:p-20">
          <h1 class="mb-4 text-4xl font-bold tracking-tight">Simlab Reengineered</h1>
          <p class="text-lg text-muted-foreground">{{ $t('Landing page is currently under construction.') }}</p>
        </div>
      </main>
    </div>
  </div>
</template>

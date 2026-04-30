<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineOptions({
  layout: {
    title: 'Log in to your account',
    description: 'Enter your email and password below to log in',
  },
});

defineProps<{
  status?: string;
  canResetPassword: boolean;
  canRegister: boolean;
}>();
</script>

<template>
  <Head title="Log in" />

  <div class="md:contents">

    <!-- ================= MOBILE ================= -->
    <div class="md:hidden fixed inset-0 w-full h-full flex items-center justify-center overflow-hidden">

      <!-- Background Image -->
      <div
        class="absolute inset-0 bg-cover bg-center bg-no-repeat"
        style="background-image: url('/images/photos/lab-terpadu-1.webp');"
      />

      <!-- Gradient Putih -->
      <div class="absolute inset-0 bg-gradient-to-b from-white/0 via-white/30 to-white/90" />

      <!-- FORM -->
      <div class="relative z-10 w-full max-w-sm mx-auto px-5 py-8
                  bg-white/70 backdrop-blur-md rounded-2xl shadow-xl border border-white/40">

        <!-- Status -->
        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
          {{ status }}
        </div>

        <!-- Logo -->
        <div class="mb-6 flex justify-center">
          <img
            src="/images/logos/logovertikal.png"
            alt="Logo"
            class="h-20 w-auto object-contain"
          />
        </div>

        <!-- Title -->
        <div class="mb-6 text-center">
          <h1 class="text-xl font-semibold text-gray-900">
            {{ $t('Log in to your account') }}
          </h1>
          <p class="mt-1 text-sm text-gray-600">
            {{ $t('Enter your email and password below to log in') }}
          </p>
        </div>

        <!-- Form -->
        <Form
          v-bind="store.form()"
          :reset-on-success="['password']"
          v-slot="{ errors, processing }"
          class="flex flex-col gap-5"
        >

          <!-- Email -->
          <div class="grid gap-2">
            <Label class="text-gray-800">Email address</Label>
            <Input
              type="email"
              name="email"
              required
              autofocus
              autocomplete="email"
              placeholder="email@example.com"
              class="bg-white text-gray-900 placeholder:text-gray-900 border-gray-300 focus:border-primary"
            />
            <InputError :message="errors.email" />
          </div>

          <!-- Password -->
          <div class="grid gap-2">
            <div class="flex justify-between">
              <Label class="text-gray-800">Password</Label>
              <TextLink
                v-if="canResetPassword"
                :href="request()"
                class="text-sm text-gray-600 hover:text-gray-900"
              >
                Forgot password?
              </TextLink>
            </div>

            <PasswordInput
              name="password"
              required
              autocomplete="current-password"
              placeholder="Enter your password"
              class="bg-white text-gray-900 placeholder:text-gray-900 border-gray-300 focus:border-primary"
            />

            <InputError :message="errors.password" />
          </div>

          <!-- Remember -->
          <div class="flex items-center">
            <Label class="flex items-center gap-2 text-gray-700 cursor-pointer">
              <Checkbox name="remember" />
              Remember me
            </Label>
          </div>

          <!-- Button -->
          <Button
            type="submit"
            class="w-full mt-2"
            :disabled="processing"
          >
            <Spinner v-if="processing" />
            Log in
          </Button>

          <!-- Register -->
          <div v-if="canRegister" class="text-center text-sm text-gray-600">
            Don't have an account?
            <TextLink
              :href="register()"
              class="text-primary underline hover:opacity-80"
            >
              Sign up
            </TextLink>
          </div>

        </Form>
      </div>
    </div>

    <!-- ================= DESKTOP (UNCHANGED) ================= -->
    <div class="hidden md:contents">
      <div v-if="status" class="mb-4 text-center text-sm text-green-600">
        {{ status }}
      </div>

      <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
      >
        <div class="grid gap-6">
          <div class="grid gap-2">
            <Label>Email address</Label>
            <Input type="email" name="email" required />
            <InputError :message="errors.email" />
          </div>

          <div class="grid gap-2">
            <div class="flex justify-between">
              <Label>Password</Label>
              <TextLink v-if="canResetPassword" :href="request()">
                Forgot password?
              </TextLink>
            </div>

            <PasswordInput name="password" required />
            <InputError :message="errors.password" />
          </div>

          <Label class="flex items-center gap-2">
            <Checkbox name="remember" />
            Remember me
          </Label>

          <Button type="submit" class="w-full" :disabled="processing">
            <Spinner v-if="processing" />
            Log in
          </Button>
        </div>

        <div v-if="canRegister" class="text-center text-sm">
          Don't have an account?
          <TextLink :href="register()">Sign up</TextLink>
        </div>
      </Form>
    </div>

  </div>
</template>
<script setup lang="ts">
import AuthLayout from '@/layouts/auth/AuthSplitLayout.vue';

const { title = '', description = '' } = defineProps<{
  title?: string;
  description?: string;
}>();
</script>

<template>
  <AuthLayout :title="title ? $t(title) : ''" :description="description ? $t(description) : ''">
    <slot />
  </AuthLayout>
</template>

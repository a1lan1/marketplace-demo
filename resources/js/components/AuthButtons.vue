<script setup lang="ts">
import { login, register } from '@/routes'
import { Link } from '@inertiajs/vue3'

withDefaults(
  defineProps<{
    canRegister?: boolean;
  }>(),
  {
    canRegister: true
  }
)
</script>

<template>
  <Link
    :href="login()"
    class="rounded-md px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700"
  >
    Log in
  </Link>
  <Link
    v-if="canRegister"
    :href="register()"
    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700"
  >
    Register
  </Link>
</template>

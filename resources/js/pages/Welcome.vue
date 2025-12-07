<script setup lang="ts">
import { catalog } from '@/routes/products'
import { login, register } from '@/routes'
import { Head, Link } from '@inertiajs/vue3'

withDefaults(
  defineProps<{
    canRegister: boolean;
  }>(),
  {
    canRegister: true
  }
)
</script>

<template>
  <Head title="Welcome">
    <link
      rel="preconnect"
      href="https://rsms.me/"
    >
    <link
      rel="stylesheet"
      href="https://rsms.me/inter/inter.css"
    >
  </Head>
  <div class="flex min-h-screen flex-col items-center bg-gray-100 p-6 text-gray-800 lg:justify-center lg:p-8 dark:bg-gray-900 dark:text-gray-200">
    <header class="mb-8 w-full max-w-6xl text-sm">
      <nav class="flex items-center justify-end gap-4">
        <Link
          :href="catalog()"
          class="rounded-md px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700"
        >
          Catalog
        </Link>
        <template v-if="!$page.props.auth.user">
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
      </nav>
    </header>
    <div
      class="flex w-full flex-grow items-center justify-center"
    >
      <main
        class="w-full max-w-6xl overflow-hidden rounded-xl bg-white shadow-2xl dark:bg-gray-800"
      >
        <div class="grid grid-cols-1 items-center md:grid-cols-2">
          <div class="md:order-last">
            <img
              src="/img/marketplace.png"
              alt="Marketplace"
              class="h-full w-full object-cover"
            >
          </div>
          <div class="p-8 text-center md:p-12 md:text-left">
            <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-5xl md:text-6xl">
              <span class="block">Welcome to our</span>
              <span class="block text-indigo-600">Marketplace!</span>
            </h1>
            <p class="mx-auto mt-6 max-w-md text-lg text-gray-500 dark:text-gray-400 md:mx-0">
              The best place to buy and sell amazing products. Explore our catalog and find what you need.
            </p>
            <div class="mt-8">
              <Link
                :href="catalog()"
                class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-6 py-3 text-base font-medium text-white shadow-sm hover:bg-indigo-700"
              >
                Explore Catalog
              </Link>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

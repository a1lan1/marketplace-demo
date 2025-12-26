<script setup lang="ts">
import { show as sellerShow } from '@/routes/sellers'
import type { User } from '@/types'
import { Link } from '@inertiajs/vue3'

withDefaults(
  defineProps<{
    seller: User;
    withAvatar?: boolean;
    chip?: boolean;
    variant?: 'flat' | 'text' | 'elevated' | 'tonal' | 'outlined' | 'plain';
  }>(),
  {
    chip: false,
    withAvatar: false,
    variant: undefined
  }
)
</script>

<template>
  <v-chip
    v-if="chip"
    :href="sellerShow(seller.id).url"
    link
    pill
    :variant
    color="primary"
    title="Seller"
  >
    <v-avatar
      v-if="withAvatar"
      start
    >
      <v-img
        :src="seller.avatar"
        :alt="seller.name"
      />
    </v-avatar>
    {{ seller.name }}
  </v-chip>
  <div
    v-else
    class="flex align-middle"
  >
    <span class="text-medium-emphasis mr-1">Seller:</span>
    <v-avatar
      v-if="withAvatar"
      size="24"
      :href="sellerShow(seller.id).url"
      class="mr-2"
    >
      <v-img
        v-if="seller.avatar"
        :src="seller.avatar"
        :alt="seller.name"
      />
      <span v-else>
        {{ seller.name[0] }}
      </span>
    </v-avatar>
    <Link
      :href="sellerShow(seller.id).url"
      class="text-decoration-none font-weight-medium text-primary"
      style="transition: opacity 0.2s"
      @mouseenter="$event.target.style.opacity = '0.8'"
      @mouseleave="$event.target.style.opacity = '1'"
    >
      {{ seller.name }}
    </Link>
  </div>
</template>

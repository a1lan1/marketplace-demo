<script setup lang="ts">
import { useMoney } from '@/composables/useMoney'
import { MoneyData } from '@/types'

interface Props {
  value: number | MoneyData | null | undefined;
  chip?: boolean;
  size?: 'small' | 'medium' | 'large' | 'x-large';
  variant?: 'flat' | 'text' | 'elevated' | 'tonal' | 'outlined' | 'plain';
  color?: string;
}

const props = withDefaults(defineProps<Props>(), {
  color: 'success',
  size: undefined,
  variant: undefined
})

const { formatted } = useMoney(() => props.value)
</script>

<template>
  <v-chip
    v-if="chip"
    :size="size"
    :variant="variant"
    label
    :color="color"
  >
    {{ formatted }}
  </v-chip>
  <span
    v-else
    :class="[
      'font-weight-bold',
      {
        'text-primary': !color,
        'text-body-2': size === 'small',
        'text-h6': size === 'medium' || !size,
        'text-h5': size === 'large',
        'text-h4': size === 'x-large',
      },
    ]"
    :style="{ color: color ? undefined : 'rgb(var(--v-theme-primary))' }"
  >
    {{ formatted }}
  </span>
</template>

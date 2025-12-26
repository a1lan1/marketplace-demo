<script setup lang="ts">
import type { Sentiment } from '@/types'
import { getSentimentColor, getSentimentIcon } from '@/utils/feedback'

defineProps<{
  sentiment: Sentiment;
}>()
</script>

<template>
  <VChip
    pill
    size="small"
    variant="flat"
    :color="getSentimentColor(sentiment)"
    :prepend-icon="getSentimentIcon(sentiment)"
  >
    {{ sentiment || 'pending' }}
  </VChip>
</template>

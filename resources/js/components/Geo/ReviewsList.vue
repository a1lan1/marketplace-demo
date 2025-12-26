<script setup lang="ts">
import SentimentChip from '@/components/shared/SentimentChip.vue'
import type { Review } from '@/types/geo'

defineProps<{
  reviews: Review[];
  loading: boolean;
}>()

defineEmits<{
  (e: 'reply', review: Review): void;
}>()
</script>

<template>
  <VCard>
    <VCardTitle>Recent Reviews</VCardTitle>
    <VCardText>
      <div
        v-if="loading"
        class="flex justify-center py-8"
      >
        <VProgressCircular
          indeterminate
          color="primary"
        />
      </div>
      <VEmptyState
        v-else-if="!reviews.length"
        headline="No reviews found"
        title="No reviews match the current filters."
        icon="mdi-comment-question-outline"
      />
      <div
        v-else
        class="space-y-4"
      >
        <div
          v-for="review in reviews"
          :key="review.id"
          class="border-b pb-4 last:border-0"
        >
          <div class="mb-2 flex items-start justify-between">
            <div>
              <div class="font-bold">
                {{ review.author_name }}
              </div>
              <div class="text-xs text-gray-500">
                <v-chip
                  size="small"
                  color="primary"
                  density="compact"
                >
                  {{ review.source }}
                </v-chip> •
                {{ new Date(review.published_at).toLocaleDateString() }}
              </div>
            </div>
            <div class="flex items-center gap-2">
              <VRating
                :model-value="review.rating"
                readonly
                density="compact"
                color="warning"
              />
              <SentimentChip :sentiment="review.sentiment" />
              <VBtn
                size="small"
                variant="tonal"
                @click="$emit('reply', review)"
              >
                Reply
              </VBtn>
            </div>
          </div>
          <p class="text-gray-300">
            {{ review.text }}
          </p>
        </div>
      </div>
    </VCardText>
  </VCard>
</template>

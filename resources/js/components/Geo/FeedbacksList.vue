<script setup lang="ts">
import SentimentChip from '@/components/shared/SentimentChip.vue'
import type { Feedback } from '@/types'
import { getFeedbackTypeLabel } from '@/utils/feedback'

defineProps<{
  feedbacks: Feedback[];
  loading: boolean;
}>()
</script>

<template>
  <VCard>
    <VCardTitle>Internal Feedbacks</VCardTitle>
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
        v-else-if="!feedbacks.length"
        headline="No feedbacks found"
        title="No internal feedbacks match the current filters."
        icon="mdi-message-draw"
      />
      <div
        v-else
        class="space-y-4"
      >
        <div
          v-for="feedback in feedbacks"
          :key="feedback.id"
          class="border-b pb-4 last:border-0"
        >
          <div class="mb-2 flex items-start justify-between">
            <div>
              <div class="flex items-center gap-2 font-bold">
                {{ feedback.author?.name || 'Anonymous' }}
                <VTooltip
                  v-if="feedback.is_verified_purchase"
                  text="Verified Purchase"
                  location="top"
                >
                  <template #activator="{ props }">
                    <VIcon
                      v-bind="props"
                      color="success"
                      size="small"
                    >
                      mdi-check-decagram
                    </VIcon>
                  </template>
                </VTooltip>
              </div>
              <div class="text-xs text-gray-500">
                <v-chip
                  density="compact"
                  color="primary"
                  size="small"
                >
                  {{ getFeedbackTypeLabel(feedback.feedbackable_type) }}
                  #{{ feedback.feedbackable_id }}
                </v-chip>
                {{ feedback.created_at }}
              </div>
            </div>
            <div class="flex items-center gap-2">
              <SentimentChip :sentiment="feedback.sentiment" />
              <VRating
                :model-value="feedback.rating"
                readonly
                density="compact"
                color="warning"
              />
            </div>
          </div>
          <p class="text-gray-300">
            {{ feedback.comment }}
          </p>
        </div>
      </div>
    </VCardText>
  </VCard>
</template>

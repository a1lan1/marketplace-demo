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
            <div class="flex items-start gap-3">
              <VAvatar
                size="40"
                color="grey-darken-3"
              >
                <VImg
                  v-if="feedback.author?.avatar"
                  :src="feedback.author.avatar"
                  :alt="feedback.author?.name"
                />
                <span
                  v-else
                  class="text-h6 font-weight-bold"
                >{{ feedback.author?.name?.charAt(0).toUpperCase() || 'A' }}</span>
              </VAvatar>

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
                    class="mr-2"
                  >
                    {{ getFeedbackTypeLabel(feedback.feedbackable_type) }}
                    #{{ feedback.feedbackable_id }}
                  </v-chip>
                  {{ feedback.created_at }}
                </div>
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
          <p class="ml-[52px] text-gray-300">
            {{ feedback.comment }}
          </p>
        </div>
      </div>
    </VCardText>
  </VCard>
</template>

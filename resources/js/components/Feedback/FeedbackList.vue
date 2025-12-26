<script setup lang="ts">
import SentimentChip from '@/components/shared/SentimentChip.vue'
import { useFeedbackStore } from '@/stores/feedback'
import { echo } from '@laravel/echo-vue'
import { storeToRefs } from 'pinia'
import { onMounted, onUnmounted } from 'vue'

const props = defineProps<{
  feedbackableType: 'product' | 'seller';
  feedbackableId: number;
}>()

const feedbackStore = useFeedbackStore()
const { feedbacks, loading } = storeToRefs(feedbackStore)
const { fetchFeedbacks, addOrUpdateFeedback } = feedbackStore

const channelName = `feedbacks.${props.feedbackableType}.${props.feedbackableId}`

onMounted(() => {
  fetchFeedbacks(props.feedbackableType, props.feedbackableId)

  echo()
    .channel(channelName)
    .listen('FeedbackSaved', (e: any) => {
      addOrUpdateFeedback(e.feedback)
    })
})

onUnmounted(() => {
  echo().leave(channelName)
})
</script>

<template>
  <div>
    <h3 class="mb-4 text-xl font-semibold">
      Feedbacks
    </h3>

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
      v-else-if="feedbacks.length === 0"
      icon="mdi-comment-outline"
      title="No feedbacks yet"
      text="Be the first to write a feedback!"
    />
    <div
      v-else
      class="relative"
    >
      <TransitionGroup
        name="list"
        tag="div"
        class="space-y-4"
      >
        <VCard
          v-for="feedback in feedbacks"
          :key="feedback.id"
          variant="flat"
          class="border"
        >
          <VCardText>
            <div class="mb-2 flex items-start justify-between">
              <div class="flex items-center gap-3">
                <VAvatar
                  v-if="feedback.author?.avatar"
                  size="40"
                >
                  <v-img
                    :src="feedback.author.avatar"
                    :alt="feedback.author?.name"
                  />
                </VAvatar>
                <div>
                  <div class="font-semibold">
                    {{ feedback.author?.name }}
                  </div>
                  <div class="text-xs text-gray-500">
                    {{ feedback.created_at }}
                  </div>
                </div>
              </div>

              <div class="flex flex-col align-middle">
                <VRating
                  :model-value="feedback.rating"
                  readonly
                  size="small"
                  density="compact"
                  color="warning"
                />
              </div>
            </div>

            <div class="mb-2 flex gap-2">
              <VChip
                v-if="feedback.is_verified_purchase"
                size="x-small"
                color="primary"
                variant="flat"
                class="font-weight-bold"
                prepend-icon="mdi-check"
              >
                Verified Purchase
              </VChip>
              <SentimentChip
                v-if="feedback.sentiment"
                :sentiment="feedback.sentiment"
                size="x-small"
              />
            </div>

            <p class="whitespace-pre-line text-gray-300">
              {{ feedback.comment }}
            </p>
          </VCardText>
        </VCard>
      </TransitionGroup>
    </div>
  </div>
</template>

<style scoped>
.list-move,
.list-enter-active,
.list-leave-active {
  transition: all 0.5s ease;
}

.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

.list-leave-active {
  position: absolute;
  width: 100%;
}
</style>

<script setup lang="ts">
import { trackError } from '@/composables/useActivity'
import { useFeedbackStore } from '@/stores/feedback'
import { storeToRefs } from 'pinia'
import { ref } from 'vue'

const props = defineProps<{
  feedbackableType: 'product' | 'seller';
  feedbackableId: number;
}>()

const feedbackStore = useFeedbackStore()
const { storing } = storeToRefs(feedbackStore)
const { storeFeedback } = feedbackStore

const rating = ref(0)
const comment = ref('')

const submit = async() => {
  if (rating.value === 0) return

  try {
    await storeFeedback({
      feedbackable_type: props.feedbackableType,
      feedbackable_id: props.feedbackableId,
      rating: rating.value,
      comment: comment.value
    })
    // Reset form
    rating.value = 0
    comment.value = ''
  } catch (e: any) {
    console.error(e)
    trackError('Error submitting feedback')
  }
}
</script>

<template>
  <VCard class="mb-6">
    <VCardText>
      <h3 class="mb-1 text-lg font-semibold">
        Write a Feedback
      </h3>
      <VForm @submit.prevent="submit">
        <div class="mb-1">
          <div class="mb-1 text-sm text-gray-600">
            Your Rating
          </div>
          <VRating
            v-model="rating"
            hover
            color="warning"
            density="compact"
          />
        </div>

        <VTextarea
          v-model="comment"
          label="Your Feedback"
          variant="outlined"
          rows="3"
          auto-grow
        />

        <div class="flex justify-end">
          <VBtn
            type="submit"
            color="primary"
            :loading="storing"
            :disabled="rating === 0"
            prepend-icon="mdi-send"
          >
            Submit Feedback
          </VBtn>
        </div>
      </VForm>
    </VCardText>
  </VCard>
</template>

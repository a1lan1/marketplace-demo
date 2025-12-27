import { PaginationBasic } from '@/types'
import { Feedback, FeedbackForm } from '@/types'
import { defineStore } from 'pinia'

interface State {
  feedbacks: Feedback[];
  pagination: PaginationBasic<Feedback>['meta'] | null;
  loading: boolean;
  storing: boolean;
}

export const useFeedbackStore = defineStore('feedback', {
  state: (): State => ({
    feedbacks: [],
    pagination: null,
    loading: false,
    storing: false
  }),

  actions: {
    async fetchFeedbacks(type: 'product' | 'seller', id: number, page = 1) {
      this.loading = true
      try {
        const { data } = await this.$axios.get<PaginationBasic<Feedback>>(`/${type}/${id}/feedbacks`, {
          params: { page }
        })
        this.feedbacks = data.data
        this.pagination = data.meta
      } catch (e: any) {
        this.$snackbar.error({
          text: e.response?.data?.message || 'Failed to fetch feedbacks'
        })
      } finally {
        this.loading = false
      }
    },
    async storeFeedback(form: FeedbackForm) {
      this.storing = true
      try {
        const { data } = await this.$axios.post<Feedback>('/feedbacks', form)
        this.addOrUpdateFeedback(data)
        this.$snackbar.success({ text: 'Feedback submitted successfully' })
      } catch (e: any) {
        this.$snackbar.error({
          text: e.response?.data?.message || 'Failed to submit feedback'
        })
        throw e
      } finally {
        this.storing = false
      }
    },
    addOrUpdateFeedback(feedback: Feedback) {
      const index = this.feedbacks.findIndex(f => f.id === feedback.id)
      if (index !== -1) {
        this.feedbacks[index] = feedback
      } else {
        this.feedbacks.unshift(feedback)
      }
    }
  }
})

import { Feedback, Pagination } from '@/types'
import type {
  Location,
  LocationForm,
  ResponseTemplate,
  Review,
  ReviewFilters,
  ReviewMetrics
} from '@/types/geo'
import { defineStore } from 'pinia'

const defaultForm: LocationForm = {
  name: '',
  type: 'store',
  address: {
    country: '',
    city: '',
    street: '',
    house_number: '',
    postal_code: '',
    full_address: ''
  },
  latitude: null,
  longitude: null
}

interface State {
  locations: Location[];
  reviews: Review[];
  feedbacks: Feedback[];
  metrics: ReviewMetrics | null;
  templates: ResponseTemplate[];
  form: LocationForm;
  loading: boolean;
  storing: boolean;
  reviewsLoading: boolean;
  feedbacksLoading: boolean;
  templatesLoading: boolean;
  pagination: Pagination<Review>['meta'] | null;
}

export const useGeoStore = defineStore('geo', {
  state: (): State => ({
    locations: [],
    reviews: [],
    feedbacks: [],
    metrics: null,
    templates: [],
    form: { ...defaultForm },
    loading: false,
    storing: false,
    reviewsLoading: false,
    feedbacksLoading: false,
    templatesLoading: false,
    pagination: null
  }),

  actions: {
    async fetchLocations() {
      this.loading = true
      try {
        const { data } = await this.$axios.get<Location[]>('/geo/locations')
        this.locations = data
      } catch (e: any) {
        this.$snackbar.error({
          text: e.response?.data?.message || 'Failed to fetch locations'
        })
      } finally {
        this.loading = false
      }
    },
    async createLocation(form: LocationForm) {
      this.storing = true
      try {
        const { data } = await this.$axios.post<Location>('/geo/locations', form)
        this.locations.unshift(data)
        this.$snackbar.success({ text: 'Location created successfully' })

        return data
      } catch (e: any) {
        this.$snackbar.error({
          text: e.response?.data?.message || 'Failed to create location'
        })
        throw e
      } finally {
        this.storing = false
      }
    },
    async updateLocation(id: number, form: Partial<LocationForm>) {
      this.storing = true
      try {
        const { data } = await this.$axios.put<Location>(`/geo/locations/${id}`, form)
        const index = this.locations.findIndex((l) => l.id === id)
        if (index !== -1) {
          this.locations[index] = data
        }
        this.$snackbar.success({ text: 'Location updated successfully' })

        return data
      } catch (e: any) {
        this.$snackbar.error({
          text: e.response?.data?.message || 'Failed to update location'
        })
        throw e
      } finally {
        this.storing = false
      }
    },
    async deleteLocation(id: number) {
      this.storing = true
      try {
        await this.$axios.delete(`/geo/locations/${id}`)
        this.locations = this.locations.filter((l) => l.id !== id)
        this.$snackbar.success({ text: 'Location deleted successfully' })
      } catch (e: any) {
        this.$snackbar.error({
          text: e.response?.data?.message || 'Failed to delete location'
        })
        throw e
      } finally {
        this.storing = false
      }
    },
    async fetchReviews(filters: ReviewFilters = {}) {
      this.reviewsLoading = true
      try {
        const { data } = await this.$axios.get<Pagination<Review>>('/geo/reviews', {
          params: filters
        })
        this.reviews = data.data
        this.pagination = data.meta
      } catch (e: any) {
        this.$snackbar.error({
          text: e.response?.data?.message || 'Failed to fetch reviews'
        })
      } finally {
        this.reviewsLoading = false
      }
    },
    async fetchFeedbacks(filters: any = {}) {
      this.feedbacksLoading = true
      try {
        const { data } = await this.$axios.get<Pagination<Feedback>>('/feedbacks', {
          params: filters
        })
        this.feedbacks = data.data
      } catch (e: any) {
        this.$snackbar.error({
          text: e.response?.data?.message || 'Failed to fetch feedbacks'
        })
      } finally {
        this.feedbacksLoading = false
      }
    },
    async fetchMetrics(filters: Omit<ReviewFilters, 'page'> = {}) {
      try {
        const { data } = await this.$axios.get<ReviewMetrics>('/geo/metrics', {
          params: filters
        })
        this.metrics = data
      } catch (e: any) {
        this.$snackbar.error({
          text: e.response?.data?.message || 'Failed to fetch metrics'
        })
      }
    },
    async fetchTemplates() {
      this.templatesLoading = true
      try {
        const { data } = await this.$axios.get<ResponseTemplate[]>('/geo/response-templates')
        this.templates = data
      } catch (e: any) {
        this.$snackbar.error({
          text: e.response?.data?.message || 'Failed to fetch templates'
        })
      } finally {
        this.templatesLoading = false
      }
    },
    resetForm() {
      this.form = { ...defaultForm }
    },
    fillFormForEdit(location: Location) {
      this.form = {
        id: location.id,
        name: location.name,
        type: location.type,
        address: { ...location.address },
        latitude: location.latitude,
        longitude: location.longitude
      }
    }
  }
})

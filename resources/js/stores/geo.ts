import type { Feedback } from '@/types'
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
  pagination: {
    current_page: number;
    last_page: number;
    total: number;
  };
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
    pagination: {
      current_page: 1,
      last_page: 1,
      total: 0
    }
  }),

  actions: {
    async fetchLocations() {
      this.loading = true
      try {
        const { data } = await this.$axios.get('/geo/locations')
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
        const { data } = await this.$axios.post('/geo/locations', form)
        this.locations.unshift(data.data)
        this.$snackbar.success({ text: 'Location created successfully' })

        return data.data
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
        const { data } = await this.$axios.put(`/geo/locations/${id}`, form)
        const index = this.locations.findIndex((l) => l.id === id)
        if (index !== -1) {
          this.locations[index] = data.data
        }
        this.$snackbar.success({ text: 'Location updated successfully' })

        return data.data
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
        const { data } = await this.$axios.get('/geo/reviews', {
          params: filters
        })
        this.reviews = data.data
        this.pagination = {
          current_page: data.meta.current_page,
          last_page: data.meta.last_page,
          total: data.meta.total
        }
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
        const { data } = await this.$axios.get('/feedbacks', {
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
        const { data } = await this.$axios.get('/geo/metrics', {
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
        const { data } = await this.$axios.get('/geo/response-templates')
        this.templates = data.data
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

import type {
  Location,
  LocationForm
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
  form: LocationForm;
  loading: boolean;
  storing: boolean;
  pagination: {
    current_page: number;
    last_page: number;
    total: number;
  };
}

export const useGeoStore = defineStore('geo', {
  state: (): State => ({
    locations: [],
    form: { ...defaultForm },
    loading: false,
    storing: false,
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

<script setup lang="ts">
import { useMap } from '@/composables/useMap'
import AppLayout from '@/layouts/AppLayout.vue'
import { useGeoStore } from '@/stores/geo'
import type { BreadcrumbItem } from '@/types'
import type { Coordinates } from '@/types/geo'
import { LMap, LMarker, LPopup, LTileLayer } from '@vue-leaflet/vue-leaflet'
import { storeToRefs } from 'pinia'
import { computed, onMounted } from 'vue'

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Geo', href: '#' },
  { title: 'Locations Map', href: '#' }
]

const geoStore = useGeoStore()
const { locations } = storeToRefs(geoStore)
const { fetchLocations } = geoStore

const { zoom, defaultCenter } = useMap()

const mapCenter = computed<Coordinates>(() => {
  const firstLocation = locations.value[0]
  if (firstLocation?.latitude && firstLocation?.longitude) {
    return [firstLocation.latitude, firstLocation.longitude]
  }

  return defaultCenter
})

onMounted(() => {
  fetchLocations()
})
</script>

<template>
  <AppLayout :breadcrumbs>
    <div style="height: calc(100vh - 50px); width: 100vw">
      <LMap
        v-if="locations.length"
        v-model:zoom="zoom"
        :center="mapCenter"
      >
        <LTileLayer
          url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
          attribution="&copy; OpenStreetMap contributors"
        />

        <LMarker
          v-for="location in locations"
          :key="location.id"
          :lat-lng="[location.latitude, location.longitude]"
        >
          <LPopup>
            <div>
              <h3 class="font-bold">
                {{ location.name }}
              </h3>
              <p>{{ location.address.full_address }}</p>
              <p>
                <strong>Rating:</strong>
                {{
                  location.reviews_avg_rating
                    ? parseFloat(location.reviews_avg_rating).toFixed(1)
                    : 'N/A'
                }}
              </p>
              <p>
                <strong>Reviews:</strong>
                {{ location.reviews_count }}
              </p>
            </div>
          </LPopup>
        </LMarker>
      </LMap>
    </div>
  </AppLayout>
</template>

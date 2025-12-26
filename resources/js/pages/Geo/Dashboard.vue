<script setup lang="ts">
import DashboardMetrics from '@/components/Geo/DashboardMetrics.vue'
import FeedbacksList from '@/components/Geo/FeedbacksList.vue'
import ReviewReplyDialog from '@/components/Geo/ReviewReplyDialog.vue'
import ReviewsList from '@/components/Geo/ReviewsList.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { dashboard as geoDashboard } from '@/routes/geo'
import { useGeoStore } from '@/stores/geo'
import type { BreadcrumbItem } from '@/types'
import type { Review } from '@/types/geo'
import { storeToRefs } from 'pinia'
import { onMounted, ref, watch } from 'vue'

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Geo', href: '#' },
  { title: 'Dashboard', href: geoDashboard().url }
]

const geoStore = useGeoStore()
const {
  locations,
  reviews,
  feedbacks,
  metrics,
  templates,
  reviewsLoading,
  feedbacksLoading
} = storeToRefs(geoStore)
const {
  fetchLocations,
  fetchReviews,
  fetchFeedbacks,
  fetchMetrics,
  fetchTemplates
} = geoStore

const showReplyDialog = ref(false)
const selectedReview = ref<Review | null>(null)
const selectedLocationId = ref<number | null>(null)
const activeTab = ref('external')

const loadDashboardData = async() => {
  const filters = selectedLocationId.value
    ? { location_id: selectedLocationId.value }
    : {}

  await Promise.all([
    fetchReviews(filters),
    fetchMetrics(filters)
  ])
}

watch(selectedLocationId, loadDashboardData)

const openReplyDialog = (review: Review) => {
  selectedReview.value = review
  showReplyDialog.value = true
}

onMounted(async() => {
  await Promise.all([
    fetchLocations(),
    fetchFeedbacks(),
    fetchTemplates(),
    loadDashboardData()
  ])
})
</script>

<template>
  <AppLayout :breadcrumbs>
    <div class="py-4">
      <div class="sm:px-6 lg:px-8">
        <DashboardMetrics
          v-if="metrics"
          :metrics="metrics"
        />

        <VTabs
          v-model="activeTab"
          class="mb-4"
        >
          <VTab value="external">
            External Reviews
          </VTab>
          <VTab value="internal">
            Internal Feedbacks
          </VTab>

          <v-spacer />

          <template v-if="locations.length">
            <VSelect
              v-if="activeTab === 'external'"
              v-model="selectedLocationId"
              :items="locations"
              item-title="name"
              item-value="id"
              label="Filter by Location"
              clearable
              variant="solo"
              density="compact"
              class="max-w-xs"
              hide-details
            />
          </template>
        </VTabs>

        <VWindow v-model="activeTab">
          <VWindowItem value="external">
            <ReviewsList
              :reviews="reviews"
              :loading="reviewsLoading"
              @reply="openReplyDialog"
            />
          </VWindowItem>

          <VWindowItem value="internal">
            <FeedbacksList
              :feedbacks="feedbacks"
              :loading="feedbacksLoading"
            />
          </VWindowItem>
        </VWindow>
      </div>
    </div>

    <ReviewReplyDialog
      v-model="showReplyDialog"
      :review="selectedReview"
      :templates="templates"
    />
  </AppLayout>
</template>

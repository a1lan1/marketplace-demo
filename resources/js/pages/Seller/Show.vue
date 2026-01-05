<script setup lang="ts">
import FeedbackForm from '@/components/Feedback/FeedbackForm.vue'
import FeedbackList from '@/components/Feedback/FeedbackList.vue'
import ProductCard from '@/components/product/ProductCard.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { catalog as catalogIndex } from '@/routes/products'
import type { BreadcrumbItem, Seller } from '@/types'
import { Head, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps<{
  seller: Seller;
}>()

const page = usePage()
const auth = computed(() => page.props.auth)

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Catalog',
    href: catalogIndex().url
  },
  {
    title: 'Seller',
    href: '#'
  },
  {
    title: props.seller.name,
    href: '#'
  }
]
</script>

<template>
  <Head :title="seller.name" />

  <AppLayout :breadcrumbs>
    <VContainer>
      <!-- Seller Info -->
      <VCard
        flat
        class="mb-4 border"
      >
        <VCardText>
          <div class="flex flex-col items-center md:flex-row md:items-start">
            <VAvatar
              size="120"
              class="border"
            >
              <v-img
                :src="seller.avatar"
                :alt="seller.name"
              />
            </VAvatar>

            <div class="flex-1 text-center md:text-left">
              <div class="flex items-center justify-between">
                <v-card-title>{{ seller.name }}</v-card-title>
                <VChip
                  color="success"
                  variant="flat"
                  prepend-icon="mdi-check-bold"
                >
                  Verified Seller
                </VChip>
              </div>

              <v-card-subtitle class="text-gray-200">
                Member since {{ seller.created_at }}
              </v-card-subtitle>

              <v-card-text class="flex items-center gap-2">
                <VRating
                  :model-value="seller.average_rating"
                  readonly
                  half-increments
                  density="compact"
                  color="warning"
                />
                <span class="font-weight-bold text-xl">
                  {{ seller.average_rating }}
                </span>
                <span class="text-gray-500">({{ seller.reviews_count }} reviews)</span>
              </v-card-text>
            </div>
          </div>
        </VCardText>
      </VCard>

      <!-- Seller Products -->
      <h3 class="text-h5 font-weight-bold mb-4">
        Latest Products
      </h3>
      <VRow v-if="seller.products && seller.products.length > 0">
        <VCol
          v-for="product in seller.products"
          :key="product.id"
          cols="12"
          sm="6"
          md="4"
          lg="3"
        >
          <ProductCard :product="product" />
        </VCol>
      </VRow>
      <div
        v-else
        class="mb-8 text-gray-500"
      >
        No products available.
      </div>

      <!-- Feedback -->
      <VRow>
        <VCol cols="12">
          <template v-if="auth.user && auth.user.id !== seller.id">
            <FeedbackForm
              :feedbackable-id="seller.id"
              feedbackable-type="seller"
            />
          </template>
          <v-alert
            v-else-if="!auth.user"
            type="info"
            variant="tonal"
            class="mb-6"
          >
            You must be logged in to write a review.
          </v-alert>

          <FeedbackList
            :feedbackable-id="seller.id"
            feedbackable-type="seller"
          />
        </VCol>
      </VRow>
    </VContainer>
  </AppLayout>
</template>

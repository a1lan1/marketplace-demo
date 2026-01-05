<script setup lang="ts">
import AddToCartBtn from '@/components/cart/AddToCartBtn.vue'
import Money from '@/components/common/Money.vue'
import FeedbackForm from '@/components/Feedback/FeedbackForm.vue'
import FeedbackList from '@/components/Feedback/FeedbackList.vue'
import ProductAutocomplete from '@/components/product/ProductAutocomplete.vue'
import ProductSeller from '@/components/product/ProductSeller.vue'
import ProductStockStatus from '@/components/product/ProductStockStatus.vue'
import RecommendedProducts from '@/components/product/RecommendedProducts.vue'
import { trackEvent } from '@/composables/useActivity'
import AppLayout from '@/layouts/AppLayout.vue'
import { catalog as catalogIndex } from '@/routes/products'
import { useFeedbackStore } from '@/stores/feedback'
import type { BreadcrumbItem, Product } from '@/types'
import { Head, usePage } from '@inertiajs/vue3'
import { storeToRefs } from 'pinia'
import { computed, onMounted } from 'vue'

const props = defineProps<{
  product: Product;
  recommendations: Product[];
}>()

const page = usePage()
const auth = computed(() => page.props.auth)

const feedbackStore = useFeedbackStore()
const { feedbacks } = storeToRefs(feedbackStore)

const userHasFeedback = computed(() => {
  if (!auth.value.user) return false

  return feedbacks.value.some((f) => f.author?.id === auth.value.user.id)
})

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Catalog',
    href: catalogIndex().url
  },
  {
    title: props.product.name,
    href: '#'
  }
]

onMounted(() => {
  trackEvent('page_view', undefined, {
    product_id: props.product.id
  })
})
</script>

<template>
  <Head :title="product.name" />

  <AppLayout :breadcrumbs>
    <template #header>
      <h2 class="text-h5">
        {{ product.name }}
      </h2>
    </template>

    <v-container>
      <ProductAutocomplete />

      <v-row>
        <v-col
          cols="12"
          md="6"
        >
          <v-img
            :src="product.cover_image"
            :alt="product.name"
            aspect-ratio="1.5"
            cover
            class="rounded-lg border"
          />
        </v-col>

        <v-col
          cols="12"
          md="6"
        >
          <v-card flat>
            <v-card-title class="text-h4 font-weight-bold">
              {{ product.name }}
            </v-card-title>
            <v-card-subtitle v-if="product.seller">
              <ProductSeller
                :seller="product.seller"
                chip
                with-avatar
              />
            </v-card-subtitle>

            <v-card-text>
              <p class="text-body-1 mb-4">
                {{ product.description || 'No description available.' }}
              </p>

              <v-divider class="my-4" />

              <div class="align-center mb-4 flex justify-between">
                <div class="d-flex align-center">
                  <Money
                    :value="product.price"
                    size="large"
                    chip
                  />
                </div>

                <ProductStockStatus :stock="product.stock" />
              </div>

              <AddToCartBtn
                :product
                block
              />
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <v-divider class="my-2" />

      <v-row dense>
        <v-col cols="12">
          <template v-if="auth.user">
            <FeedbackForm
              v-if="!userHasFeedback"
              :feedbackable-id="product.id"
              feedbackable-type="product"
            />
            <v-alert
              v-else
              type="success"
              variant="tonal"
              class="mb-6"
            >
              You have already submitted feedback for this product.
            </v-alert>
          </template>
          <v-alert
            v-else
            type="info"
            variant="tonal"
            class="mb-6"
          >
            You must be logged in to write a review.
          </v-alert>

          <FeedbackList
            :feedbackable-id="product.id"
            feedbackable-type="product"
          />
        </v-col>
      </v-row>

      <RecommendedProducts
        v-if="auth.user"
        :products="recommendations"
        class="mt-4"
      />
    </v-container>
  </AppLayout>
</template>

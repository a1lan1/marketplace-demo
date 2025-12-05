<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { usePermissions } from '@/composables/usePermissions'
import {
  create as productsCreate,
  edit as productsEdit,
  destroy as productsDestroy
} from '@/routes/products'
import type { Product, Pagination, BreadcrumbItem } from '@/types'

defineProps<{
  products: Pagination<Product>;
}>()

const { hasPermission } = usePermissions()

const deleteProduct = (product: Product) => {
  if (confirm(`Are you sure you want to delete "${product.name}"?`)) {
    router.delete(productsDestroy(product.id).url, {
      preserveScroll: true
    })
  }
}

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'My Products',
    href: '#'
  }
]

const tableHeaders = [
  { title: '', key: 'cover_image', align: 'start' },
  { title: 'Name', value: 'name' },
  { title: 'Price', value: 'price.formatted' },
  { title: 'Stock', value: 'stock' },
  { title: 'Actions', value: 'actions', sortable: false, align: 'end' }
]
</script>

<template>
  <Head title="My Products" />

  <AppLayout :breadcrumbs>
    <v-container>
      <v-card>
        <v-card-actions>
          <v-spacer />
          <v-btn
            v-if="hasPermission('products.create')"
            :href="productsCreate().url"
            color="success"
            variant="elevated"
          >
            New Product
          </v-btn>
        </v-card-actions>
        <v-card-text>
          <v-data-table
            v-if="products.data && products.data.length > 0"
            :headers="tableHeaders"
            :items="products.data"
            item-value="id"
          >
            <template #[`item.cover_image`]="{ item }">
              <v-img
                :src="item.cover_image"
                width="100"
                rounded
                class="my-2"
              />
            </template>
            <template #[`item.actions`]="{ item }: { item: Product }">
              <div class="d-flex justify-end align-center">
                <Link
                  v-if="hasPermission('products.edit-own')"
                  :href="productsEdit(item.id).url"
                  class="mr-2"
                >
                  <v-icon small>
                    mdi-pencil
                  </v-icon>
                </Link>
                <v-icon
                  v-if="hasPermission('products.delete-own')"
                  small
                  color="error"
                  style="cursor: pointer;"
                  @click="deleteProduct(item)"
                >
                  mdi-delete
                </v-icon>
              </div>
            </template>
          </v-data-table>
          <v-alert
            v-else
            type="info"
            variant="tonal"
          >
            You don't have any products yet.
          </v-alert>
        </v-card-text>
      </v-card>
    </v-container>
  </AppLayout>
</template>

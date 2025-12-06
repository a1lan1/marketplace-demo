<script setup lang="ts">
import { useProductForm } from '@/composables/form/useProductForm'
import type { Product } from '@/types'

const props = defineProps<{
  initialData?: Product;
  url: string;
  method: 'post' | 'put';
}>()

const { form, errors, validate, toFormData } = useProductForm(
  props.initialData
)

const submitForm = () => {
  if (!validate()) return

  const fd = toFormData()

  if (props.method === 'put') {
    fd.append('_method', 'PUT')
  }

  form
    .transform(() => fd)
    .post(props.url, {
      forceFormData: true
    })
}

const onFileChange = (files: File | File[] | null) => {
  if (!files) {
    form.cover_image = null
  }

  if (Array.isArray(files)) {
    form.cover_image = files[0] || null
  } else {
    form.cover_image = files
  }
}
</script>

<template>
  <v-container>
    <v-row>
      <v-col
        v-if="initialData?.cover_image"
        cols="12"
        md="5"
      >
        <v-img
          :src="initialData.cover_image"
          :alt="form.name"
          aspect-ratio="1.5"
          cover
          class="rounded-lg border"
        />
      </v-col>

      <v-col
        cols="12"
        :md="initialData?.cover_image ? 7 : undefined"
      >
        <form @submit.prevent="submitForm">
          <v-text-field
            v-model="form.name"
            :error-messages="errors.name"
            label="Name"
            required
          />

          <v-textarea
            v-model="form.description"
            :error-messages="errors.description"
            label="Description"
            class="mt-4"
          />

          <v-text-field
            v-model="form.price"
            :error-messages="errors.price"
            label="Price"
            type="number"
            required
            class="mt-4"
          />

          <v-text-field
            v-model="form.stock"
            :error-messages="errors.stock"
            label="Stock"
            type="number"
            required
            class="mt-4"
          />

          <v-file-input
            :model-value="form.cover_image"
            :error-messages="errors.cover_image"
            label="Cover Image"
            accept="image/*"
            prepend-icon="mdi-camera"
            class="mt-4"
            @update:model-value="onFileChange"
          />

          <div class="d-flex mt-4 justify-end">
            <v-btn
              type="submit"
              color="primary"
              :loading="form.processing"
            >
              {{ initialData ? 'Update Product' : 'Create Product' }}
            </v-btn>
          </div>
        </form>
      </v-col>
    </v-row>
  </v-container>
</template>

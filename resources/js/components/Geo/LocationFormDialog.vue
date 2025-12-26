<script setup lang="ts">
import { snackbar } from '@/plugins/snackbar'
import { useGeoStore } from '@/stores/geo'
import type { LocationType } from '@/types/geo'
import { storeToRefs } from 'pinia'
import { computed } from 'vue'

const props = defineProps<{
  isEditing?: boolean;
}>()

const model = defineModel<boolean>()

const emit = defineEmits<{
  (e: 'saved'): void;
}>()

const geoStore = useGeoStore()
const { form, storing } = storeToRefs(geoStore)
const { createLocation, updateLocation, resetForm } = geoStore

const locationTypes: { title: string; value: LocationType }[] = [
  { title: 'Store', value: 'store' },
  { title: 'Pickup Point', value: 'pickup_point' },
  { title: 'Warehouse', value: 'warehouse' },
  { title: 'Office', value: 'office' }
]

const title = computed(() =>
  props.isEditing ? 'Edit Location' : 'Add New Location'
)

const handleSave = async() => {
  if (!form.value.name || !form.value.address.full_address) {
    snackbar.error({ text: 'Name and full address are required.' })

    return
  }

  try {
    if (props.isEditing && form.value.id) {
      await updateLocation(form.value.id, form.value)
    } else {
      await createLocation(form.value)
    }
    model.value = false
    resetForm()
    emit('saved')
  } catch (e) {
    console.log('Error is handled in store', e)
  }
}

const handleCancel = () => {
  model.value = false
  resetForm()
}
</script>

<template>
  <VDialog
    v-model="model"
    max-width="600px"
    :persistent="storing"
  >
    <VCard>
      <VCardTitle>{{ title }}</VCardTitle>
      <VCardText>
        <VRow>
          <VCol cols="12">
            <VTextField
              v-model="form.name"
              label="Location Name"
              required
            />
          </VCol>
          <VCol cols="12">
            <VSelect
              v-model="form.type"
              :items="locationTypes"
              label="Location Type"
              required
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="form.address.country"
              label="Country"
              required
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="form.address.city"
              label="City"
              required
            />
          </VCol>
          <VCol
            cols="12"
            md="8"
          >
            <VTextField
              v-model="form.address.street"
              label="Street"
              required
            />
          </VCol>
          <VCol
            cols="12"
            md="4"
          >
            <VTextField
              v-model="form.address.house_number"
              label="House Number"
              required
            />
          </VCol>
          <VCol
            cols="12"
            md="4"
          >
            <VTextField
              v-model="form.address.postal_code"
              label="Postal Code"
              required
            />
          </VCol>
          <VCol
            cols="12"
            md="8"
          >
            <VTextField
              v-model="form.address.full_address"
              label="Full Address"
              required
              hint="Complete address string"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model.number="form.latitude"
              label="Latitude"
              type="number"
              step="any"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model.number="form.longitude"
              label="Longitude"
              type="number"
              step="any"
            />
          </VCol>
        </VRow>
      </VCardText>
      <div class="flex justify-end gap-2 p-4">
        <VBtn
          color="grey"
          variant="text"
          :disabled="storing"
          @click="handleCancel"
        >
          Cancel
        </VBtn>
        <VBtn
          color="primary"
          :loading="storing"
          :disabled="storing"
          @click="handleSave"
        >
          Save
        </VBtn>
      </div>
    </VCard>
  </VDialog>
</template>

<script setup lang="ts">
import { snackbar } from '@/plugins/snackbar'
import type { ResponseTemplate, Review } from '@/types/geo'
import { ref, watch } from 'vue'

const props = defineProps<{
  review: Review | null;
  templates: ResponseTemplate[];
}>()

const model = defineModel<boolean>()

const replyText = ref('')
const selectedTemplateId = ref<number | null>(null)

watch(selectedTemplateId, (newId) => {
  if (newId) {
    const template = props.templates.find((t) => t.id === newId)
    if (template) {
      replyText.value = template.body
    }
  }
})

watch(model, (isOpen) => {
  if (isOpen) {
    replyText.value = ''
    selectedTemplateId.value = null
  }
})

const handleSendReply = () => {
  snackbar.info({ text: 'Reply functionality is in development.' })
  model.value = false
}
</script>

<template>
  <VDialog
    v-model="model"
    max-width="600px"
  >
    <VCard>
      <VCardTitle>Reply to {{ review?.author_name }}</VCardTitle>
      <VCardText>
        <VSelect
          v-model="selectedTemplateId"
          :items="templates"
          item-title="title"
          item-value="id"
          label="Use a template"
          clearable
          variant="outlined"
          density="compact"
          class="mb-4"
        />
        <VTextarea
          v-model="replyText"
          label="Your Reply"
          rows="5"
          variant="outlined"
          auto-grow
        />
      </VCardText>
      <div class="flex justify-end gap-2 p-4">
        <VBtn
          color="grey"
          variant="text"
          @click="model = false"
        >
          Cancel
        </VBtn>
        <VBtn
          color="primary"
          @click="handleSendReply"
        >
          Send Reply
        </VBtn>
      </div>
    </VCard>
  </VDialog>
</template>

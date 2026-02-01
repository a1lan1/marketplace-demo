<script setup lang="ts">
import type { PaginationMeta } from '@/types'

interface Props {
  items: any[];
  pagination: PaginationMeta | null;
  onLoad: (page: number) => Promise<void>;
}

const props = withDefaults(defineProps<Props>(), {
  items: () => [],
  pagination: null
})

const loadMore = async({ done }: { done: (status: 'ok' | 'error' | 'empty') => void }) => {
  if (!props.pagination || props.pagination.current_page >= props.pagination.last_page) {
    done('empty')

    return
  }

  try {
    await props.onLoad(props.pagination.current_page + 1)
    done('ok')
  } catch (e) {
    console.error(e)
    done('error')
  }
}
</script>

<template>
  <v-infinite-scroll
    v-if="pagination"
    :items="items"
    @load="loadMore"
  >
    <template #loading>
      <v-progress-circular
        indeterminate
        color="accent"
        size="small"
        class="mt-5"
      />
    </template>

    <template #empty>
      <slot name="empty">
        <div class="text-caption text-medium-emphasis py-4 text-center">
          No more items
        </div>
      </slot>
    </template>

    <template #error="{ props: errorProps }">
      <slot
        name="error"
        v-bind="errorProps"
      >
        <div class="text-caption text-error py-4 text-center">
          Error loading items
          <v-btn
            variant="text"
            size="small"
            color="primary"
            @click="errorProps.onClick"
          >
            Retry
          </v-btn>
        </div>
      </slot>
    </template>

    <slot />
  </v-infinite-scroll>
  <div v-else>
    <slot />
  </div>
</template>

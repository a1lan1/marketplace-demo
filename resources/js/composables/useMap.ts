import type { Coordinates } from '@/types/geo'
import { ref } from 'vue'

export function useMap() {
  const zoom = ref<number>(12)
  const defaultCenter: Coordinates = [-8.38, -46.45]

  return {
    zoom,
    defaultCenter
  }
}

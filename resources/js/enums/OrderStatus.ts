export const orderStatusOptions = [
  'pending',
  'processing',
  'completed',
  'cancelled'
] as const

export type OrderStatus = typeof orderStatusOptions[number]

export enum StatusVariant {
  cancelled = 'error',
  pending = 'warning',
  processing = 'info',
  completed = 'success'
}

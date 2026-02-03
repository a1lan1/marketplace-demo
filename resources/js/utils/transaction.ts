import type { TransactionType } from '@/types'

export const getTransactionIcon = (type: TransactionType): string => {
  switch (type) {
  case 'deposit':
    return 'mdi-arrow-down-bold'
  case 'withdrawal':
    return 'mdi-arrow-up-bold'
  case 'transfer':
    return 'mdi-swap-horizontal-bold'
  case 'purchase':
    return 'mdi-cart'
  default:
    return 'mdi-help-circle'
  }
}

export const getTransactionColor = (type: TransactionType): string | undefined => {
  switch (type) {
  case 'deposit':
  case 'transfer':
    return 'success'
  case 'withdrawal':
    return 'error'
  case 'purchase':
    return 'info'
  default:
    return undefined
  }
}

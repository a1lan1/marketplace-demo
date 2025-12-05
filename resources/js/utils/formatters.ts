import { MoneyData } from '@/types'

export function formatCurrency(
  value: number | string | MoneyData,
  currency: string = 'USD'
): string {
  let num: number

  if (typeof value === 'object' && 'amount' in value && 'currency' in value) {
    num = Number(value.amount) / 100
    currency = value.currency
  } else {
    num = Number(value)
  }

  if (isNaN(num)) {
    num = 0
  }

  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency,
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(num)
}

export function formatDate(dateString: string): string {
  const date = new Date(dateString)

  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date)
}

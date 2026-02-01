import { useCurrencyStore } from '@/stores/currency'
import { MoneyData } from '@/types'
import { formatCurrency } from '@/utils/formatters'
import { storeToRefs } from 'pinia'
import { computed, MaybeRefOrGetter, toValue } from 'vue'

export function useMoney(
  value: MaybeRefOrGetter<number | MoneyData | null | undefined>
) {
  const currencyStore = useCurrencyStore()
  const { currentCurrency, rates } = storeToRefs(currencyStore)

  const amountInBase = computed(() => {
    const rawValue = toValue(value)

    if (rawValue === null || rawValue === undefined) {
      return 0
    }

    if (typeof rawValue === 'object' && 'amount' in rawValue) {
      return Number(rawValue.amount) / 100
    }

    return Number(rawValue) / 100
  })

  const convertedAmount = computed(() => {
    if (currentCurrency.value === 'USD') {
      return amountInBase.value
    }

    const rate = rates.value[currentCurrency.value]

    return rate ? amountInBase.value * rate : amountInBase.value
  })

  const formatted = computed(() =>
    formatCurrency(convertedAmount.value * 100, currentCurrency.value)
  )

  return {
    amount: convertedAmount,
    formatted,
    currency: currentCurrency
  }
}

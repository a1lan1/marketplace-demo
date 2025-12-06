import type { Product, ProductFormData } from '@/types'
import { useForm } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { z } from 'zod'

export const productSchema = z.object({
  name: z.string().min(3, 'Name must be at least 3 characters long'),
  description: z.string().nullable().optional(),
  price: z.number().min(0.01, 'Price must be a positive number'),
  stock: z.number().int().min(0, 'Stock must be a positive integer'),
  cover_image: z.instanceof(File).nullable().optional()
})

export function useProductForm(initial?: Product) {
  const form = useForm<ProductFormData>({
    name: initial?.name ?? '',
    description: initial?.description ?? null,
    price: initial ? initial.price.amount / 100 : 0,
    stock: initial?.stock ?? 0,
    cover_image: null
  })

  // Zod errors
  const errors = ref<Record<string, string>>({})

  const validate = () => {
    const parsed = productSchema.safeParse({
      name: form.name,
      description: form.description,
      price: Number(form.price),
      stock: Number(form.stock),
      cover_image: form.cover_image
    })

    if (!parsed.success) {
      errors.value = Object.fromEntries(
        parsed.error.issues.map((i) => [i.path[0], i.message])
      )

      return false
    }

    errors.value = {}

    return true
  }

  const toFormData = () => {
    const fd = new FormData()
    fd.append('name', form.name)
    fd.append('description', form.description ?? '')
    fd.append('price', String(Number(form.price) * 100))
    fd.append('stock', String(form.stock))

    if (form.cover_image) {
      fd.append('cover_image', form.cover_image)
    }

    return fd
  }

  watch(
    () => initial,
    (newVal) => {
      if (!newVal) return

      form.name = newVal.name
      form.description = newVal.description
      form.price = newVal.price.amount / 100
      form.stock = newVal.stock
      form.cover_image = null
    },
    { deep: true }
  )

  return {
    form,
    validate,
    errors,
    toFormData
  }
}

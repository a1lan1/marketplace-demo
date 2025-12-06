import { api } from '@/plugins/axios'
import { router, usePage } from '@inertiajs/vue3'
import {
  useDebounceFn,
  useEventListener,
  useOnline,
  useStorage
} from '@vueuse/core'
import { watch } from 'vue'

type ActivityPayload = {
  event_type: string;
  page: string;
  props?: Record<string, unknown>;
};

// Debounce interval for rapid route changes / duplicate events
const MIN_INTERVAL_MS = 500
const STORAGE_KEY = 'activity_queue_v1'

// Persistent queue
const queue = useStorage<ActivityPayload[]>(STORAGE_KEY, [])
const online = useOnline()

async function flushQueue() {
  if (!online.value || !queue.value.length) return

  const rest: ActivityPayload[] = []

  for (const item of queue.value) {
    try {
      await api.post('/user-activities', item)
    } catch {
      rest.push(item)
    }
  }
  queue.value = rest
}

const send = async(payload: ActivityPayload) => {
  if (!online.value) {
    queue.value.push(payload)

    return
  }

  try {
    await api.post('/user-activities', payload)
  } catch {
    queue.value.push(payload)
  }
}

const sendDebounced = useDebounceFn((payload: ActivityPayload) => {
  void send(payload)
}, MIN_INTERVAL_MS)

export function trackEvent(
  event_type: string,
  pageUrl?: string,
  props?: Record<string, unknown>
) {
  const currentPage = usePage()
  const page =
    pageUrl ?? currentPage.url ?? `${location.pathname}${location.search}`

  sendDebounced({
    event_type,
    page,
    props
  })
}

export function initActivityAutoTrack() {
  // initial page
  trackEvent('page_view')

  // flush queued when back online (reactive)
  watch(
    online,
    (isOnline) => {
      if (isOnline) void flushQueue()
    },
    { immediate: true }
  )

  // track on every inertia navigation
  router.on('navigate', () => trackEvent('page_view'))

  // auto-track clicks on elements annotated with data-track="click"
  const clickHandler = (e: MouseEvent) => {
    const target = e.target as HTMLElement | null

    if (!target) return

    const el = target.closest('[data-track="click"]') as HTMLElement | null

    if (!el) return

    const props: Record<string, unknown> = {
      id: el.id || undefined,
      text: (el.textContent || '').trim().slice(0, 100) || undefined
    }

    const href = (el as HTMLAnchorElement).href

    if (href) props.href = href

    trackClick(undefined, props)
  }

  useEventListener(document, 'click', clickHandler, { capture: true })
}

export function trackClick(page?: string, props?: Record<string, unknown>) {
  trackEvent('click', page, props)
}

export function trackSignIn(props?: Record<string, unknown>) {
  trackEvent('sign_in', undefined, props)
}

export function trackSignUp(props?: Record<string, unknown>) {
  trackEvent('sign_up', undefined, props)
}

export function trackError(message: string, extra?: Record<string, unknown>) {
  trackEvent('error', undefined, { message, ...(extra || {}) })
}

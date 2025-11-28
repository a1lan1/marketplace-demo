import { configureEcho } from '@laravel/echo-vue'

configureEcho({
  broadcaster: 'pusher',
  key: 'local',
  wsHost: window.location.hostname,
  wsPort: 6001,
  forceTLS: false,
  enabledTransports: ['ws'],
  disableStats: true
})

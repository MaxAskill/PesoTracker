import api from './api'

const DASHBOARD_CACHE_KEY = 'pesotracker_dashboard_snapshot'
const WARMUP_KEY = 'pesotracker_backend_warmup_at'
const WARMUP_INTERVAL = 60 * 1000

const readJson = (key) => {
  try {
    return JSON.parse(localStorage.getItem(key))
  } catch {
    return null
  }
}

const currentUserId = () => readJson('user')?.id ?? null

export const loadDashboardSnapshot = () => {
  const snapshot = readJson(DASHBOARD_CACHE_KEY)

  if (!snapshot) return null

  if (snapshot.user_id && snapshot.user_id !== currentUserId()) {
    return null
  }

  return snapshot
}

export const saveDashboardSnapshot = (snapshot) => {
  localStorage.setItem(
    DASHBOARD_CACHE_KEY,
    JSON.stringify({
      ...snapshot,
      user_id: currentUserId(),
      cached_at: new Date().toISOString()
    })
  )
}

export const warmBackend = async () => {
  const lastWarmup = Number(localStorage.getItem(WARMUP_KEY) || 0)

  if (Date.now() - lastWarmup < WARMUP_INTERVAL) return

  localStorage.setItem(WARMUP_KEY, String(Date.now()))

  try {
    await api.get('/test', { timeout: 20000 })
  } catch (error) {
    console.info('Backend warm-up did not finish yet.', error?.message)
  }
}

export const preloadAuthenticatedData = async () => {
  if (!localStorage.getItem('token')) return null

  const [
    dashboard,
    analytics,
    insights,
    financialHealth,
    notifications,
    unreadCount
  ] = await Promise.allSettled([
    api.get('/dashboard'),
    api.get('/analytics/summary'),
    api.get('/insights'),
    api.get('/financial-health'),
    api.get('/notifications'),
    api.get('/notifications/unread-count')
  ])

  const snapshot = {
    dashboard: dashboard.status === 'fulfilled' ? dashboard.value.data : null,
    analytics: analytics.status === 'fulfilled' ? analytics.value.data : null,
    insights: insights.status === 'fulfilled' ? insights.value.data : null,
    financialHealth: financialHealth.status === 'fulfilled' ? financialHealth.value.data : null,
    notifications: notifications.status === 'fulfilled' ? notifications.value.data : null,
    unreadCount: unreadCount.status === 'fulfilled' ? unreadCount.value.data.count : null
  }

  if (Object.values(snapshot).some((value) => value !== null)) {
    saveDashboardSnapshot(snapshot)
  }

  return snapshot
}

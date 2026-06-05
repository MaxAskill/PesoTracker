import api from './api'

const DASHBOARD_CACHE_KEY = 'pesotracker_dashboard_snapshot'
const WARMUP_KEY = 'pesotracker_backend_warmup_at'
const WARMUP_INTERVAL = 60 * 1000
const DISPLAY_CACHE_PREFIX = 'pesotracker_display_cache'
const DISPLAY_CACHE_TTL = 5 * 60 * 1000

const readJson = (key) => {
  try {
    return JSON.parse(localStorage.getItem(key))
  } catch {
    return null
  }
}

const currentUserId = () => readJson('user')?.id ?? null

const isFresh = (cachedAt, ttl = DISPLAY_CACHE_TTL) => {
  if (!cachedAt) return false

  return Date.now() - new Date(cachedAt).getTime() < ttl
}

const displayCacheKey = (name) => {
  const userId = currentUserId()

  return userId ? `${DISPLAY_CACHE_PREFIX}:${userId}:${name}` : null
}

export const loadDashboardSnapshot = () => {
  const snapshot = readJson(DASHBOARD_CACHE_KEY)

  if (!snapshot) return null

  if (snapshot.user_id && snapshot.user_id !== currentUserId()) {
    return null
  }

  if (!isFresh(snapshot.cached_at)) {
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

export const loadDisplayCache = (name) => {
  const key = displayCacheKey(name)
  if (!key) return null

  const cached = readJson(key)

  if (!cached || !isFresh(cached.cached_at)) {
    return null
  }

  return cached.data
}

export const saveDisplayCache = (name, data) => {
  const key = displayCacheKey(name)
  if (!key) return

  localStorage.setItem(
    key,
    JSON.stringify({
      data,
      cached_at: new Date().toISOString()
    })
  )
}

export const clearDisplayCache = (names = []) => {
  const keys = names
    .map(displayCacheKey)
    .filter(Boolean)

  keys.forEach((key) => localStorage.removeItem(key))
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
    unreadCount,
    transactions,
    budgets,
    savingsGoals
  ] = await Promise.allSettled([
    api.get('/dashboard'),
    api.get('/analytics/summary'),
    api.get('/insights'),
    api.get('/financial-health'),
    api.get('/notifications'),
    api.get('/notifications/unread-count'),
    api.get('/transactions'),
    api.get('/budgets'),
    api.get('/savings-goals')
  ])

  if (!localStorage.getItem('token')) return null

  const snapshot = {
    dashboard: dashboard.status === 'fulfilled' ? dashboard.value.data : null,
    analytics: analytics.status === 'fulfilled' ? analytics.value.data : null,
    insights: insights.status === 'fulfilled' ? insights.value.data : null,
    financialHealth: financialHealth.status === 'fulfilled' ? financialHealth.value.data : null,
    notifications: notifications.status === 'fulfilled' ? notifications.value.data : null,
    unreadCount: unreadCount.status === 'fulfilled' ? unreadCount.value.data.count : null,
    transactions: transactions.status === 'fulfilled' ? transactions.value.data : null,
    budgets: budgets.status === 'fulfilled' ? budgets.value.data : null,
    savingsGoals: savingsGoals.status === 'fulfilled' ? savingsGoals.value.data : null
  }

  if (Object.values(snapshot).some((value) => value !== null)) {
    saveDashboardSnapshot(snapshot)
  }

  if (snapshot.transactions) {
    saveDisplayCache('transactions', snapshot.transactions)
  }

  if (snapshot.budgets) {
    saveDisplayCache('budgets', snapshot.budgets)
  }

  if (snapshot.savingsGoals) {
    saveDisplayCache('savings-goals', snapshot.savingsGoals)
  }

  return snapshot
}

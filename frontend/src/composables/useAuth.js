import { computed, ref } from 'vue'
import api, {
  abortActiveRequests,
  clearApiAuthHeader,
  configureAuthFailureHandler
} from '../services/api'

const readUser = () => {
  try {
    return JSON.parse(localStorage.getItem('user'))
  } catch {
    return null
  }
}

const token = ref(localStorage.getItem('token'))
const user = ref(readUser())
const isRedirectingToLogin = ref(false)
let routerInstance = null

export const isAuthenticated = computed(() => Boolean(token.value))

export const setAuthRouter = (router) => {
  routerInstance = router
}

export const syncAuthState = () => {
  token.value = localStorage.getItem('token')
  user.value = readUser()
}

export const setAuthState = (authToken, authUser) => {
  localStorage.setItem('token', authToken)
  localStorage.setItem('user', JSON.stringify(authUser))
  token.value = authToken
  user.value = authUser
  isRedirectingToLogin.value = false
}

export const clearAuthState = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  sessionStorage.removeItem('token')
  sessionStorage.removeItem('user')
  token.value = null
  user.value = null
  clearApiAuthHeader()
  window.dispatchEvent(new CustomEvent('pesotracker-auth-cleared'))
}

const redirectToLogin = (router = routerInstance) => {
  const currentPath = router?.currentRoute?.value?.path || window.location.pathname

  if (currentPath === '/login') return
  if (isRedirectingToLogin.value) return

  isRedirectingToLogin.value = true

  if (router) {
    router.replace('/login').finally(() => {
      isRedirectingToLogin.value = false
    })
    return
  }

  window.location.assign('/login')
}

export const handleAuthFailure = (router = routerInstance) => {
  clearAuthState()
  abortActiveRequests()
  redirectToLogin(router)
}

export const logout = (router = routerInstance) => {
  const logoutToken = token.value || localStorage.getItem('token')

  if (logoutToken) {
    api.post('/logout', null, {
      headers: {
        Authorization: `Bearer ${logoutToken}`
      },
      skipAuthRedirect: true,
      skipGlobalAbort: true
    }).catch(() => {})
  }

  clearAuthState()
  abortActiveRequests()
  redirectToLogin(router)
}

export const useAuth = () => ({
  token,
  user,
  isAuthenticated,
  setAuthState,
  clearAuthState,
  handleAuthFailure,
  logout,
  syncAuthState
})

configureAuthFailureHandler(() => handleAuthFailure())

import { createRouter, createWebHistory } from 'vue-router'

import Login from '../pages/Login.vue'
import Register from '../pages/Register.vue'
import VerifyOtp from '../pages/VerifyOtp.vue'
import Dashboard from '../pages/Dashboard.vue'
import Transactions from '../pages/Transactions.vue'
import Budgets from '../pages/Budgets.vue'
import SavingsGoals from '../pages/SavingsGoals.vue'
import Reports from '../pages/Reports.vue'
import RecurringTransactions from "../pages/RecurringTransactions.vue"
import Landing from '../pages/Landing.vue'
import Profile from '../pages/Profile.vue'

const routes = [
 { path: '/', name: 'landing', component: Landing, meta: { keepAlive: false } },

  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { guest: true, keepAlive: false }
  },

  {
    path: '/register',
    name: 'register',
    component: Register,
    meta: { guest: true, keepAlive: false }
  },

  {
    path: '/verify-otp',
    name: 'verify-otp',
    component: VerifyOtp,
    meta: { guest: true, keepAlive: false }
  },

  {
    path: '/dashboard',
    name: 'dashboard',
    component: Dashboard,
    meta: { requiresAuth: true, keepAlive: true }
  },

  {
    path: '/transactions',
    name: 'transactions',
    component: Transactions,
    meta: { requiresAuth: true, keepAlive: true }
  },

  {
    path: '/budgets',
    name: 'budgets',
    component: Budgets,
    meta: { requiresAuth: true, keepAlive: true }
  },

  {
    path: '/savings-goals',
    name: 'savings-goals',
    component: SavingsGoals,
    meta: { requiresAuth: true, keepAlive: true }
  },

  {
    path: '/reports',
    name: 'reports',
    component: Reports,
    meta: { requiresAuth: true, keepAlive: true }
  },

  {
    path: "/recurring-transactions",
    name: 'recurring-transactions',
    component: RecurringTransactions,
    meta: { requiresAuth: true, keepAlive: false }
  },
  
  {
    path: '/profile',
    name: 'profile',
    component: Profile,
    meta: { requiresAuth: true, keepAlive: false }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {

  const token = localStorage.getItem('token')

  // Protected routes
  if (to.meta.requiresAuth && !token) {
    return next('/login')
  }

  // Guest-only routes
  if (to.meta.guest && token) {
    return next('/dashboard')
  }

  next()
})

export default router

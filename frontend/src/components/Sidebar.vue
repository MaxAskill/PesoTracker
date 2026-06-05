<template>
  <!-- Mobile Topbar -->
  <header class="fixed left-0 right-0 top-0 z-40 flex items-center justify-between border-b border-slate-800 bg-slate-950/95 px-4 py-4 backdrop-blur lg:hidden">
    <div class="flex items-center gap-3">
      <AppLogo subtitle="Smart Expense Tracker" />
    </div>

    <button
      type="button"
      class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-800 text-xl font-black text-white"
      aria-label="Open navigation"
      @click="openDrawer"
    >
      =
    </button>
  </header>

  <!-- Mobile Overlay -->
  <div
    v-if="isOpen"
    class="fixed inset-0 z-40 bg-slate-950/70 backdrop-blur-sm lg:hidden"
    @click="closeDrawer"
  ></div>

  <!-- Sidebar -->
  <aside
    :class="[
      'fixed left-0 top-0 z-50 flex h-screen w-[82vw] max-w-[320px] flex-col justify-between overflow-y-auto border-r border-slate-800 bg-slate-950/95 p-5 shadow-2xl shadow-slate-950/30 backdrop-blur transition-transform duration-300 lg:w-72 lg:max-w-none',
      isOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
    ]"
  >
    <div class="min-h-0">
      <!-- Header -->
      <div class="flex items-center justify-between gap-3">
        <AppLogo subtitle="Finance workspace" />

        <button
          type="button"
          class="h-10 w-10 rounded-xl bg-slate-800 text-slate-300 lg:hidden"
          aria-label="Close navigation"
          @click="closeDrawer"
        >
          X
        </button>
      </div>

      <!-- Navigation -->
      <nav class="mt-9 space-y-1.5">
        <router-link
          v-for="link in links"
          :key="link.path"
          :to="link.path"
          class="group flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-semibold transition"
          :class="
            route.path === link.path
              ? 'border border-emerald-400/30 bg-emerald-500/15 text-emerald-200 shadow-lg shadow-emerald-500/5'
              : 'border border-transparent text-slate-400 hover:border-slate-700 hover:bg-slate-900 hover:text-white'
          "
          @click="closeDrawer"
        >
          <span>{{ link.label }}</span>
          <span
            class="h-2 w-2 rounded-full transition"
            :class="route.path === link.path ? 'bg-emerald-400' : 'bg-transparent group-hover:bg-slate-600'"
          ></span>
        </router-link>
      </nav>
    </div>

    <!-- Logout -->
    <div class="border-t border-slate-800 pt-4">
      <button
        type="button"
        class="w-full rounded-2xl border border-slate-800 bg-slate-900 px-4 py-3 font-semibold text-slate-200 transition hover:border-red-500/40 hover:bg-red-500/10 hover:text-red-200"
        @click="logout"
      >
        Logout
      </button>
    </div>
  </aside>

  <div class="hidden w-72 shrink-0 lg:block"></div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppLogo from './AppLogo.vue'
import { useAuth } from '../composables/useAuth'

const route = useRoute()
const router = useRouter()
const { logout: logoutUser } = useAuth()

const isOpen = ref(false)

const links = [
  { label: 'Overview', path: '/dashboard' },
  { label: 'Profile', path: '/profile' },
  { label: 'Transactions', path: '/transactions' },
  { label: 'Budgets', path: '/budgets' },
  { label: 'Savings Goals', path: '/savings-goals' },
  { label: 'Reports', path: '/reports' },
  { label: 'Recurring', path: '/recurring-transactions' }
]

const emitDrawerState = () => {
  window.dispatchEvent(new CustomEvent('pesotracker-sidebar-state', {
    detail: { isOpen: isOpen.value }
  }))
}

const openDrawer = () => {
  isOpen.value = true
}

const closeDrawer = () => {
  isOpen.value = false
}

const logout = () => {
  closeDrawer()
  logoutUser(router)
}

const handleEscape = (event) => {
  if (event.key === 'Escape') {
    closeDrawer()
  }
}

const handleExternalClose = () => {
  closeDrawer()
}

watch(isOpen, (value) => {
  document.body.classList.toggle('overflow-hidden', value)
  emitDrawerState()
})

onMounted(() => {
  window.addEventListener('keydown', handleEscape)
  window.addEventListener('pesotracker-close-sidebar', handleExternalClose)
})

onBeforeUnmount(() => {
  document.body.classList.remove('overflow-hidden')
  window.removeEventListener('keydown', handleEscape)
  window.removeEventListener('pesotracker-close-sidebar', handleExternalClose)
})
</script>

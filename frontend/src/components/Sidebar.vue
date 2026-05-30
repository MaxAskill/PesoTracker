<template>
    <!-- Mobile Topbar -->
    <header class="lg:hidden fixed top-0 left-0 right-0 z-50 bg-slate-950/95 border-b border-slate-800 px-4 py-4 flex items-center justify-between backdrop-blur">
      <div class="flex items-center gap-3">
        <img
          src="/logo.png"
          class="w-12 h-12 rounded-2xl"
        />
      
        <div>
          <h1 class="text-2xl font-bold text-white">
            Peso<span class="text-emerald-400">Tracker</span>
          </h1>
      
          <p class="text-slate-400 text-sm">
            Smart Expense Tracker
          </p>
        </div>
      </div>

      <button
        @click="isOpen = true"
        class="w-11 h-11 rounded-xl bg-slate-800 text-white flex items-center justify-center"
      >
        ☰
      </button>
    </header>

    <!-- Mobile Overlay -->
    <div
      v-if="isOpen"
      @click="isOpen = false"
      class="lg:hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-40"
    ></div>

    <!-- Sidebar -->
    <aside
      :class="[
        'fixed left-0 top-0 z-50 h-screen w-72 bg-slate-950/95 border-r border-slate-800 p-5 flex flex-col justify-between shadow-2xl shadow-slate-950/30 backdrop-blur transition-transform duration-300',
        isOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
      ]"
    >
      <div class="min-h-0">
        <!-- Header -->
        <div class="flex items-center justify-between gap-3">
          <div class="flex items-center gap-3">
            <img
              src="/logo.png"
              class="h-11 w-11 rounded-2xl border border-emerald-400/20"
            />

            <div>
              <h1 class="text-xl font-bold text-white">
                Peso<span class="text-emerald-400">Tracker</span>
              </h1>
              <p class="text-xs text-slate-500">Finance workspace</p>
            </div>
          </div>

          <button
            @click="isOpen = false"
            class="lg:hidden w-10 h-10 rounded-xl bg-slate-800 text-slate-300"
          >
            ✕
          </button>
        </div>

        <!-- Navigation -->
        <nav class="mt-9 space-y-1.5">
          <router-link
            v-for="link in links"
            :key="link.path"
            :to="link.path"
            @click="isOpen = false"
            class="group flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-semibold transition"
            :class="
              route.path === link.path
                ? 'border border-emerald-400/30 bg-emerald-500/15 text-emerald-200 shadow-lg shadow-emerald-500/5'
                : 'border border-transparent text-slate-400 hover:border-slate-700 hover:bg-slate-900 hover:text-white'
            "
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
          @click="logout"
          class="w-full rounded-2xl border border-slate-800 bg-slate-900 px-4 py-3 font-semibold text-slate-200 transition hover:border-red-500/40 hover:bg-red-500/10 hover:text-red-200"
        >
          Logout
        </button>
      </div>
    </aside>

    <div class="hidden w-72 shrink-0 lg:block"></div>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

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

const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')

  router.push('/login')
}
</script>

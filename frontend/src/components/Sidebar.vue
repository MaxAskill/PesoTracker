<template>
    <!-- Mobile Topbar -->
    <header class="lg:hidden fixed top-0 left-0 right-0 z-50 bg-slate-900 border-b border-slate-800 px-4 py-4 flex items-center justify-between">
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
        'fixed lg:static top-0 left-0 z-50 h-screen w-72 bg-slate-900 border-r border-slate-800 p-6 flex flex-col justify-between transition-transform duration-300',
        isOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
      ]"
    >
      <div>
        <!-- Header -->
        <div class="flex items-center justify-between">
          <h1 class="text-2xl font-bold text-white">
            Peso<span class="text-emerald-400">Tracker</span>
          </h1>

          <button
            @click="isOpen = false"
            class="lg:hidden w-10 h-10 rounded-xl bg-slate-800 text-slate-300"
          >
            ✕
          </button>
        </div>

        <!-- Navigation -->
        <nav class="mt-10 space-y-2">
          <router-link
            v-for="link in links"
            :key="link.path"
            :to="link.path"
            @click="isOpen = false"
            class="block px-4 py-3 rounded-2xl font-semibold transition"
            :class="
              route.path === link.path
                ? 'bg-emerald-500 text-white'
                : 'text-slate-400 hover:bg-slate-800'
            "
          >
            {{ link.label }}
          </router-link>
        </nav>
      </div>

      <!-- Logout -->
      <button
        @click="logout"
        class="bg-slate-800 hover:bg-red-500 text-white px-4 py-3 rounded-2xl font-semibold transition"
      >
        Logout
      </button>
    </aside>
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
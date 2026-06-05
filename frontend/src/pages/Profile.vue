<template>
  <main class="magic-bg min-h-screen text-white flex">
    <Sidebar />

    <section class="min-w-0 flex-1 p-4 pt-24 sm:p-6 lg:pt-6">
      <div class="mb-8 rounded-[2rem] border border-white/10 bg-slate-950/55 p-6 shadow-2xl shadow-slate-950/30 backdrop-blur">
        <p class="text-sm font-semibold uppercase tracking-wide text-emerald-300">Account Center</p>
        <h2 class="mt-2 text-3xl font-black md:text-4xl">Profile</h2>
        <p class="mt-2 max-w-2xl text-sm text-slate-400">
          Manage your personal information and account settings.
        </p>
      </div>

      <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="rounded-[2rem] border border-white/10 bg-slate-950/70 p-6 shadow-2xl shadow-slate-950/20 xl:col-span-2">
          <p class="text-sm font-semibold uppercase tracking-wide text-emerald-300">Personal Information</p>
          <h3 class="mb-6 mt-1 text-2xl font-black">Account Information</h3>

          <div class="space-y-5">
            <div>
              <label class="mb-2 block text-sm text-slate-400">First Name</label>
              <input
                v-model="user.first_name"
                disabled
                class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-slate-300"
              />
            </div>

            <div>
              <label class="mb-2 block text-sm text-slate-400">Last Name</label>
              <input
                v-model="user.last_name"
                disabled
                class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-slate-300"
              />
            </div>

            <div>
              <label class="mb-2 block text-sm text-slate-400">Email</label>
              <input
                v-model="user.email"
                disabled
                class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-slate-300"
              />
            </div>
          </div>
        </div>

        <div class="relative overflow-hidden rounded-[2rem] border border-emerald-400/15 bg-gradient-to-br from-slate-950/80 via-slate-950/70 to-emerald-950/20 p-6 shadow-2xl shadow-slate-950/20">
          <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-emerald-400/10 blur-2xl"></div>
          <div class="relative mb-6 flex h-20 w-20 items-center justify-center rounded-3xl bg-emerald-500 text-4xl font-black text-slate-950">
            P
          </div>

          <h3 class="relative text-2xl font-black">PesoTracker</h3>

          <p class="relative mt-3 leading-relaxed text-slate-400">
            Your smart finance tracker with budgets, savings goals, OCR receipts,
            analytics, notifications, and assistant-powered insights.
          </p>

          <div class="relative mt-6 rounded-3xl border border-white/10 bg-slate-950/70 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Account Status</p>
            <p class="mt-1 font-bold text-emerald-300">Active</p>
          </div>

          <button
            @click="logout"
            class="relative mt-8 w-full rounded-2xl border border-red-500/30 bg-red-500/10 py-3 font-bold text-red-200 transition hover:bg-red-500 hover:text-white"
          >
            Logout
          </button>
        </div>
      </div>
    </section>
  </main>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import Sidebar from '../components/Sidebar.vue'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const { logout: logoutUser } = useAuth()

const user = ref(JSON.parse(localStorage.getItem('user')) || {})

const logout = () => {
  logoutUser(router)
}
</script>

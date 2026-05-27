<template>
  <main class="min-h-screen bg-slate-950 text-white flex">
    <Sidebar />

    <section class="flex-1 p-6 pt-24 lg:pt-6">
      <div class="mb-8">
        <p class="text-slate-400">Manage your</p>
        <h2 class="text-3xl font-bold">Profile & Settings</h2>
      </div>

      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 bg-slate-900 border border-slate-800 rounded-3xl p-6">
          <h3 class="text-xl font-bold mb-6">Account Information</h3>

          <div class="space-y-5">
            <div>
              <label class="block text-sm text-slate-400 mb-2">First Name</label>
              <input
                v-model="user.first_name"
                disabled
                class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-slate-300"
              />
            </div>

            <div>
              <label class="block text-sm text-slate-400 mb-2">Last Name</label>
              <input
                v-model="user.last_name"
                disabled
                class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-slate-300"
              />
            </div>

            <div>
              <label class="block text-sm text-slate-400 mb-2">Email</label>
              <input
                v-model="user.email"
                disabled
                class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-slate-300"
              />
            </div>
          </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6">
          <div class="w-20 h-20 bg-emerald-500 rounded-3xl flex items-center justify-center text-4xl font-black mb-6">
            ₱
          </div>

          <h3 class="text-2xl font-bold">
            PesoTracker
          </h3>

          <p class="text-slate-400 mt-3 leading-relaxed">
            Your smart finance tracker with budgets, savings goals, OCR receipts,
            analytics, notifications, and AI-style insights.
          </p>

          <button
            @click="logout"
            class="w-full mt-8 bg-red-500 hover:bg-red-600 text-white py-3 rounded-2xl font-bold transition"
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

const router = useRouter()

const user = ref(JSON.parse(localStorage.getItem('user')) || {})

const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  router.push('/login')
}
</script>
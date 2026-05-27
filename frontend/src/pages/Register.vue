<template>
  <main class="min-h-screen bg-slate-950 text-white flex items-center justify-center px-6 py-10">
    <section class="w-full max-w-6xl grid grid-cols-1 lg:grid-cols-2 bg-slate-900 rounded-3xl shadow-2xl overflow-hidden border border-slate-800">
      <!-- Branding Panel -->
      <div class="hidden lg:flex flex-col justify-between bg-slate-950 p-10 relative overflow-hidden">
        <div class="absolute -top-24 -right-24 w-72 h-72 bg-emerald-500/20 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-72 h-72 bg-emerald-400/10 rounded-full blur-3xl"></div>

        <div class="relative z-10">
          <div class="flex items-center gap-3">
            <div class="w-14 h-14 bg-emerald-500 rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-500/20">
              <span class="text-3xl font-black text-white">₱</span>
            </div>

            <div>
              <h1 class="text-3xl font-bold">Peso<span class="text-emerald-400">Tracker</span></h1>
              <p class="text-slate-400 text-sm">Smart Expense Tracker</p>
            </div>
          </div>

          <div class="mt-20">
            <p class="text-emerald-400 font-semibold mb-3">Start tracking today.</p>
            <h2 class="text-5xl font-bold leading-tight max-w-md">
              Build better money habits.
            </h2>
            <p class="text-slate-400 mt-6 max-w-md leading-relaxed">
              Create your PesoTracker account and begin monitoring your income, expenses, budgets, and savings goals.
            </p>
          </div>
        </div>

        <div class="relative z-10 grid grid-cols-3 gap-4">
          <div class="bg-slate-900 border border-slate-800 p-4 rounded-2xl">
            <p class="text-2xl font-bold">₱0</p>
            <p class="text-sm text-slate-400">Start</p>
          </div>
          <div class="bg-slate-900 border border-slate-800 p-4 rounded-2xl">
            <p class="text-2xl font-bold text-emerald-400">100%</p>
            <p class="text-sm text-slate-400">Control</p>
          </div>
          <div class="bg-slate-900 border border-slate-800 p-4 rounded-2xl">
            <p class="text-2xl font-bold text-amber-400">AI</p>
            <p class="text-sm text-slate-400">Insights</p>
          </div>
        </div>
      </div>

      <!-- Register Form -->
      <div class="p-8 sm:p-12 lg:p-16 flex items-center">
        <div class="w-full max-w-md mx-auto">
          <div class="lg:hidden flex items-center gap-3 mb-10">
            <div class="w-12 h-12 bg-emerald-500 rounded-2xl flex items-center justify-center">
              <span class="text-2xl font-black text-white">₱</span>
            </div>
            <div>
              <h1 class="text-2xl font-bold">Peso<span class="text-emerald-400">Tracker</span></h1>
              <p class="text-sm text-slate-400">Smart Expense Tracker</p>
            </div>
          </div>

          <div class="mb-8">
            <p class="text-emerald-400 font-semibold mb-2">Create account</p>
            <h2 class="text-4xl font-bold">Register now</h2>
            <p class="text-slate-400 mt-3">
              Enter your details to create your financial dashboard.
            </p>
          </div>

          <form class="space-y-5" @submit.prevent="handleRegister">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div>
                <label for="first_name" class="block text-sm font-semibold text-slate-300 mb-2">
                  First Name
                </label>
            
                <input
                  id="first_name"
                  v-model="form.first_name"
                  type="text"
                  placeholder="Juan"
                  class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition"
                />
              </div>
            
              <div>
                <label for="last_name" class="block text-sm font-semibold text-slate-300 mb-2">
                  Last Name
                </label>
            
                <input
                  id="last_name"
                  v-model="form.last_name"
                  type="text"
                  placeholder="Dela Cruz"
                  class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition"
                />
              </div>
            </div>

            <div>
              <label for="email" class="block text-sm font-semibold text-slate-300 mb-2">
                Email Address
              </label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                placeholder="example@email.com"
                class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition"
              />
            </div>

            <div>
              <label for="password" class="block text-sm font-semibold text-slate-300 mb-2">
                Password
              </label>
              <input
                id="password"
                v-model="form.password"
                type="password"
                placeholder="Create a password"
                class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition"
              />
            </div>

            <div>
              <label for="password_confirmation" class="block text-sm font-semibold text-slate-300 mb-2">
                Confirm Password
              </label>
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                placeholder="Confirm your password"
                class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition"
              />
            </div>

            <p v-if="error" class="text-sm text-red-400 font-medium">
              {{ error }}
            </p>

            <button
              type="submit"
              class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-emerald-500/20 transition"
            >
              Create Account
            </button>
          </form>

          <p class="text-center text-slate-400 mt-8">
            Already have an account?
            <router-link to="/login" class="text-emerald-400 font-bold hover:text-emerald-300">
              Login
            </router-link>
          </p>
        </div>
      </div>
    </section>
  </main>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()
const error = ref('')

const form = reactive({
  first_name: '',
  last_name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const handleRegister = async () => {
  error.value = ''

  if (form.password !== form.password_confirmation) {
    error.value = 'Passwords do not match.'
    return
  }

  try {
    const payload = {
      first_name: form.first_name,
      last_name: form.last_name,
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation
    }

    await api.post('/register', payload)

    localStorage.setItem('pending_email', form.email)

    router.push('/verify-otp')

  } catch (err) {
    error.value =
      err.response?.data?.message ||
      'Registration failed. Please try again.'
  }
}
</script>
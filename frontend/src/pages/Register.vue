<template>
  <main class="magic-bg min-h-screen px-6 py-6 text-white">
    <header class="mx-auto mb-6 flex max-w-7xl items-center justify-between">
      <RouterLink to="/" class="flex items-center gap-3">
        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-400 text-2xl font-black text-slate-950 shadow-lg shadow-emerald-500/25">
          ₱
        </div>
        <div>
          <h1 class="text-xl font-black">Peso<span class="text-emerald-300">Tracker</span></h1>
          <p class="text-xs text-slate-400">Finance Assistant</p>
        </div>
      </RouterLink>

      <RouterLink to="/" class="rounded-full bg-white/[0.06] px-4 py-2 text-sm font-semibold text-slate-200 transition hover:bg-emerald-400/10 hover:text-emerald-100">
        Back to home
      </RouterLink>
    </header>

    <div class="flex min-h-[calc(100vh-112px)] items-center justify-center">
    <section class="grid w-full max-w-6xl overflow-hidden rounded-[2rem] border border-white/10 bg-slate-950/50 shadow-2xl shadow-emerald-950/40 backdrop-blur-xl lg:grid-cols-2">
      <div class="relative hidden min-h-[760px] flex-col justify-between overflow-hidden border-r border-white/10 p-10 lg:flex">
        <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-emerald-400/20 blur-3xl"></div>
        <div class="absolute -bottom-16 left-10 h-72 w-72 rounded-full bg-lime-300/10 blur-3xl"></div>

        <div class="relative z-10">
          <RouterLink to="/" class="flex items-center gap-3">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-400 text-3xl font-black text-slate-950 shadow-lg shadow-emerald-500/25">
              ₱
            </div>
            <div>
              <h1 class="text-3xl font-black">Peso<span class="text-emerald-300">Tracker</span></h1>
              <p class="text-sm text-slate-400">Smart Expense Tracker</p>
            </div>
          </RouterLink>

          <div class="mt-20">
            <p class="mb-3 font-semibold text-emerald-300">Start tracking today.</p>
            <h2 class="max-w-md text-5xl font-black leading-tight">
              Build better money habits with a little magic.
            </h2>
            <p class="mt-6 max-w-md leading-7 text-slate-300">
              Create your PesoTracker account and start monitoring income, expenses, budgets, savings, and receipts.
            </p>
          </div>
        </div>

        <div class="relative z-10 grid grid-cols-3 gap-4">
          <div class="glass-card p-4">
            <p class="text-2xl font-black">₱0.00</p>
            <p class="text-sm text-slate-400">Start</p>
          </div>
          <div class="glass-card p-4">
            <p class="text-2xl font-black text-emerald-300">100%</p>
            <p class="text-sm text-slate-400">Control</p>
          </div>
          <div class="glass-card p-4">
            <p class="text-2xl font-black text-lime-300">AI</p>
            <p class="text-sm text-slate-400">Insights</p>
          </div>
        </div>
      </div>

      <div class="flex items-center p-8 sm:p-12 lg:p-16">
        <div class="mx-auto w-full max-w-md">
          <div class="mb-8">
            <p class="mb-2 font-semibold text-emerald-300">Create account</p>
            <h2 class="text-4xl font-black">Register now</h2>
            <p class="mt-3 text-slate-400">Enter your details to create your financial dashboard.</p>
          </div>

          <form class="space-y-5" @submit.prevent="handleRegister">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
              <div>
                <label for="first_name" class="mb-2 block text-sm font-semibold text-slate-300">First Name</label>
                <input
                  id="first_name"
                  v-model="form.first_name"
                  type="text"
                  placeholder="Juan"
                  class="w-full rounded-2xl border border-white/10 bg-white/[0.06] px-4 py-3 text-white outline-none transition placeholder:text-slate-500 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-400/10"
                />
              </div>

              <div>
                <label for="last_name" class="mb-2 block text-sm font-semibold text-slate-300">Last Name</label>
                <input
                  id="last_name"
                  v-model="form.last_name"
                  type="text"
                  placeholder="Dela Cruz"
                  class="w-full rounded-2xl border border-white/10 bg-white/[0.06] px-4 py-3 text-white outline-none transition placeholder:text-slate-500 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-400/10"
                />
              </div>
            </div>

            <div>
              <label for="email" class="mb-2 block text-sm font-semibold text-slate-300">Email Address</label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                placeholder="example@email.com"
                class="w-full rounded-2xl border border-white/10 bg-white/[0.06] px-4 py-3 text-white outline-none transition placeholder:text-slate-500 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-400/10"
              />
            </div>

            <div>
              <label for="password" class="mb-2 block text-sm font-semibold text-slate-300">Password</label>
              <input
                id="password"
                v-model="form.password"
                type="password"
                placeholder="Create a password"
                class="w-full rounded-2xl border border-white/10 bg-white/[0.06] px-4 py-3 text-white outline-none transition placeholder:text-slate-500 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-400/10"
              />
            </div>

            <div>
              <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-300">Confirm Password</label>
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                placeholder="Confirm your password"
                class="w-full rounded-2xl border border-white/10 bg-white/[0.06] px-4 py-3 text-white outline-none transition placeholder:text-slate-500 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-400/10"
              />
            </div>

            <p v-if="error" class="text-sm font-medium text-red-400">{{ error }}</p>

            <button
              type="submit"
              :disabled="loading"
              class="magic-button inline-flex w-full items-center justify-center gap-2 disabled:cursor-not-allowed disabled:opacity-70"
            >
              <span
                v-if="loading"
                class="h-4 w-4 animate-spin rounded-full border-2 border-slate-950/30 border-t-slate-950"
              ></span>
              {{ loading ? 'Creating account...' : 'Create Account' }}
            </button>
          </form>

          <p class="mt-8 text-center text-slate-400">
            Already have an account?
            <RouterLink to="/login" class="font-bold text-emerald-300 hover:text-emerald-200">
              Login
            </RouterLink>
          </p>
        </div>
      </div>
    </section>
    </div>
  </main>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()
const error = ref('')
const loading = ref(false)

const form = reactive({
  first_name: '',
  last_name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const handleRegister = async () => {
  if (loading.value) return

  error.value = ''

  if (form.password !== form.password_confirmation) {
    error.value = 'Passwords do not match.'
    return
  }

  loading.value = true

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
  } finally {
    loading.value = false
  }
}
</script>

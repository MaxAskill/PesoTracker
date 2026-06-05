<template>
  <main class="magic-bg min-h-screen px-6 pb-6 pt-24 text-white">
    <AuthHeader />

    <div class="flex min-h-[calc(100vh-112px)] items-center justify-center">
      <section class="motion-scale-in grid w-full max-w-6xl overflow-hidden rounded-[2rem] border border-white/10 bg-slate-950/70 shadow-2xl shadow-emerald-950/40 backdrop-blur-xl lg:grid-cols-2">
        <div class="motion-slide-left relative hidden min-h-[760px] flex-col justify-between overflow-hidden border-r border-white/10 p-10 lg:flex">
          <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-emerald-400/20 blur-3xl"></div>
          <div class="absolute -bottom-16 left-10 h-72 w-72 rounded-full bg-lime-300/10 blur-3xl"></div>

          <div class="relative z-10">
            <RouterLink to="/" class="flex items-center gap-3">
              <AppLogo size="lg" subtitle="Smart Expense Tracker" />
            </RouterLink>

            <div class="mt-20">
              <p class="mb-3 font-semibold text-emerald-300">Start tracking today.</p>
              <h2 class="max-w-md text-5xl font-black leading-tight">
                Build better money habits.
              </h2>
              <p class="mt-6 max-w-md leading-7 text-slate-300">
                Track income, expenses, budgets, savings goals, receipts, and insights from one dashboard.
              </p>
            </div>
          </div>

          <div class="relative z-10 grid grid-cols-3 gap-4">
            <div class="pt-stat">
              <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Start</p>
              <p class="mt-2 text-2xl font-black">PHP 0</p>
            </div>
            <div class="pt-stat">
              <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Control</p>
              <p class="mt-2 text-2xl font-black text-emerald-300">100%</p>
              <div class="mt-3 h-2 rounded-full bg-slate-800">
                <div class="h-2 w-full rounded-full bg-emerald-400"></div>
              </div>
            </div>
            <div class="pt-stat">
              <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Insight</p>
              <p class="mt-2 text-2xl font-black text-emerald-300">AI</p>
            </div>
          </div>
        </div>

        <div class="motion-fade-up motion-delay-2 flex items-center p-8 sm:p-12 lg:p-16">
          <div class="mx-auto w-full max-w-md">
            <div class="mb-8">
              <p class="mb-2 font-semibold text-emerald-300">Create account</p>
              <h2 class="text-4xl font-black">Register now</h2>
              <p class="mt-3 text-slate-400">Start building better money habits.</p>
              <p class="mt-2 text-sm leading-6 text-slate-500">
                Track income, expenses, budgets, savings goals, receipts, and insights.
              </p>
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
                    class="pt-input"
                  />
                </div>

                <div>
                  <label for="last_name" class="mb-2 block text-sm font-semibold text-slate-300">Last Name</label>
                  <input
                    id="last_name"
                    v-model="form.last_name"
                    type="text"
                    placeholder="Dela Cruz"
                    class="pt-input"
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
                  class="pt-input"
                />
              </div>

              <div>
                <label for="password" class="mb-2 block text-sm font-semibold text-slate-300">Password</label>
                <input
                  id="password"
                  v-model="form.password"
                  type="password"
                  placeholder="Create a password"
                  class="pt-input"
                />
              </div>

              <div>
                <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-300">Confirm Password</label>
                <input
                  id="password_confirmation"
                  v-model="form.password_confirmation"
                  type="password"
                  placeholder="Confirm your password"
                  class="pt-input"
                />
              </div>

              <p v-if="error" class="text-sm font-medium text-red-400">{{ error }}</p>

              <button
                type="submit"
                :disabled="loading"
                class="pt-primary inline-flex w-full items-center justify-center gap-2 disabled:cursor-not-allowed disabled:opacity-70"
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
import AuthHeader from '../components/AuthHeader.vue'
import AppLogo from '../components/AppLogo.vue'

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
    const normalizedEmail = form.email.trim().toLowerCase()

    const payload = {
      first_name: form.first_name,
      last_name: form.last_name,
      email: normalizedEmail,
      password: form.password,
      password_confirmation: form.password_confirmation
    }

    await api.post('/register', payload)

    localStorage.setItem('pending_email', normalizedEmail)

    router.push('/verify-otp')

  } catch (err) {
    if (err.response?.status === 429) {
      error.value = 'Too many attempts. Please wait before trying again.'
      return
    }

    error.value =
      err.response?.data?.message ||
      'Registration failed. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>

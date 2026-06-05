<template>
  <main class="magic-bg min-h-screen px-6 pb-6 pt-24 text-white">
    <AuthHeader />

    <div class="flex min-h-[calc(100vh-112px)] items-center justify-center">
      <section class="motion-scale-in grid w-full max-w-6xl overflow-hidden rounded-[2rem] border border-white/10 bg-slate-950/70 shadow-2xl shadow-emerald-950/40 backdrop-blur-xl lg:grid-cols-2">
        <div class="motion-slide-left relative hidden min-h-[680px] flex-col justify-between overflow-hidden border-r border-white/10 p-10 lg:flex">
          <div class="absolute -right-16 top-10 h-72 w-72 rounded-full bg-emerald-400/20 blur-3xl"></div>
          <div class="absolute bottom-20 left-8 h-56 w-56 rounded-full bg-lime-300/10 blur-3xl"></div>

          <div class="relative z-10">
            <RouterLink to="/" class="flex items-center gap-3">
              <AppLogo size="lg" subtitle="Smart Expense Tracker" />
            </RouterLink>

            <div class="mt-20">
              <p class="mb-3 font-semibold text-emerald-300">Track smarter. Save better.</p>
              <h2 class="max-w-md text-5xl font-black leading-tight">
                Your finance workspace is ready.
              </h2>
              <p class="mt-6 max-w-md leading-7 text-slate-300">
                Review expenses, budgets, savings, receipts, and AI insights in one focused dashboard.
              </p>
            </div>
          </div>

          <div class="relative z-10 grid grid-cols-3 gap-4">
            <div class="pt-stat">
              <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Balance</p>
              <p class="mt-2 text-2xl font-black">PHP 28K</p>
            </div>
            <div class="pt-stat">
              <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Goal</p>
              <p class="mt-2 text-2xl font-black text-emerald-300">62%</p>
              <div class="mt-3 h-2 rounded-full bg-slate-800">
                <div class="h-2 w-[62%] rounded-full bg-emerald-400"></div>
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
              <p class="mb-2 font-semibold text-emerald-300">Welcome back</p>
              <h2 class="text-4xl font-black">Login to your account</h2>
              <p class="mt-3 text-slate-400">Login to your financial workspace.</p>
              <p class="mt-2 text-sm leading-6 text-slate-500">
                Review expenses, budgets, savings, receipts, and AI insights in one focused dashboard.
              </p>
              <p v-if="successMessage" class="mt-4 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm font-semibold text-emerald-200">
                {{ successMessage }}
              </p>
            </div>

            <form class="space-y-5" @submit.prevent="handleLogin">
              <div>
                <label for="email" class="mb-2 block text-sm font-semibold text-slate-300">Email Address</label>
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  placeholder="example@email.com"
                  tabindex="1"
                  class="pt-input"
                />
              </div>

              <div>
                <div class="mb-2 flex items-center justify-between">
                  <label for="password" class="block text-sm font-semibold text-slate-300">Password</label>
                  <span class="text-sm font-semibold text-slate-600">Secure login</span>
                </div>
                <input
                  id="password"
                  v-model="form.password"
                  type="password"
                  placeholder="Enter your password"
                  tabindex="2"
                  class="pt-input"
                />
              </div>

              <p v-if="error" class="text-sm font-medium text-red-400">{{ error }}</p>

              <button
                type="submit"
                :disabled="loading"
                tabindex="3"
                class="pt-primary inline-flex w-full items-center justify-center gap-2 focus:outline-none focus:ring-4 focus:ring-emerald-400/30 disabled:cursor-not-allowed disabled:opacity-70"
              >
                <span
                  v-if="loading"
                  class="h-4 w-4 animate-spin rounded-full border-2 border-slate-950/30 border-t-slate-950"
                ></span>
                {{ loading ? 'Logging in...' : 'Login' }}
              </button>
            </form>

            <p class="mt-8 text-center text-slate-400">
              Don't have an account?
              <RouterLink to="/register" tabindex="4" class="rounded-lg font-bold text-emerald-300 outline-none transition hover:text-emerald-200 focus:ring-4 focus:ring-emerald-400/20">
                Create account
              </RouterLink>
            </p>

            <p class="mt-4 text-center">
              <RouterLink
                to="/forgot-password"
                tabindex="5"
                class="rounded-lg text-sm font-semibold text-slate-500 outline-none transition hover:text-emerald-200 focus:ring-4 focus:ring-emerald-400/20"
              >
                Forgot password?
              </RouterLink>
            </p>
          </div>
        </div>
      </section>
    </div>
  </main>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import AuthHeader from '../components/AuthHeader.vue'
import AppLogo from '../components/AppLogo.vue'
import { preloadAuthenticatedData } from '../services/preload'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const { setAuthState } = useAuth()
const error = ref('')
const successMessage = ref('')
const loading = ref(false)

const form = reactive({
  email: '',
  password: ''
})

onMounted(() => {
  successMessage.value = localStorage.getItem('login_success_message') || ''
  localStorage.removeItem('login_success_message')
})

const handleLogin = async () => {
  if (loading.value) return

  error.value = ''
  loading.value = true

  try {
    const response = await api.post('/login', form)

    setAuthState(response.data.token, response.data.user)

    preloadAuthenticatedData()
    router.push('/dashboard')
  } catch (err) {
    if (err.response?.data?.code === 'email_not_verified') {
      localStorage.setItem('pending_email', err.response.data.email || form.email)
      router.push('/verify-otp')
      return
    }

    error.value =
      err.response?.data?.message ||
      'Invalid email or password.'
  } finally {
    loading.value = false
  }
}
</script>

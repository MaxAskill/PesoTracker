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
            <p class="text-emerald-400 font-semibold mb-3">Track smarter. Save better.</p>
            <h2 class="text-5xl font-bold leading-tight max-w-md">
              Manage your money with confidence.
            </h2>
            <p class="text-slate-400 mt-6 max-w-md leading-relaxed">
              Monitor your income, expenses, budgets, and savings goals in one modern dashboard.
            </p>
          </div>
        </div>

        <div class="relative z-10 grid grid-cols-3 gap-4">
          <div class="bg-slate-900 border border-slate-800 p-4 rounded-2xl">
            <p class="text-2xl font-bold">₱28k</p>
            <p class="text-sm text-slate-400">Balance</p>
          </div>
          <div class="bg-slate-900 border border-slate-800 p-4 rounded-2xl">
            <p class="text-2xl font-bold text-emerald-400">62%</p>
            <p class="text-sm text-slate-400">Goal</p>
          </div>
          <div class="bg-slate-900 border border-slate-800 p-4 rounded-2xl">
            <p class="text-2xl font-bold text-amber-400">AI</p>
            <p class="text-sm text-slate-400">Insights</p>
          </div>
        </div>
      </div>

      <!-- Login Form -->
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
            <p class="text-emerald-400 font-semibold mb-2">Welcome back</p>
            <h2 class="text-4xl font-bold">Login to your account</h2>
            <p class="text-slate-400 mt-3">
              Enter your details to access your financial dashboard.
            </p>
          </div>

          <form class="space-y-5" @submit.prevent="handleLogin">
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
              <div class="flex items-center justify-between mb-2">
                <label for="password" class="block text-sm font-semibold text-slate-300">
                  Password
                </label>
                <a href="#" class="text-sm text-emerald-400 font-semibold hover:text-emerald-300">
                  Forgot password?
                </a>
              </div>

              <input
                id="password"
                v-model="form.password"
                type="password"
                placeholder="Enter your password"
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
              Login
            </button>
          </form>

          <p class="text-center text-slate-400 mt-8">
            Don’t have an account?
            <router-link to="/register" class="text-emerald-400 font-bold hover:text-emerald-300">
              Create account
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
  email: '',
  password: ''
})

const handleLogin = async () => {
  error.value = ''

  try {
    const response = await api.post('/login', form)

    localStorage.setItem('token', response.data.token)
    localStorage.setItem('user', JSON.stringify(response.data.user))

    router.push('/dashboard')
  } catch (err) {
    error.value = 'Invalid email or password.'
  }
}
</script>
<template>
  <main class="magic-bg min-h-screen px-6 pb-6 pt-24 text-white">
    <AuthHeader />

    <div class="flex min-h-[calc(100vh-112px)] items-center justify-center">
      <section class="w-full max-w-md rounded-[2rem] border border-white/10 bg-slate-950/75 p-8 shadow-2xl shadow-emerald-950/30 backdrop-blur-xl">
        <div class="mb-8">
          <p class="mb-2 font-semibold text-emerald-300">Password reset</p>
          <h1 class="text-4xl font-black">Forgot your password?</h1>
          <p class="mt-3 leading-6 text-slate-400">
            Enter your email and we'll send a reset code.
          </p>
        </div>

        <form class="space-y-5" @submit.prevent="handleSubmit">
          <div>
            <label for="email" class="mb-2 block text-sm font-semibold text-slate-300">Email Address</label>
            <input
              id="email"
              v-model="email"
              type="email"
              placeholder="example@email.com"
              class="pt-input"
            />
          </div>

          <p v-if="error" class="text-sm font-medium text-red-400">{{ error }}</p>
          <p v-if="success" class="text-sm font-medium text-emerald-300">{{ success }}</p>

          <button
            type="submit"
            :disabled="loading"
            class="pt-primary inline-flex w-full items-center justify-center gap-2 disabled:cursor-not-allowed disabled:opacity-70"
          >
            <span v-if="loading" class="h-4 w-4 animate-spin rounded-full border-2 border-slate-950/30 border-t-slate-950"></span>
            {{ loading ? 'Sending...' : 'Send Reset Code' }}
          </button>
        </form>

        <p class="mt-8 text-center text-slate-400">
          Remember your password?
          <RouterLink to="/login" class="font-bold text-emerald-300 hover:text-emerald-200">
            Back to login
          </RouterLink>
        </p>
      </section>
    </div>
  </main>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import AuthHeader from '../components/AuthHeader.vue'

const router = useRouter()
const email = ref('')
const error = ref('')
const success = ref('')
const loading = ref(false)

const handleSubmit = async () => {
  if (loading.value) return

  error.value = ''
  success.value = ''

  if (!email.value) {
    error.value = 'Please enter your email address.'
    return
  }

  loading.value = true

  try {
    const normalizedEmail = email.value.trim().toLowerCase()
    const response = await api.post('/forgot-password', {
      email: normalizedEmail
    })

    sessionStorage.setItem('reset_email', normalizedEmail)
    success.value = response.data.message || 'If the email exists, a password reset OTP has been sent.'
    router.push('/reset-password/verify-otp')
  } catch (err) {
    error.value = err.response?.data?.message || 'Unable to send reset code right now.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main class="magic-bg min-h-screen px-6 pb-6 pt-24 text-white">
    <AuthHeader />

    <div class="flex min-h-[calc(100vh-112px)] items-center justify-center">
      <section class="w-full max-w-md rounded-[2rem] border border-white/10 bg-slate-950/75 p-8 shadow-2xl shadow-emerald-950/30 backdrop-blur-xl">
        <div class="mb-8">
          <p class="mb-2 font-semibold text-emerald-300">New password</p>
          <h1 class="text-4xl font-black">Create new password</h1>
          <p class="mt-3 leading-6 text-slate-400">
            Choose a new password for your PesoTracker account.
          </p>
        </div>

        <form class="space-y-5" @submit.prevent="handleReset">
          <div>
            <label for="password" class="mb-2 block text-sm font-semibold text-slate-300">New Password</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              placeholder="Create a new password"
              class="pt-input"
            />
          </div>

          <div>
            <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-300">Confirm Password</label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              placeholder="Confirm your new password"
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
            {{ loading ? 'Resetting...' : 'Reset Password' }}
          </button>
        </form>
      </section>
    </div>
  </main>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import AuthHeader from '../components/AuthHeader.vue'

const router = useRouter()
const email = ref('')
const otp = ref('')
const loading = ref(false)
const error = ref('')
const success = ref('')

const form = reactive({
  password: '',
  password_confirmation: ''
})

onMounted(() => {
  email.value = sessionStorage.getItem('reset_email') || ''
  otp.value = sessionStorage.getItem('reset_otp') || ''
  const verified = sessionStorage.getItem('reset_verified') === 'true'

  if (!email.value || !otp.value || !verified) {
    router.push('/forgot-password')
  }
})

const handleReset = async () => {
  if (loading.value) return

  error.value = ''
  success.value = ''

  if (form.password.length < 6) {
    error.value = 'Password must be at least 6 characters.'
    return
  }

  if (form.password !== form.password_confirmation) {
    error.value = 'Password confirmation does not match.'
    return
  }

  loading.value = true

  try {
    const response = await api.post('/reset-password', {
      email: email.value,
      otp: otp.value,
      password: form.password,
      password_confirmation: form.password_confirmation
    })

    sessionStorage.removeItem('reset_email')
    sessionStorage.removeItem('reset_otp')
    sessionStorage.removeItem('reset_verified')
    localStorage.setItem('login_success_message', 'Password reset successful. Please login.')
    success.value = response.data.message || 'Password reset successful. Please login.'
    router.push('/login')
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to reset password.'
  } finally {
    loading.value = false
  }
}
</script>

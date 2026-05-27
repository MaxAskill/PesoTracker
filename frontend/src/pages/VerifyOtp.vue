<template>
  <main class="min-h-screen bg-slate-950 text-white flex items-center justify-center px-6 py-10">
    <section class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl p-8">
      <div class="text-center mb-8">
        <div class="w-16 h-16 bg-emerald-500 rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-emerald-500/20">
          <span class="text-3xl font-black text-white">₱</span>
        </div>

        <h1 class="text-3xl font-bold">
          Verify Your Email
        </h1>

        <p class="text-slate-400 mt-3">
          Enter the 6-digit OTP sent to your email.
        </p>

        <p class="text-emerald-400 text-sm mt-2">
          {{ email }}
        </p>
      </div>

      <form @submit.prevent="handleVerifyOtp" class="space-y-5">
        <div>
          <label for="otp" class="block text-sm font-semibold text-slate-300 mb-2">
            OTP Code
          </label>

          <input
            id="otp"
            v-model="otp"
            type="text"
            maxlength="6"
            placeholder="123456"
            class="w-full text-center tracking-[0.5em] px-4 py-4 rounded-xl bg-slate-950 border border-slate-800 text-white text-2xl font-bold placeholder:text-slate-600 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition"
          />
        </div>

        <p v-if="error" class="text-sm text-red-400 font-medium text-center">
          {{ error }}
        </p>

        <p v-if="success" class="text-sm text-emerald-400 font-medium text-center">
          {{ success }}
        </p>

        <button
          type="submit"
          class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-emerald-500/20 transition"
        >
          Verify OTP
        </button>
      </form>

      <p class="text-center text-slate-400 mt-8">
        Wrong email?
        <router-link to="/register" class="text-emerald-400 font-bold hover:text-emerald-300">
          Register again
        </router-link>
      </p>

      <button
        type="button"
        :disabled="resendLoading"
        @click="handleResendOtp"
        class="mt-4 w-full rounded-xl border border-emerald-500/30 bg-emerald-500/10 py-3 text-sm font-semibold text-emerald-300 transition hover:bg-emerald-500 hover:text-white disabled:cursor-not-allowed disabled:opacity-60"
      >
        {{ resendLoading ? 'Sending...' : 'Resend OTP' }}
      </button>
    </section>
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()

const email = ref('')
const otp = ref('')
const error = ref('')
const success = ref('')
const resendLoading = ref(false)

onMounted(() => {
  email.value = localStorage.getItem('pending_email')

  if (!email.value) {
    router.push('/register')
  }
})

const handleVerifyOtp = async () => {
  error.value = ''
  success.value = ''

  if (!otp.value || otp.value.length !== 6) {
    error.value = 'Please enter a valid 6-digit OTP.'
    return
  }

  try {
    const response = await api.post('/verify-otp', {
      email: email.value,
      otp: otp.value
    })

    localStorage.setItem('token', response.data.token)
    localStorage.setItem('user', JSON.stringify(response.data.user))
    localStorage.removeItem('pending_email')

    success.value = 'Email verified successfully.'

    router.push('/dashboard')
  } catch (err) {
    error.value =
      err.response?.data?.message ||
      'Invalid or expired OTP.'
  }
}

const handleResendOtp = async () => {
  error.value = ''
  success.value = ''

  if (!email.value) {
    error.value = 'Email is missing. Please register again.'
    return
  }

  resendLoading.value = true

  try {
    await api.post('/resend-otp', {
      email: email.value
    })

    success.value = 'A new OTP has been sent.'
  } catch (err) {
    error.value =
      err.response?.data?.message ||
      'Failed to resend OTP.'
  } finally {
    resendLoading.value = false
  }
}
</script>

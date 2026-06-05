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
          :disabled="verifyLoading"
          class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-500 py-3.5 font-bold text-white shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-600 disabled:cursor-not-allowed disabled:opacity-70"
        >
          <span
            v-if="verifyLoading"
            class="h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
          ></span>
          {{ verifyLoading ? 'Verifying...' : 'Verify OTP' }}
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
        :disabled="resendLoading || resendCooldown > 0"
        @click="handleResendOtp"
        class="mt-4 w-full rounded-xl border border-emerald-500/30 bg-emerald-500/10 py-3 text-sm font-semibold text-emerald-300 transition hover:bg-emerald-500 hover:text-white disabled:cursor-not-allowed disabled:opacity-60"
      >
        <span
          v-if="resendLoading"
          class="mr-2 inline-block h-4 w-4 animate-spin rounded-full border-2 border-emerald-300/30 border-t-emerald-300 align-[-2px]"
        ></span>
        {{ resendButtonText }}
      </button>
    </section>
  </main>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const { setAuthState } = useAuth()

const email = ref('')
const otp = ref('')
const error = ref('')
const success = ref('')
const verifyLoading = ref(false)
const resendLoading = ref(false)
const resendCooldown = ref(0)
let cooldownInterval = null

const resendButtonText = computed(() => {
  if (resendLoading.value) return 'Sending...'
  if (resendCooldown.value > 0) return `Resend OTP in ${resendCooldown.value}s`
  return 'Resend OTP'
})

onMounted(() => {
  email.value = (localStorage.getItem('pending_email') || '').trim().toLowerCase()

  if (!email.value) {
    router.push('/register')
  }
})

onBeforeUnmount(() => {
  stopResendCooldown()
})

const handleVerifyOtp = async () => {
  if (verifyLoading.value) return

  error.value = ''
  success.value = ''

  if (!otp.value || otp.value.length !== 6) {
    error.value = 'Please enter a valid 6-digit OTP.'
    return
  }

  verifyLoading.value = true

  try {
    const response = await api.post('/verify-otp', {
      email: email.value,
      otp: otp.value
    })

    setAuthState(response.data.token, response.data.user)
    localStorage.removeItem('pending_email')

    success.value = 'Email verified successfully.'

    router.push('/dashboard')
  } catch (err) {
    if (err.response?.status === 429) {
      error.value = 'Too many attempts. Please wait before trying again.'
      return
    }

    error.value =
      err.response?.data?.message ||
      'Invalid or expired OTP.'
  } finally {
    verifyLoading.value = false
  }
}

const handleResendOtp = async () => {
  if (resendLoading.value || resendCooldown.value > 0) return

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
    startResendCooldown()
  } catch (err) {
    if (err.response?.status === 429) {
      error.value = 'Too many attempts. Please wait before trying again.'
      startResendCooldown()
      return
    }

    error.value =
      err.response?.data?.message ||
      'Failed to resend OTP.'
  } finally {
    resendLoading.value = false
  }
}

const startResendCooldown = (seconds = 60) => {
  resendCooldown.value = seconds
  stopResendCooldown()

  cooldownInterval = setInterval(() => {
    resendCooldown.value = Math.max(resendCooldown.value - 1, 0)

    if (resendCooldown.value === 0) {
      stopResendCooldown()
    }
  }, 1000)
}

const stopResendCooldown = () => {
  if (cooldownInterval) {
    clearInterval(cooldownInterval)
    cooldownInterval = null
  }
}
</script>

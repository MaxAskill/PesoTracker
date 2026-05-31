<template>
  <main class="magic-bg min-h-screen px-6 pb-6 pt-24 text-white">
    <AuthHeader />

    <div class="flex min-h-[calc(100vh-112px)] items-center justify-center">
      <section class="motion-scale-in w-full max-w-md rounded-[2rem] border border-white/10 bg-slate-950/75 p-8 shadow-2xl shadow-emerald-950/30 backdrop-blur-xl">
        <div class="mb-8 text-center">
          <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-500 text-3xl font-black text-slate-950 shadow-lg shadow-emerald-500/20">
            P
          </div>
          <p class="mb-2 font-semibold text-emerald-300">Reset code</p>
          <h1 class="text-4xl font-black">Verify reset code</h1>
          <p class="mt-3 leading-6 text-slate-400">
            Enter the 6-digit code sent to your email.
          </p>
          <p class="mt-2 text-sm font-bold text-emerald-300">{{ email }}</p>
        </div>

        <form class="space-y-5" @submit.prevent="handleVerify">
          <div>
            <label for="otp" class="mb-2 block text-sm font-semibold text-slate-300">OTP Code</label>
            <input
              id="otp"
              v-model="otp"
              type="text"
              maxlength="6"
              inputmode="numeric"
              placeholder="123456"
              class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-4 text-center text-2xl font-bold tracking-[0.5em] text-white outline-none transition placeholder:text-slate-600 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"
            />
          </div>

          <p v-if="error" class="text-center text-sm font-medium text-red-400">{{ error }}</p>
          <p v-if="success" class="text-center text-sm font-medium text-emerald-300">{{ success }}</p>

          <button
            type="submit"
            :disabled="verifyLoading"
            class="pt-primary inline-flex w-full items-center justify-center gap-2 disabled:cursor-not-allowed disabled:opacity-70"
          >
            <span v-if="verifyLoading" class="h-4 w-4 animate-spin rounded-full border-2 border-slate-950/30 border-t-slate-950"></span>
            {{ verifyLoading ? 'Verifying...' : 'Verify Code' }}
          </button>
        </form>

        <button
          type="button"
          :disabled="resendLoading"
          class="mt-4 w-full rounded-xl border border-emerald-500/30 bg-emerald-500/10 py-3 text-sm font-semibold text-emerald-300 transition hover:bg-emerald-500 hover:text-slate-950 disabled:cursor-not-allowed disabled:opacity-60"
          @click="handleResend"
        >
          {{ resendLoading ? 'Sending...' : 'Resend code' }}
        </button>
      </section>
    </div>
  </main>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import AuthHeader from '../components/AuthHeader.vue'

const router = useRouter()
const email = ref('')
const otp = ref('')
const error = ref('')
const success = ref('')
const verifyLoading = ref(false)
const resendLoading = ref(false)

onMounted(() => {
  email.value = sessionStorage.getItem('reset_email') || ''

  if (!email.value) {
    router.push('/forgot-password')
  }
})

const handleVerify = async () => {
  if (verifyLoading.value) return

  error.value = ''
  success.value = ''

  if (!/^\d{6}$/.test(otp.value)) {
    error.value = 'Please enter a valid 6-digit OTP.'
    return
  }

  verifyLoading.value = true

  try {
    await api.post('/forgot-password/verify-otp', {
      email: email.value,
      otp: otp.value
    })

    sessionStorage.setItem('reset_otp', otp.value)
    sessionStorage.setItem('reset_verified', 'true')
    success.value = 'OTP verified. You can now reset your password.'
    router.push('/reset-password')
  } catch (err) {
    error.value = err.response?.data?.message || 'Invalid or expired OTP.'
  } finally {
    verifyLoading.value = false
  }
}

const handleResend = async () => {
  if (resendLoading.value) return

  error.value = ''
  success.value = ''
  resendLoading.value = true

  try {
    await api.post('/forgot-password', {
      email: email.value
    })

    otp.value = ''
    sessionStorage.removeItem('reset_otp')
    sessionStorage.removeItem('reset_verified')
    success.value = 'A new reset code has been sent.'
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to resend reset code.'
  } finally {
    resendLoading.value = false
  }
}
</script>

<template>
  <div class="fixed bottom-4 right-4 z-30 sm:bottom-5 sm:right-5 lg:bottom-6 lg:right-6">
    <button
      type="button"
      aria-label="Open PesoTracker Assistant"
      class="motion-pulse-glow group relative flex h-12 w-12 items-center justify-center overflow-visible rounded-full bg-emerald-500 text-slate-950 shadow-2xl shadow-emerald-500/30 transition-all duration-200 hover:-translate-y-1 hover:scale-105 hover:bg-emerald-400 sm:h-14 sm:w-auto sm:gap-3 sm:rounded-full sm:bg-gradient-to-r sm:from-emerald-400 sm:to-teal-300 sm:px-5"
      @click="toggleAssistant"
    >
      <span class="absolute inset-0 -z-10 rounded-full bg-emerald-400/30 blur-xl transition group-hover:bg-emerald-300/40"></span>
      <span class="absolute inset-0 rounded-full border border-white/30"></span>
      <span class="relative flex h-8 w-8 items-center justify-center rounded-full bg-slate-950/90 text-base font-black text-emerald-300 shadow-inner sm:h-9 sm:w-9 sm:text-lg">
        ✦
      </span>
      <span class="hidden whitespace-nowrap text-sm font-black sm:inline">
        AI Assistant
      </span>
      <span class="hidden items-center gap-1 text-xs font-bold text-slate-900 sm:flex">
        <span class="h-2 w-2 rounded-full bg-emerald-800 shadow-[0_0_10px_rgba(6,95,70,0.9)]"></span>
        Online
      </span>
    </button>
  </div>

  <Transition
    enter-active-class="transition duration-200 ease-out"
    enter-from-class="translate-y-4 opacity-0 scale-95"
    enter-to-class="translate-y-0 opacity-100 scale-100"
    leave-active-class="transition duration-150 ease-in"
    leave-from-class="translate-y-0 opacity-100 scale-100"
    leave-to-class="translate-y-4 opacity-0 scale-95"
  >
  <div
    v-if="isOpen"
    class="finance-panel fixed bottom-4 left-3 right-3 z-40 flex h-[min(620px,calc(100vh-2rem))] min-h-0 flex-col overflow-hidden sm:bottom-5 sm:left-auto sm:right-5 sm:w-[410px] lg:bottom-6 lg:right-6"
  >
    <div class="flex shrink-0 items-center justify-between border-b border-slate-800 p-5">
      <div>
        <div class="flex items-center gap-2 text-sm font-semibold text-emerald-400">
          <span class="h-2 w-2 rounded-full bg-emerald-400 shadow-[0_0_10px_rgba(52,211,153,0.8)]"></span>
          Online
        </div>
        <h3 class="font-bold text-white">PesoTracker Assistant</h3>
      </div>

      <button
        type="button"
        aria-label="Close PesoTracker Assistant"
        class="h-10 w-10 rounded-xl bg-slate-800 text-slate-300 transition hover:bg-slate-700"
        @click="isOpen = false"
      >
        X
      </button>
    </div>

    <div ref="chatBody" class="min-h-0 flex-1 space-y-4 overflow-y-auto p-5">
      <div class="finance-surface p-4">
        <p class="text-sm leading-relaxed text-slate-300">
          Hi, I'm your PesoTracker Assistant. I can help you understand your spending, budget, and savings.
        </p>
      </div>

      <div v-if="insightsLoading" class="finance-surface p-4 text-sm text-slate-400">
        Loading your latest insights...
      </div>

      <div
        v-else-if="insightsError"
        class="rounded-2xl border border-red-500/20 bg-red-500/10 p-4 text-sm text-red-200"
      >
        {{ insightsError }}
      </div>

      <div v-else class="space-y-3">
        <div class="grid grid-cols-3 gap-2">
          <div class="rounded-2xl border border-white/10 bg-slate-950/70 p-3">
            <p class="text-[11px] uppercase text-slate-500">Income</p>
            <p class="mt-1 min-h-5 text-sm font-bold text-emerald-300">
              {{ summaryLoading ? 'Loading...' : formatPeso(summary.income) }}
            </p>
          </div>

          <div class="rounded-2xl border border-white/10 bg-slate-950/70 p-3">
            <p class="text-[11px] uppercase text-slate-500">Expenses</p>
            <p class="mt-1 min-h-5 text-sm font-bold text-red-300">
              {{ summaryLoading ? 'Loading...' : formatPeso(summary.expenses) }}
            </p>
          </div>

          <div class="rounded-2xl border border-white/10 bg-slate-950/70 p-3">
            <p class="text-[11px] uppercase text-slate-500">Balance</p>
            <p class="mt-1 min-h-5 text-sm font-bold text-white">
              {{ summaryLoading ? 'Loading...' : formatPeso(summary.balance) }}
            </p>
          </div>
        </div>

        <div
          v-for="insight in insights"
          :key="`${insight.title}-${insight.message}`"
          class="finance-surface p-4"
        >
          <div class="flex gap-3">
            <div
              class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-xs font-black"
              :class="insightTone(insight.type)"
            >
              {{ insightInitial(insight.type) }}
            </div>

            <div>
              <h4 class="text-sm font-bold text-white">{{ insight.title }}</h4>
              <p class="mt-1 text-sm leading-relaxed text-slate-400">
                {{ insight.message }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="space-y-3">
        <div
          v-for="(message, index) in messages"
          :key="index"
          class="flex"
          :class="message.role === 'user' ? 'justify-end' : 'justify-start'"
        >
          <div
            class="h-auto max-w-[82%] whitespace-pre-wrap break-words rounded-2xl px-4 py-3 text-sm leading-relaxed overflow-visible [word-break:break-word]"
            :class="message.role === 'user' ? 'bg-emerald-500 text-slate-950' : 'bg-slate-800 text-slate-200'"
          >
            {{ message.text }}
          </div>
        </div>

        <div v-if="askLoading" class="text-sm text-slate-400">
          Assistant is thinking...
        </div>
      </div>

      <div v-if="questionChips.length" class="flex flex-wrap gap-2 pt-1">
        <button
          v-for="question in questionChips"
          :key="question"
          type="button"
          class="rounded-xl bg-slate-800 px-3 py-2 text-left text-xs font-semibold text-slate-300 transition hover:bg-emerald-500 hover:text-slate-950"
          :disabled="askLoading"
          @click="askQuestion(question)"
        >
          {{ question }}
        </button>
      </div>
    </div>

    <form class="flex shrink-0 gap-3 border-t border-slate-800 p-5" @submit.prevent="askQuestion(input)">
      <input
        v-model="input"
        type="text"
        placeholder="Ask about your finances..."
        class="min-w-0 flex-1 rounded-2xl border border-slate-800 bg-slate-950 px-4 py-3 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"
      />

      <button
        type="submit"
        class="rounded-2xl bg-emerald-500 px-5 text-sm font-bold text-slate-950 transition hover:bg-emerald-400 disabled:cursor-not-allowed disabled:opacity-60"
        :disabled="askLoading || !input.trim()"
      >
        Send
      </button>
    </form>
  </div>
  </Transition>
</template>

<script setup>
import { computed, nextTick, ref, watch } from 'vue'
import api, { isCanceledRequest } from '../services/api'
import { formatPeso } from '../utils/currency'
import { useAuth } from '../composables/useAuth'
import { assistantErrorMessage } from '../utils/apiErrors'

const { isAuthenticated } = useAuth()

const props = defineProps({
  income: {
    type: [Number, String],
    default: null
  },
  expenses: {
    type: [Number, String],
    default: null
  },
  balance: {
    type: [Number, String],
    default: null
  },
  loading: Boolean
})

const isOpen = ref(false)
const insightsLoading = ref(false)
const askLoading = ref(false)
const insightsError = ref('')
const input = ref('')
const messages = ref([])
const chatBody = ref(null)

const assistantData = ref({
  insights: [],
  suggested_questions: []
})

const toNumber = (value) => {
  if (value === null || value === undefined) return 0

  const number = Number(value)
  return Number.isFinite(number) ? number : 0
}

const hasSummaryValue = computed(() => {
  return props.income !== null ||
    props.expenses !== null ||
    props.balance !== null
})

const summaryLoading = computed(() => props.loading && !hasSummaryValue.value)

const summary = computed(() => ({
  income: toNumber(props.income),
  expenses: toNumber(props.expenses),
  balance: toNumber(props.balance)
}))
const insights = computed(() => assistantData.value.insights)
const suggestedQuestions = computed(() => assistantData.value.suggested_questions)
const questionChips = computed(() => {
  return suggestedQuestions.value.length
    ? suggestedQuestions.value
    : ['Spending summary', 'Budget warning', 'Savings progress', 'Smart tips']
})

const toggleAssistant = async () => {
  if (!isAuthenticated.value) return

  isOpen.value = !isOpen.value

  if (isOpen.value && !assistantData.value.insights.length) {
    await loadInsights()
  }
}

const loadInsights = async () => {
  if (!isAuthenticated.value) return

  insightsLoading.value = true
  insightsError.value = ''
  insightsLoading.value = false
}

if (import.meta.env.DEV) {
  watch(
    () => [props.loading, props.income, props.expenses, props.balance],
    ([loading, income, expenses, balance]) => {
      if (!loading && income === null && expenses === null && balance === null) {
        console.warn('SmartAssistantWidget did not receive dashboard summary props.')
      }
    },
    { immediate: true }
  )
}

const askQuestion = async (question) => {
  if (!isAuthenticated.value) return

  const text = question.trim()

  if (!text || askLoading.value) return

  messages.value.push({
    role: 'user',
    text
  })

  input.value = ''
  askLoading.value = true
  await scrollToBottom()

  try {
    const response = await api.post('/ai/assistant', {
      message: text
    })

    if (import.meta.env.DEV) {
      console.log('Smart Assistant API response:', response.data)
    }

    const assistantReply = response.data.message || response.data.reply || response.data.answer || ''

    messages.value.push({
      role: 'assistant',
      text: String(assistantReply)
    })
  } catch (error) {
    if (isCanceledRequest(error)) return
    messages.value.push({
      role: 'assistant',
      text: assistantErrorMessage(error)
    })
  } finally {
    askLoading.value = false
    await scrollToBottom()
  }
}

const scrollToBottom = async () => {
  await nextTick()

  if (chatBody.value) {
    chatBody.value.scrollTop = chatBody.value.scrollHeight
  }
}

const insightTone = (type) => {
  return {
    'bg-emerald-500/10 text-emerald-300': type === 'success',
    'bg-amber-500/10 text-amber-300': type === 'warning',
    'bg-red-500/10 text-red-300': type === 'danger',
    'bg-slate-800 text-slate-300': !['success', 'warning', 'danger'].includes(type)
  }
}

const insightInitial = (type) => {
  if (type === 'warning') return '!'
  if (type === 'success') return '+'
  return 'i'
}
</script>

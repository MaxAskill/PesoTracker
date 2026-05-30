<template>
  <div class="fixed bottom-6 right-6 z-50">
    <button
      type="button"
      aria-label="Open PesoTracker Assistant"
      class="flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-500 text-sm font-black text-slate-950 shadow-2xl shadow-emerald-500/30 transition hover:bg-emerald-400"
      @click="toggleAssistant"
    >
      AI
    </button>
  </div>

  <div
    v-if="isOpen"
    class="finance-panel fixed bottom-24 left-4 right-4 z-50 flex h-[min(640px,calc(100vh-8rem))] flex-col overflow-hidden sm:bottom-28 sm:left-auto sm:right-6 sm:w-[400px]"
  >
    <div class="flex items-center justify-between border-b border-slate-800 p-5">
      <div>
        <p class="text-sm font-semibold text-emerald-400">Smart Assistant</p>
        <h3 class="font-bold text-white">PesoTracker Assistant</h3>
      </div>

      <button
        type="button"
        aria-label="Close PesoTracker Assistant"
        class="h-10 w-10 rounded-xl bg-slate-800 text-slate-300 transition hover:bg-slate-700"
        @click="isOpen = false"
      >
        x
      </button>
    </div>

    <div ref="chatBody" class="flex-1 space-y-4 overflow-y-auto p-5">
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
            <p class="mt-1 text-sm font-bold text-emerald-300">
              {{ formatPeso(summary.total_income) }}
            </p>
          </div>

          <div class="rounded-2xl border border-white/10 bg-slate-950/70 p-3">
            <p class="text-[11px] uppercase text-slate-500">Expenses</p>
            <p class="mt-1 text-sm font-bold text-red-300">
              {{ formatPeso(summary.total_expenses) }}
            </p>
          </div>

          <div class="rounded-2xl border border-white/10 bg-slate-950/70 p-3">
            <p class="text-[11px] uppercase text-slate-500">Balance</p>
            <p class="mt-1 text-sm font-bold text-white">
              {{ formatPeso(summary.balance) }}
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
            class="max-w-[82%] rounded-2xl px-4 py-3 text-sm leading-relaxed"
            :class="message.role === 'user' ? 'bg-emerald-500 text-slate-950' : 'bg-slate-800 text-slate-200'"
          >
            {{ message.text }}
          </div>
        </div>

        <div v-if="askLoading" class="text-sm text-slate-400">
          Assistant is thinking...
        </div>
      </div>

      <div v-if="suggestedQuestions.length" class="flex flex-wrap gap-2 pt-1">
        <button
          v-for="question in suggestedQuestions"
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

    <form class="flex gap-3 border-t border-slate-800 p-5" @submit.prevent="askQuestion(input)">
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
</template>

<script setup>
import { computed, nextTick, ref } from 'vue'
import api from '../services/api'
import { formatPeso } from '../utils/currency'

const isOpen = ref(false)
const insightsLoading = ref(false)
const askLoading = ref(false)
const insightsError = ref('')
const input = ref('')
const messages = ref([])
const chatBody = ref(null)

const assistantData = ref({
  summary: {
    month: '',
    total_income: 0,
    total_expenses: 0,
    balance: 0
  },
  insights: [],
  suggested_questions: []
})

const summary = computed(() => assistantData.value.summary)
const insights = computed(() => assistantData.value.insights)
const suggestedQuestions = computed(() => assistantData.value.suggested_questions)

const toggleAssistant = async () => {
  isOpen.value = !isOpen.value

  if (isOpen.value && !assistantData.value.insights.length) {
    await loadInsights()
  }
}

const loadInsights = async () => {
  insightsLoading.value = true
  insightsError.value = ''

  try {
    const response = await api.get('/assistant/insights')
    assistantData.value = response.data
  } catch (error) {
    insightsError.value = 'I could not load your insights right now. Please try again in a moment.'
  } finally {
    insightsLoading.value = false
  }
}

const askQuestion = async (question) => {
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
    const response = await api.post('/assistant/ask', {
      message: text
    })

    messages.value.push({
      role: 'assistant',
      text: response.data.reply
    })
  } catch (error) {
    messages.value.push({
      role: 'assistant',
      text: 'Sorry, I could not answer that right now. Please try again.'
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

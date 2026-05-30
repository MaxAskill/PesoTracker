<template>
  <main class="flex min-h-screen bg-[#020617] text-white">
    <!-- Sidebar -->
    <Sidebar />

    <!-- Main Content -->
    <section class="magic-bg flex-1 overflow-y-auto p-6 pt-24 lg:pt-6">
      <!-- Top Bar -->
      <header class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5 mb-8">
        <div>
          <p class="text-slate-400">Welcome back,</p>
          <h2 class="text-3xl font-bold">{{ displayName }}</h2>
          <p v-if="isPrimingDashboard" class="mt-2 text-sm text-emerald-300">
            Waking up your dashboard...
          </p>
          <p v-else-if="isRefreshingDashboard" class="mt-2 text-sm text-slate-500">
            Refreshing latest data...
          </p>
        </div>

        <div class="flex w-full flex-col gap-3 xl:w-auto xl:items-end">
          <div class="flex items-center gap-3">
            <input
              type="text"
              placeholder="Search transaction..."
              class="min-w-0 flex-1 rounded-2xl border border-white/10 bg-slate-950/70 px-5 py-3 text-sm text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-400 xl:w-80"
            />

            <div class="relative">
              <button
                @click="showNotifications = !showNotifications"
                class="relative h-12 w-12 rounded-2xl border border-white/10 bg-slate-950/70 transition hover:border-emerald-400/40"
              >
                🔔
            
                <span
                  v-if="unreadCount > 0"
                  class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-6 h-6 rounded-full flex items-center justify-center font-bold"
                >
                  {{ unreadCount }}
                </span>
              </button>
            
              <div
                v-if="showNotifications"
                class="finance-panel absolute right-0 z-50 mt-3 w-96 max-w-[90vw] overflow-hidden"
              >
                <div class="p-5 border-b border-slate-800 flex items-center justify-between">
                  <h3 class="font-bold text-white">Notifications</h3>
            
                  <button
                    @click="markAllNotificationsRead"
                    class="text-emerald-400 text-sm font-semibold"
                  >
                    Mark all read
                  </button>
                </div>
            
                <div v-if="notifications.length" class="max-h-96 overflow-y-auto">
                  <div
                    v-for="notification in notifications"
                    :key="notification.id"
                    class="p-5 border-b border-slate-800"
                    :class="notification.is_read ? 'bg-slate-900' : 'bg-slate-950'"
                  >
                    <div class="flex gap-3">
                      <div
                        class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                        :class="{
                          'bg-emerald-500/10 text-emerald-400': notification.type === 'success',
                          'bg-amber-500/10 text-amber-400': notification.type === 'warning',
                          'bg-red-500/10 text-red-400': notification.type === 'danger',
                          'bg-slate-800 text-slate-400': notification.type === 'info'
                        }"
                      >
                        ✦
                      </div>
            
                      <div>
                        <h4 class="font-semibold text-white">
                          {{ notification.title }}
                        </h4>
            
                        <p class="text-sm text-slate-400 mt-1">
                          {{ notification.message }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
            
                <div v-else class="p-8 text-center text-slate-500">
                  No notifications yet.
                </div>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full xl:w-auto">
            <button
              @click="showExpenseModal = true"
              class="h-12 inline-flex items-center justify-center gap-2 rounded-2xl border border-red-500/30 bg-red-500/10 px-5 text-sm font-semibold text-red-300 transition hover:border-red-500/50 hover:bg-red-500 hover:text-white xl:min-w-40"
            >
              <span class="flex h-6 w-6 items-center justify-center rounded-full bg-red-500/20 text-base leading-none">
                -
              </span>
              Add Expense
            </button>

            <button
              @click="showIncomeModal = true"
              class="h-12 inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-500/40 bg-emerald-500 px-5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/10 transition hover:bg-emerald-600 xl:min-w-40"
            >
              <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/20 text-base leading-none">
                +
              </span>
              Add Income
            </button>
          </div>
        </div>
      </header>
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">
        
        <div class="finance-panel flex min-h-40 flex-col justify-between p-6" :class="loadingClass">
          <p class="text-slate-400 text-sm">Current Balance</p>
          <h3 class="text-3xl font-bold mt-3">{{ formatPeso(dashboard.balance) }}</h3>
          <p class="text-emerald-400 text-sm mt-3">Available funds</p>
        </div>

        <div class="finance-panel flex min-h-40 flex-col justify-between p-6" :class="loadingClass">
          <p class="text-slate-400 text-sm">Total Income</p>
          <h3 class="text-3xl font-bold mt-3 text-emerald-400">
            {{ formatPeso(dashboard.total_income) }}
          </h3>
          <p class="text-slate-500 text-sm mt-3">All income recorded</p>
        </div>

        <div class="finance-panel flex min-h-40 flex-col justify-between p-6" :class="loadingClass">
          <p class="text-slate-400 text-sm">Total Expenses</p>
          <h3 class="text-3xl font-bold mt-3 text-red-400">
            {{ formatPeso(dashboard.total_expenses) }}
          </h3>
          <p class="text-slate-500 text-sm mt-3">All expenses recorded</p>
        </div>

        <div class="finance-panel flex min-h-40 flex-col justify-between p-6" :class="loadingClass">
          <p class="text-slate-400 text-sm">Savings Score</p>
          <h3 class="text-3xl font-bold mt-3 text-amber-400">85%</h3>
          <p class="text-slate-500 text-sm mt-3">Healthy spending</p>
        </div>
      </div>

      <!-- Charts -->
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">

        <!-- Expense by Category -->
        <div class="finance-panel min-h-[380px] p-6" :class="loadingClass">
          <h3 class="text-xl font-bold mb-6">Expenses by Category</h3>
        
            <div
            v-if="analytics.expense_by_category.length"
            class="h-72 flex items-center justify-center"
            >
            <div class="w-full max-w-[280px]">
                <ExpenseCategoryChart :categories="analytics.expense_by_category" />
            </div>
            </div>
        
          <div v-else class="h-64 flex items-center justify-center text-slate-500">
            No expense data yet.
          </div>
        </div>

        <!-- Monthly Income vs Expenses -->
        <div class="finance-panel min-h-[380px] p-6 xl:col-span-2" :class="loadingClass">
          <h3 class="text-xl font-bold mb-6">Monthly Income vs Expenses</h3>
        
          <div v-if="analytics.monthly_summary.length">
            <MonthlySummaryChart :monthly-summary="analytics.monthly_summary" />
          </div>
        
          <div v-else class="h-72 flex items-center justify-center text-slate-500">
            No monthly data yet.
          </div>
        </div>
      </div>

      <!-- AI Insights and Health -->
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6 items-stretch">
        <div class="finance-panel p-6 xl:col-span-2" :class="loadingClass">
          <div class="flex items-center justify-between mb-6">
            <div>
              <p class="text-emerald-400 font-semibold text-sm">AI Assistant</p>
              <h3 class="text-xl font-bold">Financial Insights</h3>
            </div>
        
            <span class="bg-emerald-500/10 text-emerald-400 px-4 py-2 rounded-xl text-sm font-semibold">
              Smart Tips
            </span>
          </div>
        
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="insight in insights"
              :key="insight.title"
              class="finance-surface min-h-44 p-5"
            >
              <div
                class="w-10 h-10 rounded-xl flex items-center justify-center mb-4"
                :class="{
                  'bg-emerald-500/10 text-emerald-400': insight.type === 'success',
                  'bg-amber-500/10 text-amber-400': insight.type === 'warning',
                  'bg-red-500/10 text-red-400': insight.type === 'danger',
                  'bg-slate-800 text-slate-400': insight.type === 'neutral'
                }"
              >
                ✦
              </div>
        
              <h4 class="font-bold mb-2">
                {{ insight.title }}
              </h4>
        
              <p class="text-slate-400 text-sm leading-relaxed">
                {{ insight.message }}
              </p>
            </div>
          </div>
        </div>
        <div class="finance-panel p-6" :class="loadingClass">
          <div class="flex items-center justify-between mb-6">
            <div>
              <p class="text-emerald-400 font-semibold text-sm">
                AI Financial Health
              </p>
        
              <h3 class="text-xl font-bold">
                Finance Score
              </h3>
            </div>
        
            <div
              class="w-16 h-16 rounded-2xl flex items-center justify-center text-2xl font-bold"
              :class="{
                'bg-emerald-500/10 text-emerald-400': financialHealth.score >= 80,
                'bg-amber-500/10 text-amber-400':
                  financialHealth.score >= 60 &&
                  financialHealth.score < 80,
                'bg-red-500/10 text-red-400': financialHealth.score < 60
              }"
            >
              {{ financialHealth.score }}
            </div>
          </div>
        
          <div class="mb-5">
            <div class="flex justify-between text-sm mb-2">
              <span class="text-slate-400">
                Status
              </span>
        
              <span class="font-semibold">
                {{ financialHealth.status }}
              </span>
            </div>
        
            <div class="h-3 bg-slate-800 rounded-full overflow-hidden">
              <div
                class="h-3 rounded-full transition-all duration-500"
                :style="{ width: financialHealth.score + '%' }"
                :class="{
                  'bg-emerald-500': financialHealth.score >= 80,
                  'bg-amber-500':
                    financialHealth.score >= 60 &&
                    financialHealth.score < 80,
                  'bg-red-500': financialHealth.score < 60
                }"
              ></div>
            </div>
          </div>
        
          <div class="finance-surface p-5">
            <p class="text-slate-400 text-sm leading-relaxed">
              {{ financialHealth.recommendation }}
            </p>
          </div>
        </div>
        
      </div>

      <!-- Recent Transactions -->
      <div class="finance-panel overflow-hidden" :class="loadingClass">
        <div class="p-6 border-b border-slate-800 flex justify-between items-center">
          <h3 class="text-xl font-bold">Recent Transactions</h3>
          <router-link to="/transactions" class="text-emerald-400 text-sm font-semibold">
            View All
          </router-link>
        </div>

        <div v-if="dashboard.recent_transactions?.length">
          <div
            v-for="transaction in dashboard.recent_transactions"
            :key="transaction.id"
            class="grid grid-cols-1 gap-3 px-6 py-5 border-b border-slate-800 md:grid-cols-[minmax(0,1.5fr)_minmax(8rem,0.8fr)_minmax(8rem,0.8fr)_7rem] md:items-center"
          >
            <div>
              <p class="font-semibold">{{ transaction.title }}</p>
              <p class="text-sm text-slate-500">{{ transaction.category }}</p>
            </div>

            <p class="text-slate-400">{{ transaction.transaction_date }}</p>

            <p
              class="font-bold"
              :class="transaction.type === 'income' ? 'text-emerald-400' : 'text-red-400'"
            >
              {{ transaction.type === 'income' ? '+' : '-' }}{{ formatPeso(transaction.amount) }}
            </p>

            <span
              class="text-center px-3 py-1 rounded-full text-sm"
              :class="transaction.type === 'income' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400'"
            >
              {{ transaction.type }}
            </span>
          </div>
        </div>

        <div v-else class="p-10 text-center text-slate-500">
          No transactions yet.
        </div>
      </div>
    </section>
    <TransactionModal
      :show="showExpenseModal"
      type="expense"
      @close="showExpenseModal = false"
      @saved="refreshDashboard"
    />
    
    <TransactionModal
      :show="showIncomeModal"
      type="income"
      @close="showIncomeModal = false"
      @saved="refreshDashboard"
    />
    <SmartAssistantWidget v-if="!showExpenseModal && !showIncomeModal" />
  </main>
  <!-- Floating Button -->
  <div v-if="false" class="fixed bottom-6 right-6 z-50">
    <button
      @click="showAssistant = !showAssistant"
      class="w-16 h-16 rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white text-2xl shadow-2xl shadow-emerald-500/30 transition"
    >
      ✦
    </button>
  </div>
  
  <!-- Chatbot -->
  <div
    v-if="showAssistant"
    class="finance-panel fixed bottom-24 left-4 right-4 z-50 flex h-[min(600px,calc(100vh-8rem))] flex-col overflow-hidden sm:bottom-28 sm:left-auto sm:right-6 sm:w-[380px]"
  >
  
    <!-- Header -->
    <div class="p-5 border-b border-slate-800 flex items-center justify-between">
      <div>
        <p class="text-emerald-400 text-sm font-semibold">
          AI Assistant
        </p>
  
        <h3 class="font-bold text-white">
          PesoTracker Finance Chat
        </h3>
      </div>
  
      <button
        @click="showAssistant = false"
        class="w-10 h-10 rounded-xl bg-slate-800 text-slate-300"
      >
        ✕
      </button>
    </div>
  
    <!-- Body -->
    <div ref="chatBody" class="flex-1 overflow-y-auto p-5">
    
      <!-- Greeting -->
      <div class="finance-surface mb-4 p-4">
        <p class="text-sm text-slate-300 leading-relaxed">
          Hi! I am your PesoTracker finance assistant.
          Ask me about your spending, savings,
          budgets, and financial health.
        </p>
      </div>
    
      <!-- Messages -->
      <div class="space-y-4">
        <div
          v-for="(message, index) in assistantMessages"
          :key="index"
          class="flex"
          :class="message.role === 'user' ? 'justify-end' : 'justify-start'"
        >
          <div
            class="max-w-[80%] px-4 py-3 rounded-2xl text-sm leading-relaxed"
            :class="
              message.role === 'user'
                ? 'bg-emerald-500 text-white'
                : 'bg-slate-800 text-slate-200'
            "
          >
            {{ message.text }}
          </div>
        </div>
    
        <div v-if="assistantLoading" class="text-slate-400 text-sm">
          Assistant is typing...
        </div>
    
        <!-- Suggestions at bottom -->
        <div
          v-if="!assistantLoading"
          class="flex flex-wrap gap-2 pt-2"
        >
          <button
            v-for="suggestion in suggestions"
            :key="suggestion"
            @click="useSuggestion(suggestion)"
            class="bg-slate-800 hover:bg-emerald-500 text-slate-300 hover:text-white px-4 py-2 rounded-xl text-sm transition"
          >
            {{ suggestion }}
          </button>
        </div>
      </div>
    </div>
  
    <!-- Input -->
    <form
      @submit.prevent="sendAssistantMessage"
      class="p-5 border-t border-slate-800 flex gap-3"
    >
      <input
        v-model="assistantInput"
        type="text"
        placeholder="Ask about your finances..."
        class="flex-1 px-4 py-3 rounded-2xl bg-slate-950 border border-slate-800 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition"
      />
  
      <button
        type="submit"
        class="bg-emerald-500 hover:bg-emerald-600 text-white px-5 rounded-2xl font-bold transition"
      >
        Send
      </button>
    </form>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, onBeforeUnmount,nextTick } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import TransactionModal from '../components/TransactionModal.vue'
import ExpenseCategoryChart from '../components/ExpenseCategoryChart.vue'
import MonthlySummaryChart from '../components/MonthlySummaryChart.vue'
import Sidebar from '../components/Sidebar.vue'
import SmartAssistantWidget from '../components/SmartAssistantWidget.vue'
import { formatPeso } from '../utils/currency'
import {
  loadDashboardSnapshot,
  preloadAuthenticatedData,
  saveDashboardSnapshot
} from '../services/preload'

const insights = ref([])

const notifications = ref([])
const unreadCount = ref(0)
const showNotifications = ref(false)

let refreshInterval = null

const suggestionGroups = {
  default: [
    'How much did I spend on food this month?',
    'What is my biggest expense category?',
    'How much have I saved?',
    'What are my total expenses?',
    'Do I have active budgets?'
  ],

  food: [
    'How much did I spend on transportation?',
    'What are my latest food expenses?',
    'Which category do I spend the most on?'
  ],

  savings: [
    'How many savings goals do I have?',
    'What is my financial health score?',
    'How much do I save monthly?'
  ],

  expenses: [
    'Did I exceed my budget?',
    'What is my highest expense?',
    'How much are my recurring expenses?'
  ]
}

const suggestions = ref(suggestionGroups.default)

const useSuggestion = async (text) => {

  assistantInput.value = text

  await sendAssistantMessage()

  const lower = text.toLowerCase()

  if (lower.includes('food')) {

    suggestions.value = suggestionGroups.food

  } else if (
    lower.includes('save') ||
    lower.includes('savings')
  ) {

    suggestions.value = suggestionGroups.savings

  } else if (
    lower.includes('expense') ||
    lower.includes('budget')
  ) {

    suggestions.value = suggestionGroups.expenses

  } else {

    suggestions.value = suggestionGroups.default
  }
}

const chatBody = ref(null)

const scrollChatToBottom = async () => {
  await nextTick()

  if (chatBody.value) {
    chatBody.value.scrollTop = chatBody.value.scrollHeight
  }
}

const router = useRouter()
const user = ref(JSON.parse(localStorage.getItem('user')))
const displayName = computed(() => {
  return [user.value?.first_name, user.value?.last_name]
    .filter(Boolean)
    .join(' ') || user.value?.name || 'User'
})

const dashboard = reactive({
  total_income: 0,
  total_expenses: 0,
  balance: 0,
  recent_transactions: []
})

const dashboardLoaded = ref(false)
const hasCachedDashboard = ref(false)
const isRefreshingDashboard = ref(false)
const isPrimingDashboard = computed(() => !dashboardLoaded.value && !hasCachedDashboard.value)
const loadingClass = computed(() => {
  return isRefreshingDashboard.value ? 'opacity-80 transition-opacity' : ''
})

const applyDashboardSnapshot = (snapshot) => {
  if (!snapshot) return

  if (snapshot.dashboard) {
    dashboard.total_income = snapshot.dashboard.total_income ?? 0
    dashboard.total_expenses = snapshot.dashboard.total_expenses ?? 0
    dashboard.balance = snapshot.dashboard.balance ?? 0
    dashboard.recent_transactions = snapshot.dashboard.recent_transactions ?? []
  }

  if (snapshot.analytics) {
    analytics.expense_by_category = snapshot.analytics.expense_by_category ?? []
    analytics.monthly_summary = snapshot.analytics.monthly_summary ?? []
    analytics.highest_expense_category = snapshot.analytics.highest_expense_category ?? null
  }

  if (snapshot.insights) {
    insights.value = snapshot.insights
  }

  if (snapshot.financialHealth) {
    financialHealth.value = snapshot.financialHealth
  }

  if (snapshot.notifications) {
    notifications.value = snapshot.notifications
  }

  if (snapshot.unreadCount !== null && snapshot.unreadCount !== undefined) {
    unreadCount.value = snapshot.unreadCount
  }
}

const getDashboard = async () => {
  try {
    const response = await api.get('/dashboard')

    dashboard.total_income = response.data.total_income
    dashboard.total_expenses = response.data.total_expenses
    dashboard.balance = response.data.balance
    dashboard.recent_transactions = response.data.recent_transactions
  } catch (error) {
    console.error(error)
  }
}

const showExpenseModal = ref(false)
const showIncomeModal = ref(false)

const analytics = reactive({
  expense_by_category: [],
  monthly_summary: [],
  highest_expense_category: null
})


const getAnalytics = async () => {
  try {
    const response = await api.get('/analytics/summary')

    analytics.expense_by_category = response.data.expense_by_category
    analytics.monthly_summary = response.data.monthly_summary
    analytics.highest_expense_category = response.data.highest_expense_category
  } catch (error) {
    console.error(error)
  }
}

const getInsights = async () => {
  try {
    const response = await api.get('/insights')
    insights.value = response.data
  } catch (error) {
    console.error(error)
  }
}

const getNotifications = async () => {
  const response = await api.get('/notifications')
  notifications.value = response.data
}

const getUnreadCount = async () => {
  const response = await api.get('/notifications/unread-count')
  unreadCount.value = response.data.count
}

const markAllNotificationsRead = async () => {
  await api.post('/notifications/read-all')
  unreadCount.value = 0
  getNotifications()
}

const financialHealth = ref({
  score: 0,
  status: '',
  recommendation: '',
  income: 0,
  expenses: 0
})

const getFinancialHealth = async () => {
  try {
    const response = await api.get('/financial-health')

    financialHealth.value = response.data
  } catch (error) {
    console.error(error)
  }
}

const assistantMessages = ref([])

const assistantInput = ref('')
const assistantLoading = ref(false)
const showAssistant = ref(false)

const sendAssistantMessage = async () => {

  if (!assistantInput.value.trim()) return

  const userMessage = assistantInput.value

  assistantMessages.value.push({
    role: 'user',
    text: userMessage
  })

  await scrollChatToBottom()


  assistantInput.value = ''

  assistantLoading.value = true

  try {

    const response = await api.post('/finance-assistant', {
      message: userMessage
    })

    assistantMessages.value.push({
      role: 'assistant',
      text: response.data.reply
    })

  } catch (error) {

    assistantMessages.value.push({
      role: 'assistant',
      text: 'Something went wrong.'
    })

  } finally {

    assistantLoading.value = false
  }

  await scrollChatToBottom()
}

const refreshDashboard = () => {
  isRefreshingDashboard.value = true

  preloadAuthenticatedData()
    .then((snapshot) => {
      if (snapshot) {
        applyDashboardSnapshot(snapshot)
        saveDashboardSnapshot(snapshot)
      }
    })
    .finally(() => {
      dashboardLoaded.value = true
      isRefreshingDashboard.value = false
    })
}

onMounted(() => {
  const snapshot = loadDashboardSnapshot()

  if (snapshot) {
    hasCachedDashboard.value = true
    dashboardLoaded.value = true
    applyDashboardSnapshot(snapshot)
  }

  refreshDashboard()

  refreshInterval = setInterval(() => {
    getNotifications()
    getUnreadCount()
    getInsights()
    getFinancialHealth()
  }, 10000)
})

onBeforeUnmount(() => {
  clearInterval(refreshInterval)
})
</script>

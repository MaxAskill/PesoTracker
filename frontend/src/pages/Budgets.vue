<template>
  <main class="magic-bg min-h-screen text-white flex">
    <Sidebar />

    <section class="min-w-0 flex-1 p-4 pt-24 sm:p-6 lg:pt-6 overflow-y-auto">
      <div class="mb-8 flex flex-col gap-5 rounded-[2rem] border border-white/10 bg-slate-950/55 p-6 shadow-2xl shadow-slate-950/30 backdrop-blur md:flex-row md:items-center md:justify-between">
        <div>
          <p class="text-sm font-semibold uppercase tracking-wide text-emerald-300">Spending Plan</p>
          <h2 class="mt-2 text-3xl font-black md:text-4xl">Budgets</h2>
          <p class="mt-2 max-w-2xl text-sm text-slate-400">
            Plan spending limits and monitor your categories.
          </p>
        </div>

        <button
          @click="showModal = true"
          class="rounded-2xl border border-emerald-300/50 bg-gradient-to-r from-emerald-400 to-teal-300 px-6 py-3 font-black text-slate-950 shadow-lg shadow-emerald-500/20 transition hover:from-emerald-300 hover:to-teal-200"
        >
          Add Budget
        </button>
      </div>

      <div v-if="isLoading" class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        <div v-for="index in 3" :key="index" class="rounded-[2rem] border border-white/10 bg-slate-950/70 p-6">
          <div class="h-5 w-36 rounded-full bg-slate-800 animate-pulse"></div>
          <div class="mt-6 h-24 rounded-3xl bg-slate-800/50 animate-pulse"></div>
        </div>
      </div>

      <div v-else-if="budgets.length" class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        <div
          v-for="budget in budgets"
          :key="budget.id"
          class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-slate-950/70 p-6 shadow-2xl shadow-slate-950/20"
        >
          <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-emerald-400/10 blur-2xl"></div>

          <div class="relative mb-6 flex items-start justify-between gap-4">
            <div>
              <p class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ budget.month }}</p>
              <h3 class="mt-1 text-2xl font-black">{{ budget.category }}</h3>
            </div>

            <button @click="deleteBudget(budget.id)" class="rounded-xl bg-red-500/10 px-3 py-2 text-sm text-red-300 transition hover:bg-red-500 hover:text-white">
              Delete
            </button>
          </div>

          <div class="relative mb-5 rounded-3xl border border-white/10 bg-slate-950/80 p-5">
            <div class="mb-3 flex justify-between text-sm">
              <span class="text-slate-400">Usage</span>
              <span class="font-bold" :class="budgetTone(budget).text">{{ budgetUsage(budget) }}%</span>
            </div>

            <div class="h-3 overflow-hidden rounded-full bg-slate-800">
              <div class="h-3 rounded-full transition-all" :class="budgetTone(budget).bar" :style="{ width: budgetUsage(budget) + '%' }"></div>
            </div>
          </div>

          <div class="relative grid grid-cols-3 gap-3 text-sm">
            <div class="rounded-2xl border border-slate-800 bg-slate-950 p-3">
              <p class="text-slate-500">Spent</p>
              <p class="mt-1 font-black">{{ formatPeso(budgetSpent(budget)) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-800 bg-slate-950 p-3">
              <p class="text-slate-500">Limit</p>
              <p class="mt-1 font-black">{{ formatPeso(budget.amount) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-800 bg-slate-950 p-3">
              <p class="text-slate-500">Left</p>
              <p class="mt-1 font-black" :class="remainingBudget(budget) >= 0 ? 'text-emerald-300' : 'text-red-300'">
                {{ formatPeso(remainingBudget(budget)) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="flex min-h-72 items-center justify-center rounded-[2rem] border border-dashed border-slate-800 bg-slate-950/60 p-10 text-center text-slate-500">
        No budgets yet. Create a budget to start controlling your spending.
      </div>
    </section>

    <AppModal
      :show="showModal"
      title="Add Budget"
      subtitle="Set a spending limit for a category and month."
      @close="showModal = false"
    >
      <form class="space-y-5" @submit.prevent="saveBudget">
        <div>
          <label class="mb-2 block text-sm font-semibold text-slate-300">Category</label>
          <AppSelect
            v-model="form.category"
            :options="categoryOptions"
            placeholder="Select Category"
          />
        </div>

        <div>
          <label class="mb-2 block text-sm font-semibold text-slate-300">Budget Amount</label>
          <input v-model="form.amount" type="number" placeholder="5000" class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-white outline-none transition placeholder:text-slate-500 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" />
        </div>

        <div>
          <label class="mb-2 block text-sm font-semibold text-slate-300">Month</label>
          <AppSelect
            v-model="form.month"
            :options="months"
            placeholder="Select Month"
          />
        </div>

        <button type="submit" class="w-full rounded-xl bg-emerald-500 py-3.5 font-black text-slate-950 shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-400">
          Save Budget
        </button>
      </form>
    </AppModal>
  </main>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import api from '../services/api'
import AppModal from '../components/AppModal.vue'
import AppSelect from '../components/AppSelect.vue'
import Sidebar from '../components/Sidebar.vue'
import { formatPeso } from '../utils/currency'
import { loadDisplayCache, saveDisplayCache } from '../services/preload'

const budgets = ref([])
const transactions = ref([])
const isLoading = ref(false)
const showModal = ref(false)

const form = reactive({
  category: '',
  amount: '',
  month: ''
})

const categoryOptions = [
  { label: 'Food', value: 'Food' },
  { label: 'Transportation', value: 'Transportation' },
  { label: 'Bills', value: 'Bills' },
  { label: 'Shopping', value: 'Shopping' },
  { label: 'Utilities', value: 'Utilities' }
]

const months = Array.from({ length: 12 }, (_, index) => {
  const date = new Date(new Date().getFullYear(), index)
  return {
    value: `${date.getFullYear()}-${String(index + 1).padStart(2, '0')}`,
    label: date.toLocaleString('en-US', { month: 'long', year: 'numeric' })
  }
})

const budgetSpent = (budget) => {
  return transactions.value
    .filter(transaction => transaction.type === 'expense' && transaction.category === budget.category && transaction.transaction_date?.startsWith(budget.month))
    .reduce((sum, transaction) => sum + Number(transaction.amount), 0)
}

const remainingBudget = (budget) => Number(budget.amount) - budgetSpent(budget)
const budgetUsage = (budget) => Math.min(Math.round((budgetSpent(budget) / Number(budget.amount || 1)) * 100), 100)

const budgetTone = (budget) => {
  const usage = budgetUsage(budget)
  if (remainingBudget(budget) < 0 || usage >= 100) return { text: 'text-red-300', bar: 'bg-red-400' }
  if (usage >= 80) return { text: 'text-amber-300', bar: 'bg-amber-400' }
  return { text: 'text-emerald-300', bar: 'bg-emerald-400' }
}

const getBudgets = async () => {
  isLoading.value = !budgets.value.length

  try {
    const [budgetsResponse, transactionsResponse] = await Promise.all([
      api.get('/budgets'),
      api.get('/transactions')
    ])

    budgets.value = budgetsResponse.data
    transactions.value = transactionsResponse.data
    saveDisplayCache('budgets', budgetsResponse.data)
  } catch (error) {
    console.error(error)
  } finally {
    isLoading.value = false
  }
}

const saveBudget = async () => {
  try {
    await api.post('/budgets', form)
    showModal.value = false
    form.category = ''
    form.amount = ''
    form.month = ''
    getBudgets()
  } catch (error) {
    console.log(error.response?.data)
  }
}

const deleteBudget = async (id) => {
  if (!confirm('Delete this budget?')) return

  try {
    await api.delete(`/budgets/${id}`)
    budgets.value = budgets.value.filter(budget => budget.id !== id)
    saveDisplayCache('budgets', budgets.value)
  } catch (error) {
    console.error(error)
  }
}

onMounted(() => {
  const cachedBudgets = loadDisplayCache('budgets')
  if (cachedBudgets) budgets.value = cachedBudgets
  getBudgets()
})
</script>

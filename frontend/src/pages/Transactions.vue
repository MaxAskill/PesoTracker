<template>
  <main class="magic-bg min-h-screen text-white flex overflow-x-hidden">
    <Sidebar />

    <section class="min-w-0 flex-1 p-4 pt-24 sm:p-6 lg:pt-6 overflow-y-auto">
      <div class="mb-8 flex flex-col gap-5 rounded-[2rem] border border-white/10 bg-slate-950/55 p-6 shadow-2xl shadow-slate-950/30 backdrop-blur xl:flex-row xl:items-center xl:justify-between">
        <div>
          <p class="text-sm font-semibold uppercase tracking-wide text-emerald-300">Ledger</p>
          <h2 class="mt-2 text-3xl font-black md:text-4xl">Transactions</h2>
          <p class="mt-2 max-w-2xl text-sm text-slate-400">
            Track your income and expenses in one place.
          </p>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
          <button
            @click="showExpenseModal = true"
            class="rounded-2xl border border-red-500/30 bg-red-500/10 px-5 py-3 text-sm font-bold text-red-200 transition hover:bg-red-500 hover:text-white"
          >
            Add Expense
          </button>
          <button
            @click="showIncomeModal = true"
            class="rounded-2xl border border-emerald-300/50 bg-gradient-to-r from-emerald-400 to-teal-300 px-5 py-3 text-sm font-black text-slate-950 shadow-lg shadow-emerald-500/20 transition hover:from-emerald-300 hover:to-teal-200"
          >
            Add Income
          </button>
        </div>
      </div>

      <div class="mb-6 grid grid-cols-1 gap-5 md:grid-cols-3">
        <div class="rounded-[2rem] border border-white/10 bg-slate-950/70 p-6 shadow-2xl shadow-slate-950/20">
          <p class="text-sm font-semibold uppercase tracking-wide text-slate-400">Total Income</p>
          <h3 class="mt-4 text-3xl font-black text-emerald-300">{{ formatPeso(totalIncome) }}</h3>
        </div>
        <div class="rounded-[2rem] border border-white/10 bg-slate-950/70 p-6 shadow-2xl shadow-slate-950/20">
          <p class="text-sm font-semibold uppercase tracking-wide text-slate-400">Total Expenses</p>
          <h3 class="mt-4 text-3xl font-black text-red-300">{{ formatPeso(totalExpenses) }}</h3>
        </div>
        <div class="rounded-[2rem] border border-emerald-400/20 bg-gradient-to-br from-emerald-400/15 to-slate-950/80 p-6 shadow-2xl shadow-slate-950/20">
          <p class="text-sm font-semibold uppercase tracking-wide text-slate-400">Net Balance</p>
          <h3 class="mt-4 text-3xl font-black text-white">{{ formatPeso(netBalance) }}</h3>
        </div>
      </div>

      <div class="mb-8 grid grid-cols-1 gap-4 rounded-[2rem] border border-white/10 bg-slate-950/55 p-4 md:grid-cols-2 xl:grid-cols-5">
        <input
          v-model="search"
          type="text"
          placeholder="Search transaction..."
          class="min-w-0 w-full rounded-2xl border border-slate-800 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-500"
        />

        <select v-model="selectedCategory" class="min-w-0 w-full rounded-2xl border border-slate-800 bg-slate-950 px-4 py-3 text-white">
          <option value="">All Categories</option>
          <option>Food</option>
          <option>Bills</option>
          <option>Transportation</option>
          <option>Shopping</option>
          <option>Salary</option>
          <option>Savings</option>
        </select>

        <select v-model="selectedType" class="min-w-0 w-full rounded-2xl border border-slate-800 bg-slate-950 px-4 py-3 text-white">
          <option value="">All Types</option>
          <option value="income">Income</option>
          <option value="expense">Expense</option>
        </select>

        <input v-model="selectedMonth" type="month" class="min-w-0 w-full rounded-2xl border border-slate-800 bg-slate-950 px-4 py-3 text-white" />

        <select v-model="sortBy" class="min-w-0 w-full rounded-2xl border border-slate-800 bg-slate-950 px-4 py-3 text-white">
          <option value="latest">Latest</option>
          <option value="oldest">Oldest</option>
          <option value="highest">Highest Amount</option>
          <option value="lowest">Lowest Amount</option>
        </select>
      </div>

      <div class="hidden overflow-hidden rounded-[2rem] border border-white/10 bg-slate-950/70 shadow-2xl shadow-slate-950/20 lg:block">
        <div class="grid grid-cols-6 gap-4 border-b border-slate-800 px-6 py-4 text-sm font-semibold text-slate-400">
          <span>Title</span>
          <span>Category</span>
          <span>Type</span>
          <span>Amount</span>
          <span>Date</span>
          <span class="text-center">Actions</span>
        </div>

        <div v-if="isLoading">
          <div v-for="index in 4" :key="index" class="grid grid-cols-6 gap-4 border-b border-slate-800 px-6 py-5">
            <div class="h-4 w-36 rounded-full bg-slate-800/80 animate-pulse"></div>
            <div class="h-4 w-24 rounded-full bg-slate-800/70 animate-pulse"></div>
            <div class="h-6 w-20 rounded-full bg-slate-800/70 animate-pulse"></div>
            <div class="h-4 w-24 rounded-full bg-slate-800/80 animate-pulse"></div>
            <div class="h-4 w-24 rounded-full bg-slate-800/70 animate-pulse"></div>
            <div class="h-8 w-28 rounded-xl bg-slate-800/70 animate-pulse"></div>
          </div>
        </div>

        <div v-else-if="filteredTransactions.length">
          <div
            v-for="transaction in filteredTransactions"
            :key="transaction.id"
            class="grid grid-cols-6 items-center gap-4 border-b border-slate-800 px-6 py-5 transition hover:bg-slate-900/60"
          >
            <div>
              <p class="font-semibold">{{ transaction.title }}</p>
              <p v-if="transaction.note" class="truncate text-sm text-slate-500">{{ transaction.note }}</p>
            </div>
            <span class="w-fit rounded-full bg-slate-900 px-3 py-1 text-sm text-slate-300">{{ transaction.category }}</span>
            <span class="w-fit rounded-full px-3 py-1 text-xs font-bold" :class="typeBadgeClass(transaction.type)">
              {{ transaction.type }}
            </span>
            <span class="font-black" :class="transaction.type === 'income' ? 'text-emerald-300' : 'text-red-300'">
              {{ transaction.type === 'income' ? '+' : '-' }}{{ formatPeso(transaction.amount) }}
            </span>
            <span class="text-slate-400">{{ transaction.transaction_date }}</span>
            <div class="flex items-center justify-center gap-3">
              <button @click="editTransaction(transaction)" class="rounded-xl bg-slate-800 px-4 py-2 text-sm transition hover:bg-slate-700">Edit</button>
              <button @click="deleteTransaction(transaction.id)" class="rounded-xl bg-red-500/10 px-4 py-2 text-sm text-red-300 transition hover:bg-red-500 hover:text-white">Delete</button>
            </div>
          </div>
        </div>

        <div v-else class="flex min-h-72 items-center justify-center p-10 text-center text-slate-500">
          No transactions yet. Add your first income or expense to start tracking.
        </div>
      </div>

      <div class="space-y-4 lg:hidden">
        <div v-if="isLoading">
          <div v-for="index in 3" :key="index" class="mb-4 rounded-[2rem] border border-white/10 bg-slate-950/70 p-5">
            <div class="h-5 w-40 rounded-full bg-slate-800 animate-pulse"></div>
            <div class="mt-5 h-20 rounded-2xl bg-slate-800/50 animate-pulse"></div>
          </div>
        </div>

        <div v-else-if="filteredTransactions.length">
          <div
            v-for="transaction in filteredTransactions"
            :key="transaction.id"
            class="rounded-[2rem] border border-white/10 bg-slate-950/70 p-5 shadow-2xl shadow-slate-950/20"
          >
            <div class="mb-5 flex items-start justify-between gap-3">
              <div class="min-w-0">
                <h3 class="break-words text-lg font-black text-white">{{ transaction.title }}</h3>
                <p class="mt-1 text-sm text-slate-400">{{ transaction.transaction_date }}</p>
              </div>
              <div class="shrink-0 rounded-xl px-3 py-1 text-sm font-bold" :class="typeBadgeClass(transaction.type)">
                {{ transaction.type }}
              </div>
            </div>

            <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div class="rounded-2xl border border-slate-800 bg-slate-950 p-4">
                <p class="mb-2 text-xs text-slate-500">Category</p>
                <h4 class="break-words font-semibold">{{ transaction.category }}</h4>
              </div>
              <div class="rounded-2xl border border-slate-800 bg-slate-950 p-4">
                <p class="mb-2 text-xs text-slate-500">Amount</p>
                <h4 class="font-black" :class="transaction.type === 'income' ? 'text-emerald-300' : 'text-red-300'">
                  {{ formatPeso(transaction.amount) }}
                </h4>
              </div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
              <button @click="editTransaction(transaction)" class="flex-1 rounded-2xl bg-emerald-500 py-3 font-bold text-slate-950 transition hover:bg-emerald-400">Edit</button>
              <button @click="deleteTransaction(transaction.id)" class="flex-1 rounded-2xl bg-red-500/10 py-3 font-bold text-red-300 transition hover:bg-red-500 hover:text-white">Delete</button>
            </div>
          </div>
        </div>

        <div v-else class="flex min-h-72 items-center justify-center rounded-[2rem] border border-dashed border-slate-800 bg-slate-950/60 p-10 text-center text-slate-500">
          No transactions yet. Add your first income or expense to start tracking.
        </div>
      </div>
    </section>

    <TransactionModal :show="showEditModal" :transaction="selectedTransaction" @close="showEditModal = false" @saved="getTransactions" />
    <TransactionModal :show="showExpenseModal" type="expense" @close="showExpenseModal = false" @saved="getTransactions" />
    <TransactionModal :show="showIncomeModal" type="income" @close="showIncomeModal = false" @saved="getTransactions" />
  </main>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import api from '../services/api'
import TransactionModal from '../components/TransactionModal.vue'
import Sidebar from '../components/Sidebar.vue'
import { formatPeso } from '../utils/currency'
import { loadDisplayCache, saveDisplayCache } from '../services/preload'

const showEditModal = ref(false)
const showExpenseModal = ref(false)
const showIncomeModal = ref(false)
const selectedTransaction = ref(null)
const transactions = ref([])
const isLoading = ref(false)
const search = ref('')
const selectedCategory = ref('')
const selectedType = ref('')
const selectedMonth = ref('')
const sortBy = ref('latest')

const totalIncome = computed(() => transactions.value.filter(item => item.type === 'income').reduce((sum, item) => sum + Number(item.amount), 0))
const totalExpenses = computed(() => transactions.value.filter(item => item.type === 'expense').reduce((sum, item) => sum + Number(item.amount), 0))
const netBalance = computed(() => totalIncome.value - totalExpenses.value)

const typeBadgeClass = (type) => {
  return type === 'income'
    ? 'bg-emerald-500/10 text-emerald-300'
    : 'bg-red-500/10 text-red-300'
}

const getTransactions = async () => {
  isLoading.value = !transactions.value.length

  try {
    const response = await api.get('/transactions')
    transactions.value = response.data
    saveDisplayCache('transactions', response.data)
  } catch (error) {
    console.error(error)
  } finally {
    isLoading.value = false
  }
}

const deleteTransaction = async (id) => {
  if (!confirm('Delete this transaction?')) return

  try {
    await api.delete(`/transactions/${id}`)
    transactions.value = transactions.value.filter(transaction => transaction.id !== id)
    saveDisplayCache('transactions', transactions.value)
  } catch (error) {
    console.error(error)
  }
}

const editTransaction = (transaction) => {
  selectedTransaction.value = transaction
  showEditModal.value = true
}

const filteredTransactions = computed(() => {
  let data = [...transactions.value]

  if (search.value) {
    data = data.filter(transaction => transaction.title.toLowerCase().includes(search.value.toLowerCase()))
  }

  if (selectedCategory.value) {
    data = data.filter(transaction => transaction.category === selectedCategory.value)
  }

  if (selectedType.value) {
    data = data.filter(transaction => transaction.type === selectedType.value)
  }

  if (selectedMonth.value) {
    data = data.filter(transaction => transaction.transaction_date.startsWith(selectedMonth.value))
  }

  switch (sortBy.value) {
    case 'oldest':
      data.sort((a, b) => new Date(a.transaction_date) - new Date(b.transaction_date))
      break
    case 'highest':
      data.sort((a, b) => b.amount - a.amount)
      break
    case 'lowest':
      data.sort((a, b) => a.amount - b.amount)
      break
    default:
      data.sort((a, b) => new Date(b.transaction_date) - new Date(a.transaction_date))
  }

  return data
})

onMounted(() => {
  const cachedTransactions = loadDisplayCache('transactions')

  if (cachedTransactions) {
    transactions.value = cachedTransactions
  }

  getTransactions()
})
</script>

<template>
  <main class="magic-bg flex min-h-screen overflow-x-hidden text-white">
    <Sidebar />

    <section class="min-w-0 flex-1 overflow-x-hidden p-4 pt-24 sm:p-6 lg:pt-6">
      <div class="mb-6 flex flex-col gap-5 rounded-[2rem] border border-white/10 bg-slate-950/70 p-6 shadow-2xl shadow-slate-950/30 backdrop-blur xl:flex-row xl:items-center xl:justify-between">
        <div>
          <p class="text-sm font-semibold uppercase tracking-wide text-emerald-300">Transaction Center</p>
          <h2 class="mt-2 text-3xl font-black md:text-4xl">Transactions</h2>
          <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-400">
            Track your income, expenses, receipts, and spending activity.
          </p>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
          <button
            type="button"
            class="rounded-2xl border border-emerald-400/30 bg-slate-950/70 px-5 py-3 text-sm font-bold text-emerald-200 transition hover:bg-emerald-500/10"
            @click="showExpenseModal = true"
          >
            Scan Receipt
          </button>
          <button
            type="button"
            class="rounded-2xl border border-red-500/30 bg-red-500/10 px-5 py-3 text-sm font-bold text-red-200 transition hover:bg-red-500 hover:text-white"
            @click="showExpenseModal = true"
          >
            Add Expense
          </button>
          <button
            type="button"
            class="rounded-2xl border border-emerald-300/50 bg-gradient-to-r from-emerald-400 to-teal-300 px-5 py-3 text-sm font-black text-slate-950 shadow-lg shadow-emerald-500/20 transition hover:from-emerald-300 hover:to-teal-200"
            @click="showIncomeModal = true"
          >
            Add Income
          </button>
        </div>
      </div>

      <div class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <template v-if="isLoading">
          <div
            v-for="index in 4"
            :key="`summary-${index}`"
            class="min-h-36 rounded-[2rem] border border-white/10 bg-slate-950/70 p-6 shadow-2xl shadow-slate-950/20"
          >
            <div class="h-4 w-28 animate-pulse rounded-full bg-slate-800"></div>
            <div class="mt-6 h-9 w-36 animate-pulse rounded-2xl bg-slate-800/80"></div>
            <div class="mt-5 h-3 w-20 animate-pulse rounded-full bg-slate-800/60"></div>
          </div>
        </template>

        <template v-else>
          <SummaryCard
            label="Total Income"
            :value="formatPeso(totalIncome)"
            tone="income"
            caption="Money received"
          />
          <SummaryCard
            label="Total Expenses"
            :value="formatPeso(totalExpenses)"
            tone="expense"
            caption="Money spent"
          />
          <SummaryCard
            label="Net Balance"
            :value="formatPeso(netBalance)"
            tone="balance"
            caption="Income minus expenses"
          />
          <SummaryCard
            label="Transactions"
            :value="String(transactions.length)"
            tone="neutral"
            caption="Recorded activity"
          />
        </template>
      </div>

      <div class="mb-6 rounded-[2rem] border border-white/10 bg-slate-950/65 p-4 shadow-2xl shadow-slate-950/20">
        <div v-if="isLoading" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
          <div v-for="index in 5" :key="`filter-${index}`" class="h-12 animate-pulse rounded-2xl bg-slate-800/70"></div>
        </div>

        <div v-else>
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
            <input
              v-model="search"
              type="text"
              placeholder="Search transactions..."
              class="transaction-field xl:col-span-1"
            />

            <AppSelect
              v-model="selectedType"
              :options="typeOptions"
              placeholder="All Types"
            />

            <AppSelect
              v-model="selectedCategory"
              :options="categoryOptions"
              placeholder="All Categories"
            />

            <AppMonthFilter v-model="selectedMonth" />

            <AppSelect
              v-model="sortBy"
              :options="sortOptions"
              placeholder="Newest first"
            />
          </div>

          <div class="mt-4 flex flex-wrap items-center gap-2">
            <span class="mr-1 text-xs font-semibold uppercase tracking-wide text-slate-500">Quick month</span>
            <button
              type="button"
              class="filter-chip"
              :class="selectedMonth === currentMonthValue ? 'filter-chip-active' : ''"
              @click="setThisMonth"
            >
              This Month
            </button>
            <button
              type="button"
              class="filter-chip"
              :class="selectedMonth === previousMonthValue ? 'filter-chip-active' : ''"
              @click="setLastMonth"
            >
              Last Month
            </button>
            <button
              v-if="selectedMonth"
              type="button"
              class="filter-chip"
              @click="clearMonthFilter"
            >
              Clear
            </button>
            <span v-if="selectedMonth" class="rounded-full border border-emerald-400/20 bg-emerald-500/10 px-3 py-2 text-xs font-bold text-emerald-200">
              Viewing {{ formattedSelectedMonth }}
            </span>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-6">
        <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-slate-950/70 shadow-2xl shadow-slate-950/20">
          <div class="flex flex-col gap-3 border-b border-slate-800 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <p class="text-sm font-semibold uppercase tracking-wide text-emerald-300">Activity</p>
              <h3 class="mt-1 text-2xl font-black">Transaction List</h3>
            </div>
            <span class="w-fit rounded-full bg-slate-900 px-4 py-2 text-sm font-bold text-slate-400">
              {{ filteredTransactions.length }} shown
            </span>
          </div>

          <div v-if="isLoading" class="divide-y divide-slate-800">
            <div v-for="index in 5" :key="`row-${index}`" class="grid gap-4 p-5 md:grid-cols-[minmax(0,1fr)_8rem_9rem] md:items-center">
              <div class="flex items-center gap-4">
                <div class="h-12 w-12 animate-pulse rounded-2xl bg-slate-800"></div>
                <div class="min-w-0 flex-1">
                  <div class="h-4 w-44 animate-pulse rounded-full bg-slate-800"></div>
                  <div class="mt-3 h-3 w-28 animate-pulse rounded-full bg-slate-800/70"></div>
                </div>
              </div>
              <div class="h-5 w-24 animate-pulse rounded-full bg-slate-800"></div>
              <div class="h-10 w-full animate-pulse rounded-2xl bg-slate-800/70"></div>
            </div>
          </div>

          <div v-else-if="filteredTransactions.length" class="divide-y divide-slate-800">
            <div
              v-for="transaction in filteredTransactions"
              :key="transaction.id"
              class="grid w-full cursor-pointer gap-4 px-5 py-5 text-left transition hover:bg-slate-900/70 md:grid-cols-[minmax(0,1fr)_8rem_9rem] md:items-center"
              :class="detailsTransaction?.id === transaction.id ? 'bg-emerald-500/[0.06] ring-1 ring-inset ring-emerald-400/25' : ''"
              @click="openDetails(transaction)"
            >
              <div class="flex min-w-0 items-start gap-4">
                <div
                  class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border text-lg font-black"
                  :class="transaction.type === 'income' ? 'border-emerald-400/20 bg-emerald-500/10 text-emerald-300' : 'border-red-400/20 bg-red-500/10 text-red-300'"
                >
                  {{ transaction.type === 'income' ? '+' : '-' }}
                </div>

                <div class="min-w-0">
                  <div class="flex flex-wrap items-center gap-2">
                    <h4 class="break-words font-black text-white">{{ transaction.title }}</h4>
                    <span class="rounded-full px-2.5 py-1 text-xs font-bold capitalize" :class="typeBadgeClass(transaction.type)">
                      {{ transaction.type }}
                    </span>
                    <span v-if="hasReceipt(transaction)" class="rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-bold text-emerald-300">
                      Receipt
                    </span>
                  </div>
                  <p class="mt-1 text-sm text-slate-400">
                    {{ transaction.category }} - {{ transaction.transaction_date }}
                  </p>
                  <p v-if="transaction.note" class="mt-1 line-clamp-1 text-sm text-slate-500">
                    {{ transaction.note }}
                  </p>
                </div>
              </div>

              <div class="md:text-right">
                <p class="text-xl font-black" :class="amountClass(transaction.type)">
                  {{ transaction.type === 'income' ? '+' : '-' }}{{ formatPeso(transaction.amount) }}
                </p>
                <p class="mt-1 text-xs text-slate-500">Recorded</p>
              </div>

              <div class="flex items-center gap-2 md:justify-end" @click.stop>
                <button
                  type="button"
                  class="icon-action-button"
                  aria-label="Edit transaction"
                  title="Edit transaction"
                  @click="editTransaction(transaction)"
                >
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M4 20h4l10.5-10.5a2.1 2.1 0 0 0-3-3L5 17v3Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="m14 8 2 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                  </svg>
                </button>
                <button
                  type="button"
                  class="icon-danger-button"
                  aria-label="Delete transaction"
                  title="Delete transaction"
                  @click="deleteTransaction(transaction.id)"
                >
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 7h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                    <path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                    <path d="M7 7l1 13h8l1-13M9 7l1-3h4l1 3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <div v-else class="flex min-h-80 items-center justify-center p-8 text-center">
            <div>
              <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-3xl bg-emerald-500/10 text-2xl font-black text-emerald-300">
                i
              </div>
              <h3 class="text-xl font-black text-white">{{ emptyStateTitle }}</h3>
              <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-500">
                {{ emptyStateMessage }}
              </p>
              <button
                v-if="transactions.length && selectedMonth"
                type="button"
                class="mt-6 rounded-2xl border border-emerald-400/30 bg-emerald-500/10 px-5 py-3 font-bold text-emerald-200 transition hover:bg-emerald-500 hover:text-slate-950"
                @click="clearMonthFilter"
              >
                Clear month filter
              </button>
              <div v-if="!transactions.length" class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <button type="button" class="rounded-2xl bg-emerald-500 px-5 py-3 font-black text-slate-950 transition hover:bg-emerald-400" @click="showIncomeModal = true">
                  Add Income
                </button>
                <button type="button" class="rounded-2xl bg-red-500/10 px-5 py-3 font-bold text-red-300 transition hover:bg-red-500 hover:text-white" @click="showExpenseModal = true">
                  Add Expense
                </button>
              </div>
            </div>
          </div>
        </div>

      </div>
    </section>

    <div
      v-if="detailsTransaction"
      class="fixed inset-0 z-[60] bg-slate-950/75 backdrop-blur-sm"
      @click="closeDetails"
    ></div>

    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="translate-y-full opacity-80 md:translate-x-full md:translate-y-0"
      enter-to-class="translate-y-0 opacity-100 md:translate-x-0"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="translate-y-0 opacity-100 md:translate-x-0"
      leave-to-class="translate-y-full opacity-80 md:translate-x-full md:translate-y-0"
    >
    <aside
      v-if="detailsTransaction"
      class="fixed inset-x-0 bottom-0 z-[70] max-h-[90vh] overflow-y-auto rounded-t-[2rem] border border-white/10 bg-slate-950 p-5 shadow-2xl shadow-black/60 md:inset-y-0 md:left-auto md:right-0 md:h-screen md:max-h-none md:w-[min(480px,100vw)] md:rounded-l-[2rem] md:rounded-tr-none md:border-l md:border-r-0 md:border-t-0 md:p-6"
      @click.stop
    >
      <TransactionDetailsPanel
        :transaction="detailsTransaction"
        :format-peso="formatPeso"
        drawer
        @edit="editTransaction"
        @delete="deleteTransaction"
        @close="closeDetails"
      />
    </aside>
    </Transition>

    <TransactionModal :show="showEditModal" :transaction="editingTransaction" @close="closeEditModal" @saved="handleTransactionSaved" />
    <TransactionModal :show="showExpenseModal" type="expense" @close="showExpenseModal = false" @saved="handleTransactionSaved" />
    <TransactionModal :show="showIncomeModal" type="income" @close="showIncomeModal = false" @saved="handleTransactionSaved" />
  </main>
</template>

<script setup>
import { computed, defineComponent, h, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import api from '../services/api'
import AppMonthFilter from '../components/AppMonthFilter.vue'
import AppSelect from '../components/AppSelect.vue'
import TransactionModal from '../components/TransactionModal.vue'
import Sidebar from '../components/Sidebar.vue'
import { formatPeso } from '../utils/currency'
import { loadDisplayCache, saveDisplayCache } from '../services/preload'

const showEditModal = ref(false)
const showExpenseModal = ref(false)
const showIncomeModal = ref(false)
const editingTransaction = ref(null)
const detailsTransaction = ref(null)
const transactions = ref([])
const isLoading = ref(false)
const search = ref('')
const selectedCategory = ref('')
const selectedType = ref('')
const selectedMonth = ref('')
const sortBy = ref('latest')

const typeOptions = [
  { label: 'All Types', value: '' },
  { label: 'Income', value: 'income' },
  { label: 'Expense', value: 'expense' }
]

const sortOptions = [
  { label: 'Newest first', value: 'latest' },
  { label: 'Oldest first', value: 'oldest' },
  { label: 'Highest amount', value: 'highest' },
  { label: 'Lowest amount', value: 'lowest' }
]

const totalIncome = computed(() => transactions.value.filter(item => item.type === 'income').reduce((sum, item) => sum + Number(item.amount), 0))
const totalExpenses = computed(() => transactions.value.filter(item => item.type === 'expense').reduce((sum, item) => sum + Number(item.amount), 0))
const netBalance = computed(() => totalIncome.value - totalExpenses.value)
const currentMonthValue = computed(() => getCurrentMonthValue())
const previousMonthValue = computed(() => {
  return getPreviousMonthValue()
})
const formattedSelectedMonth = computed(() => formatMonthValue(selectedMonth.value))

const categories = computed(() => {
  const values = transactions.value
    .map(transaction => transaction.category)
    .filter(Boolean)

  return [...new Set(['Food', 'Bills', 'Transportation', 'Shopping', 'Salary', 'Savings', ...values])]
})

const categoryOptions = computed(() => [
  { label: 'All Categories', value: '' },
  ...categories.value.map(category => ({
    label: category,
    value: category
  }))
])

const amountClass = (type) => {
  return type === 'income' ? 'text-emerald-300' : 'text-red-300'
}

const typeBadgeClass = (type) => {
  return type === 'income'
    ? 'bg-emerald-500/10 text-emerald-300'
    : 'bg-red-500/10 text-red-300'
}

const hasReceipt = (transaction) => {
  return Boolean(transaction.receipt_image_url || transaction.receipt_url || transaction.receipt_image)
}

const receiptUrl = (transaction) => {
  return transaction?.receipt_image_url || transaction?.receipt_url || transaction?.receipt_image || ''
}

const getTransactions = async () => {
  isLoading.value = !transactions.value.length

  try {
    const response = await api.get('/transactions')
    transactions.value = response.data
    saveDisplayCache('transactions', response.data)

    if (detailsTransaction.value) {
      detailsTransaction.value = response.data.find(transaction => transaction.id === detailsTransaction.value.id) || null
    }
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

    if (detailsTransaction.value?.id === id) {
      detailsTransaction.value = null
    }

    saveDisplayCache('transactions', transactions.value)
  } catch (error) {
    console.error(error)
  }
}

const editTransaction = (transaction) => {
  editingTransaction.value = transaction
  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
  editingTransaction.value = null
}

const openDetails = (transaction) => {
  detailsTransaction.value = transaction
}

const closeDetails = () => {
  detailsTransaction.value = null
}

const handleTransactionSaved = () => {
  showEditModal.value = false
  showExpenseModal.value = false
  showIncomeModal.value = false
  editingTransaction.value = null
  getTransactions()
}

const handleEscape = (event) => {
  if (event.key === 'Escape') {
    closeDetails()
  }
}

const monthValueFromDate = (date) => {
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`
}

const getCurrentMonthValue = () => {
  const now = new Date()
  return monthValueFromDate(new Date(now.getFullYear(), now.getMonth(), 1))
}

const getPreviousMonthValue = () => {
  const now = new Date()
  return monthValueFromDate(new Date(now.getFullYear(), now.getMonth() - 1, 1))
}

const formatMonthValue = (value) => {
  if (!value) return ''

  const [year, month] = value.split('-').map(Number)
  if (!year || !month) return ''

  return new Date(year, month - 1, 1).toLocaleDateString('en-US', {
    month: 'long',
    year: 'numeric'
  })
}

const setThisMonth = () => {
  selectedMonth.value = currentMonthValue.value
}

const setLastMonth = () => {
  selectedMonth.value = previousMonthValue.value
}

const clearMonthFilter = () => {
  selectedMonth.value = ''
}

const filteredTransactions = computed(() => {
  let data = [...transactions.value]

  if (search.value) {
    const term = search.value.toLowerCase()
    data = data.filter(transaction => {
      return [
        transaction.title,
        transaction.category,
        transaction.note,
        transaction.type,
        transaction.transaction_date
      ].filter(Boolean).some(value => String(value).toLowerCase().includes(term))
    })
  }

  if (selectedCategory.value) {
    data = data.filter(transaction => transaction.category === selectedCategory.value)
  }

  if (selectedType.value) {
    data = data.filter(transaction => transaction.type === selectedType.value)
  }

  if (selectedMonth.value) {
    data = data.filter(transaction => transaction.transaction_date?.startsWith(selectedMonth.value))
  }

  switch (sortBy.value) {
    case 'oldest':
      data.sort((a, b) => new Date(a.transaction_date) - new Date(b.transaction_date))
      break
    case 'highest':
      data.sort((a, b) => Number(b.amount) - Number(a.amount))
      break
    case 'lowest':
      data.sort((a, b) => Number(a.amount) - Number(b.amount))
      break
    default:
      data.sort((a, b) => new Date(b.transaction_date) - new Date(a.transaction_date))
  }

  return data
})

const emptyStateTitle = computed(() => {
  if (!transactions.value.length) return 'No transactions yet'
  if (selectedMonth.value) return `No transactions for ${formattedSelectedMonth.value}`
  return 'No matching transactions'
})

const emptyStateMessage = computed(() => {
  if (!transactions.value.length) {
    return 'Add your first income or expense to start tracking your money.'
  }

  if (selectedMonth.value) {
    return 'Try another month or clear the month filter.'
  }

  return 'Try adjusting your search or filters.'
})

const SummaryCard = defineComponent({
  props: {
    label: { type: String, required: true },
    value: { type: String, required: true },
    caption: { type: String, required: true },
    tone: { type: String, required: true }
  },
  setup(props) {
    const toneClass = computed(() => {
      return {
        income: 'border-emerald-400/20 bg-emerald-500/10 text-emerald-300',
        expense: 'border-red-400/20 bg-red-500/10 text-red-300',
        balance: 'border-emerald-400/20 bg-gradient-to-br from-emerald-400/15 to-slate-950/80 text-white',
        neutral: 'border-amber-400/20 bg-amber-400/10 text-amber-300'
      }[props.tone]
    })

    return () => h('div', {
      class: 'relative min-h-36 overflow-hidden rounded-[2rem] border border-white/10 bg-slate-950/70 p-6 shadow-2xl shadow-slate-950/20'
    }, [
      h('div', { class: `absolute right-5 top-5 flex h-12 w-12 items-center justify-center rounded-2xl border text-lg font-black ${toneClass.value}` }, props.tone === 'expense' ? '-' : props.tone === 'neutral' ? '#' : '+'),
      h('p', { class: 'text-sm font-semibold uppercase tracking-wide text-slate-400' }, props.label),
      h('h3', { class: `mt-5 break-words text-3xl font-black ${props.tone === 'income' ? 'text-emerald-300' : props.tone === 'expense' ? 'text-red-300' : props.tone === 'neutral' ? 'text-amber-300' : 'text-white'}` }, props.value),
      h('p', { class: 'mt-4 text-sm text-slate-500' }, props.caption)
    ])
  }
})

const TransactionDetailsPanel = defineComponent({
  props: {
    transaction: { type: Object, default: null },
    formatPeso: { type: Function, required: true },
    drawer: Boolean
  },
  emits: ['edit', 'delete', 'close'],
  setup(props, { emit }) {
    const field = (label, value) => h('div', { class: 'rounded-2xl border border-slate-800 bg-slate-950/80 p-4' }, [
      h('p', { class: 'text-xs font-semibold uppercase tracking-wide text-slate-500' }, label),
      h('p', { class: 'mt-2 break-words text-sm font-bold text-slate-200' }, value || 'Not provided')
    ])

    return () => {
      const transaction = props.transaction

      if (!transaction) return null

      const isIncome = transaction.type === 'income'
      const image = receiptUrl(transaction)

      return h('div', {
        class: 'space-y-5'
      }, [
        h('div', { class: `relative overflow-hidden rounded-[2rem] border p-5 shadow-2xl ${isIncome ? 'border-emerald-400/20 bg-gradient-to-br from-emerald-500/15 via-slate-950 to-slate-950 shadow-emerald-950/20' : 'border-red-400/20 bg-gradient-to-br from-red-500/15 via-slate-950 to-slate-950 shadow-red-950/20'}` }, [
          h('div', { class: 'flex items-start justify-between gap-4' }, [
            h('div', { class: 'min-w-0' }, [
              h('p', { class: 'text-xs font-semibold uppercase tracking-wide text-emerald-300' }, 'Recorded Transaction'),
              h('h3', { class: 'mt-2 break-words text-2xl font-black text-white' }, transaction.title),
              h('span', { class: `mt-3 inline-flex rounded-full px-3 py-1 text-xs font-bold capitalize ${isIncome ? 'bg-emerald-500/15 text-emerald-200' : 'bg-red-500/15 text-red-200'}` }, transaction.type)
            ]),
            h('button', { class: 'h-10 w-10 rounded-xl bg-slate-950/60 text-slate-300 transition hover:bg-slate-900 hover:text-white', onClick: () => emit('close') }, 'X')
          ]),
          h('p', { class: `mt-8 break-words text-4xl font-black ${isIncome ? 'text-emerald-200' : 'text-red-200'}` }, `${isIncome ? '+' : '-'}${props.formatPeso(transaction.amount)}`)
        ]),

        h('div', { class: 'grid grid-cols-1 gap-3 sm:grid-cols-2' }, [
          field('Type', transaction.type),
          field('Category', transaction.category),
          field('Date', transaction.transaction_date),
          field('Status', 'Recorded'),
          field('Created', transaction.created_at),
          field('Updated', transaction.updated_at)
        ]),

        h('div', { class: 'rounded-2xl border border-slate-800 bg-slate-950/80 p-4' }, [
          h('p', { class: 'text-xs font-semibold uppercase tracking-wide text-slate-500' }, 'Notes'),
          h('p', { class: 'mt-2 whitespace-pre-line text-sm leading-6 text-slate-300' }, transaction.note || 'No notes added.')
        ]),

        image
          ? h('div', { class: 'overflow-hidden rounded-2xl border border-slate-800 bg-slate-950/80' }, [
            h('img', { src: image, alt: 'Receipt preview', class: 'max-h-64 w-full object-cover' })
          ])
          : null,

        h('div', { class: 'grid grid-cols-1 gap-3 sm:grid-cols-2' }, [
          h('button', { class: 'rounded-2xl bg-emerald-500 py-3 font-black text-slate-950 transition hover:bg-emerald-400', onClick: () => emit('edit', transaction) }, 'Edit Transaction'),
          h('button', { class: 'rounded-2xl bg-red-500/10 py-3 font-bold text-red-300 transition hover:bg-red-500 hover:text-white', onClick: () => emit('delete', transaction.id) }, 'Delete Transaction')
        ])
      ])
    }
  }
})

onMounted(() => {
  window.addEventListener('keydown', handleEscape)

  const cachedTransactions = loadDisplayCache('transactions')

  if (cachedTransactions) {
    transactions.value = cachedTransactions
  }

  getTransactions()
})

onBeforeUnmount(() => {
  document.body.classList.remove('overflow-hidden')
  window.removeEventListener('keydown', handleEscape)
})

watch(detailsTransaction, (value) => {
  document.body.classList.toggle('overflow-hidden', Boolean(value))
})
</script>

<style scoped>
.transaction-field {
  width: 100%;
  min-width: 0;
  border-radius: 1rem;
  border: 1px solid rgb(30 41 59);
  background: rgb(2 6 23 / 0.9);
  padding: 0.75rem 1rem;
  color: white;
  outline: none;
  transition: 0.2s ease;
}

.transaction-field::placeholder {
  color: rgb(100 116 139);
}

.transaction-field:focus {
  border-color: rgb(52 211 153);
  box-shadow: 0 0 0 4px rgb(52 211 153 / 0.1);
}

.filter-chip {
  border-radius: 9999px;
  border: 1px solid rgb(30 41 59);
  background: rgb(15 23 42 / 0.75);
  padding: 0.55rem 0.9rem;
  font-size: 0.75rem;
  font-weight: 800;
  color: rgb(203 213 225);
  transition: 0.2s ease;
}

.filter-chip:hover {
  border-color: rgb(52 211 153 / 0.4);
  background: rgb(16 185 129 / 0.1);
  color: rgb(167 243 208);
}

.filter-chip-active {
  border-color: rgb(52 211 153 / 0.55);
  background: rgb(16 185 129 / 0.18);
  color: rgb(167 243 208);
}

.icon-action-button,
.icon-danger-button {
  display: inline-flex;
  height: 2.5rem;
  width: 2.5rem;
  align-items: center;
  justify-content: center;
  border-radius: 0.875rem;
  transition: 0.2s ease;
}

.icon-action-button {
  border: 1px solid rgb(71 85 105 / 0.7);
  background: rgb(30 41 59 / 0.75);
  color: rgb(203 213 225);
}

.icon-action-button:hover {
  border-color: rgb(52 211 153 / 0.45);
  background: rgb(16 185 129 / 0.12);
  color: rgb(110 231 183);
}

.icon-danger-button {
  border: 1px solid rgb(248 113 113 / 0.22);
  background: rgb(239 68 68 / 0.1);
  color: rgb(252 165 165);
}

.icon-danger-button:hover {
  border-color: rgb(248 113 113 / 0.5);
  background: rgb(239 68 68);
  color: white;
}
</style>

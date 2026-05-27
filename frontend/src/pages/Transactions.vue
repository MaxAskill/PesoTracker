<template>
  <main class="min-h-screen bg-slate-950 text-white flex overflow-x-hidden">
    <!-- Sidebar -->
    <Sidebar />

    <!-- Main -->
    <section class="min-w-0 flex-1 p-4 pt-24 sm:p-6 lg:pt-6 overflow-y-auto">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
          <p class="text-slate-400">Manage your</p>
          <h2 class="text-3xl font-bold">Transactions</h2>
        </div>

      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4 mb-8">
      
        <input
          v-model="search"
          type="text"
          placeholder="Search transaction..."
          class="min-w-0 w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 text-white placeholder:text-slate-500"
        />
      
        <select
          v-model="selectedCategory"
          class="min-w-0 w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 text-white"
        >
          <option value="">All Categories</option>
          <option>Food</option>
          <option>Bills</option>
          <option>Transportation</option>
          <option>Shopping</option>
          <option>Salary</option>
          <option>Savings</option>
        </select>
      
        <select
          v-model="selectedType"
          class="min-w-0 w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 text-white"
        >
          <option value="">All Types</option>
          <option value="income">Income</option>
          <option value="expense">Expense</option>
        </select>
      
        <input
          v-model="selectedMonth"
          type="month"
          class="min-w-0 w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 text-white"
        />
      
        <select
          v-model="sortBy"
          class="min-w-0 w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 text-white"
        >
          <option value="latest">Latest</option>
          <option value="oldest">Oldest</option>
          <option value="highest">Highest Amount</option>
          <option value="lowest">Lowest Amount</option>
        </select>
      
      </div>
      <!-- Table -->
      <div class="hidden lg:block bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden">
        <div class="grid grid-cols-6 gap-4 px-6 py-4 border-b border-slate-800 text-slate-400 text-sm font-semibold">
          <span>Title</span>
          <span>Category</span>
          <span>Type</span>
          <span>Amount</span>
          <span>Date</span>
          <span class="text-center">Actions</span>
        </div>

        <div v-if="filteredTransactions.length">
          <div
            v-for="transaction in filteredTransactions"
            :key="transaction.id"
            class="grid grid-cols-6 gap-4 px-6 py-5 border-b border-slate-800 items-center"
          >
            <div>
              <p class="font-semibold">{{ transaction.title }}</p>

              <p v-if="transaction.note" class="text-sm text-slate-500 truncate">
                {{ transaction.note }}
              </p>
            </div>

            <span class="text-slate-300">
              {{ transaction.category }}
            </span>

            <span
              class="px-3 py-1 rounded-full text-xs font-semibold w-fit"
              :class="
                transaction.type === 'income'
                  ? 'bg-emerald-500/10 text-emerald-400'
                  : 'bg-red-500/10 text-red-400'
              "
            >
              {{ transaction.type }}
            </span>

            <span
              class="font-bold"
              :class="
                transaction.type === 'income'
                  ? 'text-emerald-400'
                  : 'text-red-400'
              "
            >
              {{ transaction.type === 'income' ? '+' : '-' }}
              ₱{{ transaction.amount }}
            </span>

            <span class="text-slate-400">
              {{ transaction.transaction_date }}
            </span>

            <div class="flex items-center justify-center gap-3">
              <button
                @click="editTransaction(transaction)"
                class="bg-slate-800 hover:bg-slate-700 px-4 py-2 rounded-xl text-sm"
              >
                Edit
              </button>

              <button
                @click="deleteTransaction(transaction.id)"
                class="bg-red-500/10 hover:bg-red-500 text-red-400 hover:text-white px-4 py-2 rounded-xl text-sm transition"
              >
                Delete
              </button>
            </div>
          </div>
        </div>

        <div v-else class="p-10 text-center text-slate-500">
          No transactions found.
        </div>
      </div>
      <div class="lg:hidden space-y-4">
      
        <div
          v-for="transaction in filteredTransactions"
          :key="transaction.id"
          class="bg-slate-900 border border-slate-800 rounded-3xl p-4 sm:p-5"
        >
      
          <!-- Top -->
          <div class="flex items-start justify-between gap-3 mb-5">
      
            <div class="min-w-0">
              <h3 class="text-lg font-bold text-white break-words">
                {{ transaction.title }}
              </h3>
      
              <p class="text-slate-400 text-sm mt-1">
                {{ transaction.transaction_date }}
              </p>
            </div>
      
            <div
              class="shrink-0 px-3 py-1 rounded-xl text-sm font-semibold"
              :class="
                transaction.type === 'income'
                  ? 'bg-emerald-500/10 text-emerald-400'
                  : 'bg-red-500/10 text-red-400'
              "
            >
              {{ transaction.type }}
            </div>
      
          </div>
      
          <!-- Middle -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
      
            <div class="bg-slate-950 border border-slate-800 rounded-2xl p-4">
              <p class="text-slate-500 text-xs mb-2">
                Category
              </p>
      
              <h4 class="font-semibold break-words">
                {{ transaction.category }}
              </h4>
            </div>
      
            <div class="bg-slate-950 border border-slate-800 rounded-2xl p-4">
              <p class="text-slate-500 text-xs mb-2">
                Amount
              </p>
      
              <h4
                class="font-bold"
                :class="
                  transaction.type === 'income'
                    ? 'text-emerald-400'
                    : 'text-red-400'
                "
              >
                ₱{{ Number(transaction.amount).toLocaleString() }}
              </h4>
            </div>
      
          </div>
      
          <!-- Actions -->
          <div class="flex flex-col sm:flex-row gap-3">
      
            <button
              @click="editTransaction(transaction)"
              class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white py-3 rounded-2xl font-semibold transition"
            >
              Edit
            </button>
      
            <button
              @click="deleteTransaction(transaction.id)"
              class="flex-1 bg-red-500 hover:bg-red-600 text-white py-3 rounded-2xl font-semibold transition"
            >
              Delete
            </button>
      
          </div>
      
        </div>
      
      </div>
    </section>
    <TransactionModal
      :show="showEditModal"
      :transaction="selectedTransaction"
      @close="showEditModal = false"
      @saved="getTransactions"
    />
  </main>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import TransactionModal from '../components/TransactionModal.vue'
import Sidebar from '../components/Sidebar.vue'

const showEditModal = ref(false)
const selectedTransaction = ref(null)

const router = useRouter()

const transactions = ref([])
const search = ref('')
const filterType = ref('')


const selectedCategory = ref('')
const selectedType = ref('')
const selectedMonth = ref('')
const sortBy = ref('latest')

const getTransactions = async () => {
  try {
    const response = await api.get('/transactions')

    transactions.value = response.data
  } catch (error) {
    console.error(error)
  }
}

const deleteTransaction = async (id) => {
  if (!confirm('Delete this transaction?')) return

  try {
    await api.delete(`/transactions/${id}`)

    transactions.value = transactions.value.filter(
      transaction => transaction.id !== id
    )
  } catch (error) {
    console.error(error)
  }
}

// const filteredTransactions = computed(() => {
//   return transactions.value.filter(transaction => {
//     const matchesSearch =
//       transaction.title.toLowerCase().includes(search.value.toLowerCase()) ||
//       transaction.category.toLowerCase().includes(search.value.toLowerCase())

//     const matchesType =
//       !filterType.value || transaction.type === filterType.value

//     return matchesSearch && matchesType
//   })
// })


const editTransaction = (transaction) => {
  selectedTransaction.value = transaction
  showEditModal.value = true
}


const filteredTransactions = computed(() => {
  let data = [...transactions.value]

  // Search
  if (search.value) {
    data = data.filter(transaction =>
      transaction.title
        .toLowerCase()
        .includes(search.value.toLowerCase())
    )
  }

  // Category
  if (selectedCategory.value) {
    data = data.filter(
      transaction =>
        transaction.category === selectedCategory.value
    )
  }

  // Type
  if (selectedType.value) {
    data = data.filter(
      transaction =>
        transaction.type === selectedType.value
    )
  }

  // Month
  if (selectedMonth.value) {
    data = data.filter(transaction =>
      transaction.transaction_date.startsWith(selectedMonth.value)
    )
  }

  // Sorting
  switch (sortBy.value) {

    case 'latest':
      data.sort((a, b) =>
        new Date(b.transaction_date) -
        new Date(a.transaction_date)
      )
      break

    case 'oldest':
      data.sort((a, b) =>
        new Date(a.transaction_date) -
        new Date(b.transaction_date)
      )
      break

    case 'highest':
      data.sort((a, b) =>
        b.amount - a.amount
      )
      break

    case 'lowest':
      data.sort((a, b) =>
        a.amount - b.amount
      )
      break
  }

  return data
})

onMounted(() => {
  getTransactions()
})
</script>

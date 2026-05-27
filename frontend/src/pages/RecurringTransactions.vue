<template>
  <main class="min-h-screen bg-slate-950 text-white flex">
    <Sidebar />

    <section class="flex-1 p-6 pt-24 lg:pt-6 overflow-y-auto">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
          <p class="text-slate-400">Manage your</p>
          <h2 class="text-3xl font-bold">Recurring Transactions</h2>
        </div>

        <button
          @click="showModal = true"
          class="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-2xl font-semibold transition"
        >
          + Add Recurring
        </button>
      </div>

      <!-- Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <div
          v-for="transaction in recurringTransactions"
          :key="transaction.id"
          class="bg-slate-900 border border-slate-800 rounded-3xl p-6"
        >
          <div class="flex items-start justify-between mb-5">
            <div>
              <p class="text-slate-400 text-sm">
                {{ transaction.next_due_date }}
              </p>

              <h3 class="text-2xl font-bold mt-1">
                {{ transaction.title }}
              </h3>

              <p class="text-slate-500 text-sm mt-2">
                {{ transaction.category }}
              </p>
            </div>

            <button
              @click="deleteRecurring(transaction.id)"
              class="bg-red-500/10 hover:bg-red-500 text-red-400 hover:text-white px-3 py-2 rounded-xl text-sm transition"
            >
              Delete
            </button>
          </div>

          <div class="flex items-center justify-between mb-5">
            <span
              class="px-4 py-2 rounded-xl text-sm font-semibold"
              :class="
                transaction.type === 'income'
                  ? 'bg-emerald-500/10 text-emerald-400'
                  : 'bg-red-500/10 text-red-400'
              "
            >
              {{ transaction.type }}
            </span>

            <span
              class="bg-slate-800 text-slate-300 px-4 py-2 rounded-xl text-sm font-semibold capitalize"
            >
              {{ transaction.frequency }}
            </span>
          </div>

          <div class="bg-slate-950 border border-slate-800 rounded-2xl p-5 mb-5">
            <p class="text-slate-500 text-sm">Amount</p>

            <p class="text-3xl font-bold mt-2">
              {{ formatPeso(transaction.amount) }}
            </p>
          </div>

          <div class="flex items-center justify-between">
            <span class="text-slate-400 text-sm">
              Auto Create
            </span>

            <div
              class="w-12 h-7 rounded-full flex items-center px-1 transition"
              :class="
                transaction.auto_create
                  ? 'bg-emerald-500'
                  : 'bg-slate-700'
              "
            >
              <div
                class="w-5 h-5 bg-white rounded-full transition"
                :class="
                  transaction.auto_create
                    ? 'translate-x-5'
                    : ''
                "
              ></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty -->
      <div
        v-if="!recurringTransactions.length"
        class="h-72 flex items-center justify-center text-slate-500"
      >
        No recurring transactions yet.
      </div>
    </section>

    <!-- Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 px-4"
    >
      <div class="w-full max-w-xl bg-slate-900 border border-slate-800 rounded-3xl p-8">
        <div class="flex items-center justify-between mb-8">
          <div>
            <p class="text-emerald-400 font-semibold text-sm">
              PesoTracker
            </p>

            <h2 class="text-3xl font-bold text-white">
              Add Recurring Transaction
            </h2>
          </div>

          <button
            @click="showModal = false"
            class="w-10 h-10 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300"
          >
            ✕
          </button>
        </div>

        <form class="space-y-5" @submit.prevent="saveRecurring">
          <input
            v-model="form.title"
            type="text"
            required
            placeholder="Transaction Title"
            class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition"
          />

          <select
            v-model="form.type"
            required
            class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition"
          >
            <option disabled value="">Select Type</option>
            <option value="income">Income</option>
            <option value="expense">Expense</option>
          </select>

          <select
            v-model="form.category"
            required
            class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition"
          >
            <option disabled value="">Select Category</option>
            <option>Food</option>
            <option>Bills</option>
            <option>Transportation</option>
            <option>Shopping</option>
            <option>Salary</option>
            <option>Savings</option>
          </select>

          <input
            v-model="form.amount"
            type="number"
            required
            min="1"
            placeholder="Amount"
            class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition"
          />

          <select
            v-model="form.frequency"
            required
            class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition"
          >
            <option disabled value="">Select Frequency</option>
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
            <option value="yearly">Yearly</option>
          </select>

          <input
            v-model="form.next_due_date"
            type="date"
            required
            class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition"
          />

          <textarea
            v-model="form.note"
            rows="3"
            placeholder="Optional note..."
            class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition resize-none"
          ></textarea>

          <button
            type="submit"
            class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-emerald-500/20 transition"
          >
            Save Recurring Transaction
          </button>
        </form>
      </div>
    </div>
  </main>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import Sidebar from '../components/Sidebar.vue'
import api from '../services/api'
import { formatPeso } from '../utils/currency'

const recurringTransactions = ref([])
const showModal = ref(false)

const form = reactive({
  title: '',
  type: '',
  category: '',
  amount: '',
  frequency: '',
  next_due_date: '',
  note: ''
})

const getRecurringTransactions = async () => {
  try {
    const response = await api.get('/recurring-transactions')
    recurringTransactions.value = response.data
  } catch (error) {
    console.error(error)
  }
}

const saveRecurring = async () => {
  try {
    await api.post('/recurring-transactions', form)

    showModal.value = false

    Object.keys(form).forEach(key => {
      form[key] = ''
    })

    getRecurringTransactions()
  } catch (error) {
    console.error(error.response?.data || error)
  }
}

const deleteRecurring = async (id) => {
  if (!confirm('Delete this recurring transaction?')) return

  try {
    await api.delete(`/recurring-transactions/${id}`)

    recurringTransactions.value =
      recurringTransactions.value.filter(
        transaction => transaction.id !== id
      )
  } catch (error) {
    console.error(error)
  }
}

onMounted(() => {
  getRecurringTransactions()
})
</script>


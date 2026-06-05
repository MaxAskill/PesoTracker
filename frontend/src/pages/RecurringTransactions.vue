<template>
  <main class="magic-bg min-h-screen text-white flex">
    <Sidebar />

    <section class="min-w-0 flex-1 p-4 pt-24 sm:p-6 lg:pt-6 overflow-y-auto">
      <div class="mb-8 flex flex-col gap-5 rounded-[2rem] border border-white/10 bg-slate-950/55 p-6 shadow-2xl shadow-slate-950/30 backdrop-blur md:flex-row md:items-center md:justify-between">
        <div>
          <p class="text-sm font-semibold uppercase tracking-wide text-emerald-300">Automation</p>
          <h2 class="mt-2 text-3xl font-black md:text-4xl">Recurring</h2>
          <p class="mt-2 max-w-2xl text-sm text-slate-400">
            Manage repeating income and expenses.
          </p>
        </div>

        <button @click="showModal = true" class="rounded-2xl border border-emerald-300/50 bg-gradient-to-r from-emerald-400 to-teal-300 px-6 py-3 font-black text-slate-950 shadow-lg shadow-emerald-500/20 transition hover:from-emerald-300 hover:to-teal-200">
          Add Recurring
        </button>
      </div>

      <div v-if="isLoading" class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        <div v-for="index in 3" :key="index" class="rounded-[2rem] border border-white/10 bg-slate-950/70 p-6">
          <div class="h-5 w-40 rounded-full bg-slate-800 animate-pulse"></div>
          <div class="mt-6 h-32 rounded-3xl bg-slate-800/50 animate-pulse"></div>
        </div>
      </div>

      <div v-else-if="recurringTransactions.length" class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        <div v-for="transaction in recurringTransactions" :key="transaction.id" class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-slate-950/70 p-6 shadow-2xl shadow-slate-950/20">
          <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-emerald-400/10 blur-2xl"></div>
          <div class="relative mb-5 flex items-start justify-between gap-4">
            <div>
              <p class="text-sm font-semibold uppercase tracking-wide text-slate-500">Next: {{ transaction.next_due_date }}</p>
              <h3 class="mt-1 text-2xl font-black">{{ transaction.title }}</h3>
              <p class="mt-2 text-sm text-slate-500">{{ transaction.category }}</p>
            </div>
            <button @click="deleteRecurring(transaction.id)" class="rounded-xl bg-red-500/10 px-3 py-2 text-sm text-red-300 transition hover:bg-red-500 hover:text-white">Delete</button>
          </div>

          <div class="relative mb-5 flex items-center justify-between">
            <span class="rounded-xl px-4 py-2 text-sm font-bold" :class="typeBadgeClass(transaction.type)">{{ transaction.type }}</span>
            <span class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-bold capitalize text-slate-300">{{ transaction.frequency }}</span>
          </div>

          <div class="relative mb-5 rounded-3xl border border-white/10 bg-slate-950/80 p-5">
            <p class="text-sm text-slate-500">Amount</p>
            <p class="mt-2 text-3xl font-black" :class="transaction.type === 'income' ? 'text-emerald-300' : 'text-red-300'">
              {{ formatPeso(transaction.amount) }}
            </p>
          </div>

          <div class="relative flex items-center justify-between">
            <span class="text-sm text-slate-400">Auto Create</span>
            <div class="flex h-7 w-12 items-center rounded-full px-1 transition" :class="transaction.auto_create ? 'bg-emerald-500' : 'bg-slate-700'">
              <div class="h-5 w-5 rounded-full bg-white transition" :class="transaction.auto_create ? 'translate-x-5' : ''"></div>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="flex min-h-72 items-center justify-center rounded-[2rem] border border-dashed border-slate-800 bg-slate-950/60 p-10 text-center text-slate-500">
        No recurring records yet. Add recurring income or expenses to automate tracking.
      </div>
    </section>

    <AppModal
      :show="showModal"
      title="Add Recurring"
      subtitle="Automate repeating income and expenses."
      size="lg"
      @close="showModal = false"
    >
      <form class="space-y-5" @submit.prevent="saveRecurring">
        <input v-model="form.title" type="text" required placeholder="Transaction title" class="form-field" />
        <AppSelect
          v-model="form.type"
          :options="typeOptions"
          placeholder="Select Type"
        />
        <AppSelect
          v-model="form.category"
          :options="categoryOptions"
          placeholder="Select Category"
        />
        <AppMoneyInput v-model="form.amount" placeholder="Amount" />
        <AppSelect
          v-model="form.frequency"
          :options="frequencyOptions"
          placeholder="Select Frequency"
        />
        <AppDatePicker v-model="form.next_due_date" placeholder="Select next due date" />
        <textarea v-model="form.note" rows="3" placeholder="Optional note..." class="form-field resize-none"></textarea>
        <button type="submit" class="w-full rounded-xl bg-emerald-500 py-3.5 font-black text-slate-950 shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-400">
          Save Recurring Transaction
        </button>
      </form>
    </AppModal>
  </main>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import AppDatePicker from '../components/AppDatePicker.vue'
import AppModal from '../components/AppModal.vue'
import AppMoneyInput from '../components/AppMoneyInput.vue'
import AppSelect from '../components/AppSelect.vue'
import Sidebar from '../components/Sidebar.vue'
import api, { isCanceledRequest } from '../services/api'
import { formatPeso } from '../utils/currency'
import { useAuth } from '../composables/useAuth'

const { isAuthenticated } = useAuth()

const recurringTransactions = ref([])
const isLoading = ref(false)
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

const typeOptions = [
  { label: 'Income', value: 'income' },
  { label: 'Expense', value: 'expense' }
]

const categoryOptions = [
  { label: 'Food', value: 'Food' },
  { label: 'Bills', value: 'Bills' },
  { label: 'Transportation', value: 'Transportation' },
  { label: 'Shopping', value: 'Shopping' },
  { label: 'Salary', value: 'Salary' },
  { label: 'Savings', value: 'Savings' }
]

const frequencyOptions = [
  { label: 'Daily', value: 'daily' },
  { label: 'Weekly', value: 'weekly' },
  { label: 'Monthly', value: 'monthly' },
  { label: 'Yearly', value: 'yearly' }
]

const typeBadgeClass = (type) => {
  return type === 'income'
    ? 'bg-emerald-500/10 text-emerald-300'
    : 'bg-red-500/10 text-red-300'
}

const getRecurringTransactions = async () => {
  if (!isAuthenticated.value) return

  isLoading.value = !recurringTransactions.value.length
  try {
    const response = await api.get('/recurring-transactions')
    recurringTransactions.value = response.data
  } catch (error) {
    if (isCanceledRequest(error)) return
    console.error(error)
  } finally {
    isLoading.value = false
  }
}

const saveRecurring = async () => {
  if (!isAuthenticated.value) return

  try {
    await api.post('/recurring-transactions', form)
    showModal.value = false
    Object.keys(form).forEach(key => {
      form[key] = ''
    })
    getRecurringTransactions()
  } catch (error) {
    if (isCanceledRequest(error)) return
    console.error(error.response?.data || error)
  }
}

const deleteRecurring = async (id) => {
  if (!isAuthenticated.value) return
  if (!confirm('Delete this recurring transaction?')) return
  try {
    await api.delete(`/recurring-transactions/${id}`)
    recurringTransactions.value = recurringTransactions.value.filter(transaction => transaction.id !== id)
  } catch (error) {
    if (isCanceledRequest(error)) return
    console.error(error)
  }
}

onMounted(() => {
  if (isAuthenticated.value) {
    getRecurringTransactions()
  }
})
</script>

<style scoped>
.form-field {
  width: 100%;
  border-radius: 0.75rem;
  border: 1px solid rgb(30 41 59);
  background: rgb(2 6 23);
  padding: 0.75rem 1rem;
  color: white;
  outline: none;
  transition: 0.2s ease;
}

.form-field::placeholder {
  color: rgb(100 116 139);
}

.form-field:focus {
  border-color: rgb(16 185 129);
  box-shadow: 0 0 0 4px rgb(16 185 129 / 0.1);
}
</style>

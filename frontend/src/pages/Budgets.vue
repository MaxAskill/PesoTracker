<template>
  <main class="min-h-screen bg-slate-950 text-white flex">
    <!-- Sidebar -->
    <Sidebar />
    <!-- Main -->
    <section class="flex-1 p-6 pt-24 lg:pt-6 overflow-y-auto">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
          <p class="text-slate-400">Manage your</p>
          <h2 class="text-3xl font-bold">Budgets</h2>
        </div>

        <button
          @click="showModal = true"
          class="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-2xl font-semibold transition"
        >
          + Add Budget
        </button>
      </div>

      <!-- Budget Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <div
          v-for="budget in budgets"
          :key="budget.id"
          class="bg-slate-900 border border-slate-800 rounded-3xl p-6"
        >
          <div class="flex items-center justify-between mb-5">
            <div>
              <p class="text-slate-400 text-sm">
                {{ budget.month }}
              </p>

              <h3 class="text-2xl font-bold mt-1">
                {{ budget.category }}
              </h3>
            </div>

            <button
              @click="deleteBudget(budget.id)"
              class="bg-red-500/10 hover:bg-red-500 text-red-400 hover:text-white px-3 py-2 rounded-xl text-sm transition"
            >
              Delete
            </button>
          </div>

          <div class="mb-4">
            <div class="flex justify-between text-sm mb-2">
              <span class="text-slate-400">Budget Limit</span>
              <span>₱{{ budget.amount }}</span>
            </div>

            <div class="h-3 bg-slate-800 rounded-full overflow-hidden">
              <div
                class="h-3 bg-emerald-500 rounded-full"
                style="width: 65%"
              ></div>
            </div>
          </div>

          <div class="flex justify-between text-sm mt-4">
            <span class="text-slate-400">Remaining</span>
            <span class="text-emerald-400 font-semibold">
              ₱{{ budget.amount }}
            </span>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div
        v-if="!budgets.length"
        class="h-72 flex items-center justify-center text-slate-500"
      >
        No budgets yet.
      </div>
    </section>

    <!-- Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 px-4"
    >
      <div class="w-full max-w-lg bg-slate-900 border border-slate-800 rounded-3xl p-8">
        <div class="flex items-center justify-between mb-8">
          <div>
            <p class="text-emerald-400 font-semibold text-sm">
              PesoTracker
            </p>

            <h2 class="text-3xl font-bold text-white">
              Add Budget
            </h2>
          </div>

          <button
            @click="showModal = false"
            class="w-10 h-10 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300"
          >
            ✕
          </button>
        </div>

        <form class="space-y-5" @submit.prevent="saveBudget">
          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">
              Category
            </label>

            <select
              v-model="form.category"
              class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition"
            >
              <option disabled value="">Select Category</option>
              <option value="Food">Food</option>
              <option value="Transportation">Transportation</option>
              <option value="Bills">Bills</option>
              <option value="Shopping">Shopping</option>
              <option value="Utilities">Utilities</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">
              Budget Amount
            </label>

            <input
              v-model="form.amount"
              type="number"
              placeholder="5000"
              class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition"
            />
          </div>

          <div>
            <div>
              <label class="block text-sm font-semibold text-slate-300 mb-2">
                Month
              </label>
            
              <select
                v-model="form.month"
                required
                class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition"
              >
                <option disabled value="">Select Month</option>
            
                <option
                  v-for="month in months"
                  :key="month.value"
                  :value="month.value"
                >
                  {{ month.label }}
                </option>
              </select>
            </div>
          </div>

          <button
            type="submit"
            class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-emerald-500/20 transition"
          >
            Save Budget
          </button>
        </form>
      </div>
    </div>
  </main>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import Sidebar from '../components/Sidebar.vue'

const router = useRouter()

const budgets = ref([])
const showModal = ref(false)

const form = reactive({
  category: '',
  amount: '',
  month: ''
})


const months = Array.from({ length: 12 }, (_, index) => {
  const date = new Date(new Date().getFullYear(), index)

  return {
    value: `${date.getFullYear()}-${String(index + 1).padStart(2, '0')}`,
    label: date.toLocaleString('en-US', {
      month: 'long',
      year: 'numeric'
    })
  }
})

const getBudgets = async () => {
  try {
    const response = await api.get('/budgets')

    budgets.value = response.data
  } catch (error) {
    console.error(error)
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

    budgets.value = budgets.value.filter(
      budget => budget.id !== id
    )
  } catch (error) {
    console.error(error)
  }
}

onMounted(() => {
  getBudgets()
})
</script>
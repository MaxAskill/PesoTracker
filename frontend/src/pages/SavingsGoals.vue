<template>
  <main class="min-h-screen bg-slate-950 text-white flex">
    <!-- Sidebar -->
    <Sidebar />

    <!-- Main -->
    <section class="flex-1 p-6 pt-24 lg:pt-6 overflow-y-auto">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
          <p class="text-slate-400">Track your</p>
          <h2 class="text-3xl font-bold">Savings Goals</h2>
        </div>

        <button
          @click="showModal = true"
          class="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-2xl font-semibold transition"
        >
          + Add Goal
        </button>
      </div>

      <!-- Goal Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <div
          v-for="goal in goals"
          :key="goal.id"
          class="bg-slate-900 border border-slate-800 rounded-3xl p-6"
        >
          <div class="flex items-start justify-between gap-4 mb-5">
            <div>
              <p class="text-slate-400 text-sm">{{ goal.deadline }}</p>
              <h3 class="text-2xl font-bold mt-1">{{ goal.title }}</h3>
              <p v-if="goal.description" class="text-slate-500 text-sm mt-2">
                {{ goal.description }}
              </p>
            </div>

            <button
              @click="deleteGoal(goal.id)"
              class="bg-red-500/10 hover:bg-red-500 text-red-400 hover:text-white px-3 py-2 rounded-xl text-sm transition"
            >
              Delete
            </button>
          </div>

          <div class="mb-5">
            <div class="flex justify-between text-sm mb-2">
              <span class="text-slate-400">Progress</span>
              <span>{{ progress(goal) }}%</span>
            </div>

            <div class="h-3 bg-slate-800 rounded-full overflow-hidden">
              <div
                class="h-3 bg-emerald-500 rounded-full"
                :style="{ width: progress(goal) + '%' }"
              ></div>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4 mb-5">
            <div class="bg-slate-950 border border-slate-800 rounded-2xl p-4">
              <p class="text-slate-500 text-sm">Saved</p>
              <p class="text-emerald-400 font-bold mt-1">
                {{ formatPeso(goal.saved_amount) }}
              </p>
            </div>

            <div class="bg-slate-950 border border-slate-800 rounded-2xl p-4">
              <p class="text-slate-500 text-sm">Target</p>
              <p class="font-bold mt-1">
                {{ formatPeso(goal.target_amount) }}
              </p>
            </div>
          </div>

          <form @submit.prevent="addContribution(goal)" class="flex gap-3">
            <input
              v-model="contributions[goal.id]"
              type="number"
              min="1"
              placeholder="Add ₱"
              class="flex-1 px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition"
            />

            <button
              type="submit"
              class="bg-emerald-500 hover:bg-emerald-600 px-5 py-3 rounded-xl font-bold transition"
            >
              Add
            </button>
          </form>
        </div>
      </div>

      <div v-if="!goals.length" class="h-72 flex items-center justify-center text-slate-500">
        No savings goals yet.
      </div>
    </section>

    <!-- Add Goal Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 px-4"
    >
      <div class="w-full max-w-lg bg-slate-900 border border-slate-800 rounded-3xl p-8">
        <div class="flex items-center justify-between mb-8">
          <div>
            <p class="text-emerald-400 font-semibold text-sm">PesoTracker</p>
            <h2 class="text-3xl font-bold text-white">Add Savings Goal</h2>
          </div>

          <button
            @click="showModal = false"
            class="w-10 h-10 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300"
          >
            ✕
          </button>
        </div>

        <form class="space-y-5" @submit.prevent="saveGoal">
          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">
              Goal Title
            </label>

            <input
              v-model="form.title"
              type="text"
              required
              placeholder="e.g. Laptop Fund"
              class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition"
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">
              Description
            </label>

            <textarea
              v-model="form.description"
              rows="3"
              placeholder="Optional description..."
              class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition resize-none"
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">
              Target Amount
            </label>

            <input
              v-model="form.target_amount"
              type="number"
              required
              min="1"
              placeholder="60000"
              class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition"
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">
              Deadline
            </label>

            <input
              v-model="form.deadline"
              type="date"
              required
              class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition"
            />
          </div>

          <button
            type="submit"
            class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-emerald-500/20 transition"
          >
            Save Goal
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
import { formatPeso } from '../utils/currency'

const router = useRouter()

const goals = ref([])
const showModal = ref(false)
const contributions = reactive({})

const form = reactive({
  title: '',
  description: '',
  target_amount: '',
  deadline: ''
})

const progress = (goal) => {
  const saved = Number(goal.saved_amount)
  const target = Number(goal.target_amount)

  if (!target) return 0

  return Math.min(Math.round((saved / target) * 100), 100)
}

const getGoals = async () => {
  try {
    const response = await api.get('/savings-goals')
    goals.value = response.data
  } catch (error) {
    console.error(error)
  }
}

const saveGoal = async () => {
  try {
    await api.post('/savings-goals', form)

    showModal.value = false

    form.title = ''
    form.description = ''
    form.target_amount = ''
    form.deadline = ''

    getGoals()
  } catch (error) {
    console.error(error.response?.data || error)
  }
}

const addContribution = async (goal) => {
  const amount = Number(contributions[goal.id])

  if (!amount || amount <= 0) return

  try {
    await api.put(`/savings-goals/${goal.id}`, {
      saved_amount: Number(goal.saved_amount) + amount
    })

    contributions[goal.id] = ''
    getGoals()
  } catch (error) {
    console.error(error.response?.data || error)
  }
}

const deleteGoal = async (id) => {
  if (!confirm('Delete this savings goal?')) return

  try {
    await api.delete(`/savings-goals/${id}`)
    goals.value = goals.value.filter(goal => goal.id !== id)
  } catch (error) {
    console.error(error)
  }
}

onMounted(() => {
  getGoals()
})
</script>

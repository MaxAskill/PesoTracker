<template>
  <main class="magic-bg min-h-screen text-white flex">
    <Sidebar />

    <section class="min-w-0 flex-1 p-4 pt-24 sm:p-6 lg:pt-6 overflow-y-auto">
      <div class="mb-8 flex flex-col gap-5 rounded-[2rem] border border-white/10 bg-slate-950/55 p-6 shadow-2xl shadow-slate-950/30 backdrop-blur md:flex-row md:items-center md:justify-between">
        <div>
          <p class="text-sm font-semibold uppercase tracking-wide text-emerald-300">Future Fund</p>
          <h2 class="mt-2 text-3xl font-black md:text-4xl">Savings Goals</h2>
          <p class="mt-2 max-w-2xl text-sm text-slate-400">
            Track your progress toward your financial goals.
          </p>
        </div>

        <button @click="showModal = true" class="rounded-2xl border border-emerald-300/50 bg-gradient-to-r from-emerald-400 to-teal-300 px-6 py-3 font-black text-slate-950 shadow-lg shadow-emerald-500/20 transition hover:from-emerald-300 hover:to-teal-200">
          Add Goal
        </button>
      </div>

      <div v-if="isLoading" class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        <div v-for="index in 3" :key="index" class="rounded-[2rem] border border-white/10 bg-slate-950/70 p-6">
          <div class="h-5 w-40 rounded-full bg-slate-800 animate-pulse"></div>
          <div class="mt-6 h-32 rounded-3xl bg-slate-800/50 animate-pulse"></div>
        </div>
      </div>

      <div v-else-if="goals.length" class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        <div v-for="goal in goals" :key="goal.id" class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-slate-950/70 p-6 shadow-2xl shadow-slate-950/20">
          <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-emerald-400/10 blur-2xl"></div>
          <div class="relative mb-5 flex items-start justify-between gap-4">
            <div>
              <p class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ goal.deadline }}</p>
              <h3 class="mt-1 text-2xl font-black">{{ goal.title }}</h3>
              <p v-if="goal.description" class="mt-2 text-sm text-slate-500">{{ goal.description }}</p>
            </div>

            <span class="rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-300">
              {{ goal.status || 'active' }}
            </span>
          </div>

          <div class="relative mb-5 rounded-3xl border border-white/10 bg-slate-950/80 p-5">
            <div class="mb-3 flex justify-between text-sm">
              <span class="text-slate-400">Progress</span>
              <span class="font-black text-emerald-300">{{ progress(goal) }}%</span>
            </div>
            <div class="h-3 overflow-hidden rounded-full bg-slate-800">
              <div class="h-3 rounded-full bg-emerald-400 transition-all" :style="{ width: progress(goal) + '%' }"></div>
            </div>
          </div>

          <div class="mb-5 grid grid-cols-2 gap-4">
            <div class="rounded-2xl border border-slate-800 bg-slate-950 p-4">
              <p class="text-sm text-slate-500">Saved</p>
              <p class="mt-1 font-black text-emerald-300">{{ formatPeso(goal.saved_amount) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-800 bg-slate-950 p-4">
              <p class="text-sm text-slate-500">Target</p>
              <p class="mt-1 font-black">{{ formatPeso(goal.target_amount) }}</p>
            </div>
          </div>

          <form @submit.prevent="addContribution(goal)" class="flex gap-3">
            <AppMoneyInput v-model="contributions[goal.id]" placeholder="Add amount" class="min-w-0 flex-1" />
            <button type="submit" class="rounded-xl bg-emerald-500 px-5 py-3 font-black text-slate-950 transition hover:bg-emerald-400">Add</button>
          </form>

          <button @click="deleteGoal(goal.id)" class="mt-4 w-full rounded-xl bg-red-500/10 py-3 font-bold text-red-300 transition hover:bg-red-500 hover:text-white">
            Delete Goal
          </button>
        </div>
      </div>

      <div v-else class="flex min-h-72 items-center justify-center rounded-[2rem] border border-dashed border-slate-800 bg-slate-950/60 p-10 text-center text-slate-500">
        No savings goals yet. Create a goal and start building your future.
      </div>
    </section>

    <AppModal
      :show="showModal"
      title="Add Savings Goal"
      subtitle="Create a target and track your progress over time."
      @close="showModal = false"
    >
      <form class="space-y-5" @submit.prevent="saveGoal">
        <input v-model="form.title" type="text" required placeholder="Goal title" class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-500 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" />
        <textarea v-model="form.description" rows="3" placeholder="Optional description..." class="w-full resize-none rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-500 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"></textarea>
        <AppMoneyInput v-model="form.target_amount" placeholder="Target amount" />
        <AppDatePicker v-model="form.deadline" placeholder="Select deadline" />
        <button type="submit" class="w-full rounded-xl bg-emerald-500 py-3.5 font-black text-slate-950 shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-400">
          Save Goal
        </button>
      </form>
    </AppModal>
  </main>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import api, { isCanceledRequest } from '../services/api'
import AppDatePicker from '../components/AppDatePicker.vue'
import AppModal from '../components/AppModal.vue'
import AppMoneyInput from '../components/AppMoneyInput.vue'
import Sidebar from '../components/Sidebar.vue'
import { formatPeso } from '../utils/currency'
import { loadDisplayCache, saveDisplayCache } from '../services/preload'
import { useAuth } from '../composables/useAuth'

const { isAuthenticated } = useAuth()

const goals = ref([])
const isLoading = ref(false)
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
  if (!isAuthenticated.value) return

  isLoading.value = !goals.value.length

  try {
    const response = await api.get('/savings-goals')
    goals.value = response.data
    saveDisplayCache('savings-goals', response.data)
  } catch (error) {
    if (isCanceledRequest(error)) return
    console.error(error)
  } finally {
    isLoading.value = false
  }
}

const saveGoal = async () => {
  if (!isAuthenticated.value) return

  try {
    await api.post('/savings-goals', form)
    showModal.value = false
    form.title = ''
    form.description = ''
    form.target_amount = ''
    form.deadline = ''
    getGoals()
  } catch (error) {
    if (isCanceledRequest(error)) return
    console.error(error.response?.data || error)
  }
}

const addContribution = async (goal) => {
  if (!isAuthenticated.value) return

  const amount = Number(contributions[goal.id])
  if (!amount || amount <= 0) return

  try {
    await api.put(`/savings-goals/${goal.id}`, {
      saved_amount: Number(goal.saved_amount) + amount
    })
    contributions[goal.id] = ''
    getGoals()
  } catch (error) {
    if (isCanceledRequest(error)) return
    console.error(error.response?.data || error)
  }
}

const deleteGoal = async (id) => {
  if (!isAuthenticated.value) return
  if (!confirm('Delete this savings goal?')) return
  try {
    await api.delete(`/savings-goals/${id}`)
    goals.value = goals.value.filter(goal => goal.id !== id)
    saveDisplayCache('savings-goals', goals.value)
  } catch (error) {
    if (isCanceledRequest(error)) return
    console.error(error)
  }
}

onMounted(() => {
  const cachedGoals = loadDisplayCache('savings-goals')
  if (cachedGoals) goals.value = cachedGoals
  if (isAuthenticated.value) {
    getGoals()
  }
})
</script>

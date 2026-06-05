<template>
  <main class="magic-bg min-h-screen text-white flex">
    <Sidebar />

    <section class="min-w-0 flex-1 p-4 pt-24 sm:p-6 lg:pt-6 overflow-y-auto">
      <div class="mb-8 rounded-[2rem] border border-white/10 bg-slate-950/55 p-6 shadow-2xl shadow-slate-950/30 backdrop-blur">
        <p class="text-sm font-semibold uppercase tracking-wide text-emerald-300">Financial Exports</p>
        <h2 class="mt-2 text-3xl font-black md:text-4xl">Reports</h2>
        <p class="mt-2 max-w-2xl text-sm text-slate-400">
          Review your financial trends and spending behavior.
        </p>
      </div>

      <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        <div class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-slate-950/70 p-6 shadow-2xl shadow-slate-950/20">
          <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-emerald-400/10 blur-2xl"></div>
          <div class="relative mb-5 flex h-14 w-14 items-center justify-center rounded-2xl border border-emerald-400/20 bg-emerald-400/10 text-sm font-black text-emerald-300">
            CSV
          </div>

          <p class="relative text-sm font-semibold uppercase tracking-wide text-emerald-300">Transactions</p>
          <h3 class="relative mt-1 text-2xl font-black">Transactions Report</h3>
          <p class="relative mb-6 mt-3 text-sm leading-relaxed text-slate-400">
            Export all your income and expense records as a CSV file.
          </p>

          <button
            @click="downloadTransactionsCsv"
            class="relative w-full rounded-2xl bg-emerald-500 py-3 font-black text-slate-950 transition hover:bg-emerald-400"
          >
            Download CSV
          </button>
        </div>

        <div class="flex min-h-72 items-center justify-center rounded-[2rem] border border-dashed border-slate-800 bg-slate-950/60 p-10 text-center text-slate-500 md:col-span-1 xl:col-span-2">
          No report data yet. Add transactions to generate insights.
        </div>
      </div>
    </section>
  </main>
</template>

<script setup>
import api, { isCanceledRequest } from '../services/api'
import Sidebar from '../components/Sidebar.vue'
import { useAuth } from '../composables/useAuth'

const { isAuthenticated } = useAuth()

const downloadTransactionsCsv = async () => {
  if (!isAuthenticated.value) return

  try {
    const response = await api.get('/reports/transactions/csv', {
      responseType: 'blob'
    })

    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')

    link.href = url
    link.setAttribute('download', 'pesotracker_transactions.csv')
    document.body.appendChild(link)
    link.click()
    link.remove()

    window.URL.revokeObjectURL(url)
  } catch (error) {
    if (isCanceledRequest(error)) return
    console.error(error)
  }
}
</script>

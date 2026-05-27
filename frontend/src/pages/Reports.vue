<template>
  <main class="min-h-screen bg-slate-950 text-white flex">
    <!-- Sidebar -->
    <Sidebar />

    <section class="flex-1 p-6 pt-24 lg:pt-6 overflow-y-auto">
      <div class="mb-8">
        <p class="text-slate-400">Download your</p>
        <h2 class="text-3xl font-bold">Reports</h2>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6">
          <div class="w-12 h-12 bg-emerald-500/10 text-emerald-400 rounded-2xl flex items-center justify-center mb-5">
            CSV
          </div>

          <h3 class="text-xl font-bold mb-2">
            Transactions Report
          </h3>

          <p class="text-slate-400 text-sm mb-6">
            Export all your income and expense records as a CSV file.
          </p>

          <button
            @click="downloadTransactionsCsv"
            class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-3 rounded-2xl font-bold transition"
          >
            Download CSV
          </button>
        </div>
      </div>
    </section>
  </main>
</template>

<script setup>
import api from '../services/api'
import Sidebar from '../components/Sidebar.vue'

const downloadTransactionsCsv = async () => {
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
    console.error(error)
  }
}
</script>
<template>
  <div class="relative h-72">
    <Bar :data="chartData" :options="chartOptions" />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  BarElement,
  CategoryScale,
  LinearScale,
  Tooltip,
  Legend
} from 'chart.js'

ChartJS.register(
  BarElement,
  CategoryScale,
  LinearScale,
  Tooltip,
  Legend
)

const props = defineProps({
  monthlySummary: {
    type: Array,
    default: () => []
  }
})

const chartData = computed(() => {
  const months = [...new Set(props.monthlySummary.map(item => item.month))]

  const incomeData = months.map(month => {
    const item = props.monthlySummary.find(
      row => row.month === month && row.type === 'income'
    )

    return item ? Number(item.total) : 0
  })

  const expenseData = months.map(month => {
    const item = props.monthlySummary.find(
      row => row.month === month && row.type === 'expense'
    )

    return item ? Number(item.total) : 0
  })

  return {
    labels: months,
    datasets: [
      {
        label: 'Income',
        data: incomeData,
        backgroundColor: '#10B981',
        borderRadius: 8
      },
      {
        label: 'Expenses',
        data: expenseData,
        backgroundColor: '#EF4444',
        borderRadius: 8
      }
    ]
  }
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      labels: {
        color: '#CBD5E1'
      }
    }
  },
  scales: {
    x: {
      ticks: {
        color: '#94A3B8'
      },
      grid: {
        color: '#1E293B'
      }
    },
    y: {
      ticks: {
        color: '#94A3B8'
      },
      grid: {
        color: '#1E293B'
      }
    }
  }
}
</script>
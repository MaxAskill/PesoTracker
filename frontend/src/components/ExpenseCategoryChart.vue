<template>
  <div class="relative h-64">
    <Doughnut :data="chartData" :options="chartOptions" />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Doughnut } from 'vue-chartjs'
import {
  Chart as ChartJS,
  ArcElement,
  Tooltip,
  Legend
} from 'chart.js'

ChartJS.register(ArcElement, Tooltip, Legend)

const props = defineProps({
  categories: {
    type: Array,
    default: () => []
  }
})

const chartData = computed(() => ({
  labels: props.categories.map(item => item.category),
  datasets: [
    {
      data: props.categories.map(item => Number(item.total)),
      backgroundColor: [
        '#10B981',
        '#F59E0B',
        '#EF4444',
        '#3B82F6',
        '#8B5CF6',
        '#EC4899'
      ],
      borderColor: '#0F172A'
    }
  ]
}))

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      labels: {
        color: '#CBD5E1'
      }
    }
  }
}
</script>
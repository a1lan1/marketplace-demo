<script setup lang="ts">
import {
  CategoryScale,
  Chart as ChartJS,
  Legend,
  LinearScale,
  LineElement,
  PointElement,
  Title,
  Tooltip
} from 'chart.js'
import { computed } from 'vue'
import { Line } from 'vue-chartjs'

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale
)

const props = defineProps<{
  data: { date: string; average_rating: number }[];
}>()

const chartData = computed(() => ({
  labels: props.data.map((item) => new Date(item.date).toLocaleDateString()),
  datasets: [
    {
      label: 'Average Rating',
      backgroundColor: '#4A90E2',
      borderColor: '#4A90E2',
      data: props.data.map((item) => item.average_rating),
      tension: 0.2
    }
  ]
}))

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  scales: {
    y: {
      beginAtZero: false,
      max: 5,
      min: 1
    }
  }
}
</script>

<template>
  <Line
    :data="chartData"
    :options="chartOptions"
  />
</template>

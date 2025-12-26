<script setup lang="ts">
import { ArcElement, Chart as ChartJS, Legend, Title, Tooltip } from 'chart.js'
import { computed } from 'vue'
import { Pie } from 'vue-chartjs'

ChartJS.register(Title, Tooltip, Legend, ArcElement)

const props = defineProps<{
  data: Record<string, number>;
}>()

const chartData = computed(() => ({
  labels: Object.keys(props.data),
  datasets: [
    {
      backgroundColor: [
        '#42A5F5',
        '#66BB6A',
        '#FFA726',
        '#AB47BC',
        '#26C6DA',
        '#FF7043',
        '#5C6BC0',
        '#D4E157',
        '#8D6E63'
      ],
      data: Object.values(props.data)
    }
  ]
}))

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom' as const
    }
  }
}
</script>

<template>
  <Pie
    :data="chartData"
    :options="chartOptions"
  />
</template>

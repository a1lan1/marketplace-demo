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
      backgroundColor: ['#41B883', '#E46651', '#00D8FF', '#DD1B16'],
      data: Object.values(props.data)
    }
  ]
}))

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false
}
</script>

<template>
  <Pie
    :data="chartData"
    :options="chartOptions"
  />
</template>

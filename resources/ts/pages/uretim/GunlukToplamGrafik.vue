<script setup>
import { computed } from 'vue'
import { Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale
} from 'chart.js'
import ChartDataLabels from 'chartjs-plugin-datalabels'
import dayjs from 'dayjs'
import 'dayjs/locale/tr'

dayjs.locale('tr')
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ChartDataLabels)

const props = defineProps({
  veri: {
    type: Array,
    default: () => []
  }
})

const labels = computed(() => props.veri.map(item => dayjs(item.gun).format('dddd')))
const dataSet = computed(() => props.veri.map(item => item.toplam_paket_miktari))
</script>

<template>
  <div style="height: 180px; overflow: hidden; margin-top: 5px;">
    <Bar :data="{
      labels: labels,
      datasets: [
        {
          label: 'Toplam Paket Miktarı',
          data: dataSet,
          backgroundColor: 'rgba(115, 102, 240, 0.7)'
        }
      ]
    }" :options="{
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        datalabels: {
          anchor: 'end',
          align: 'bottom',
          color: 'white',
          fontSize: 28,
          font: { weight: 'bold' },
          formatter: value => Math.round(value)
        }
      },
      scales: {
        y: { beginAtZero: true },
        x: { ticks: { autoSkip: false } }
      }
    }" plugins="[ChartDataLabels]" />
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

// Reaktif tema renk değişkenleri ve grafik ayarları
const borderColor = 'rgba(var(--v-border-color), var(--v-border-opacity))';

// Props tanımı: barData ve verimData dizileri olarak bekleniyor
const props = defineProps<{
  barData: number[];
  // verimData: number[];
}>();

// Kategori etiketleri (yatay bar için Y ekseninde gösterilecek)
const CATEGORIES = ['Klln %', 'Ürtk %', 'Klte %', 'OEE %'];

const clampPercent = (n: number) => Math.max(0, Math.min(100, n));

const computeOee = (values: number[]) => {
  const [a, p, q] = values.map(v => Number.isFinite(v) ? v : 0);
  const raw = (a * p * q) / 10000; // (A% * P% * Q%)
  return Math.round(clampPercent(raw));
};

const buildSeriesData = (values: number[]) => {
  const base = values.slice(0, 3).map((y, i) => ({ x: CATEGORIES[i], y: clampPercent(Number(y) || 0) }));
  const oee = computeOee(values);
  return [...base, { x: CATEGORIES[3], y: oee }];
};

// Grafik seçenekleri
const topicsChartConfig = ref({
  chart: {
    height: 170,
    type: 'bar',
    toolbar: {
      show: false,
    },
  },
  plotOptions: {
    bar: {
      horizontal: true,
      barHeight: '70%',
      distributed: true,
      borderRadius: 20,
      borderRadiusApplication: 'end',
    },
  },
  colors: [
    'rgba(var(--v-theme-info),1)',
    'rgba(var(--v-theme-warning),1)',
    'rgba(var(--v-theme-error),1)',
    'rgba(var(--v-theme-success),1)',
  ],
  grid: {
    borderColor,
    strokeDashArray: 10,
    xaxis: {
      lines: { show: true },
    },
    yaxis: {
      lines: { show: false },
    },
    padding: {
      top: -30,
      bottom: 35,
      left: 5,
      right: 20,
    },
  },
  dataLabels: {
    enabled: true,
    style: {
      colors: ['#eee'],
      fontWeight: 600,
      fontSize: '22px',
    },
    offsetX: 0,
    dropShadow: {
      enabled: false,
    },
    formatter(val: string, opt: any) {
      return `${val}`;
    },
  },
  xaxis: {
    axisBorder: { show: false },
    axisTicks: { show: false },
    labels: {
      show: false,
      style: {
        colors: 'rgba(var(--v-theme-on-background), var(--v-disabled-opacity))',
        fontSize: '16px',
      },
    },
  },
  yaxis: {
    labels: {
      show: true,
      style: {
        colors: 'gray',
        fontSize: '14px',
      },
    },
  },
  tooltip: {
    enabled: true,
    style: { fontSize: '18px' },
    onDatasetHover: {
      highlightDataSeries: false,
    },
  },
  legend: {
    show: false,
  },
});

// Serileri reaktif olarak başlat
const topicsChartSeries = ref([
  {
    data: buildSeriesData(props.barData),
  },
]);

// props.barData değişimini izleyerek grafiği güncelle
watch(
  () => props.barData,
  (newData) => {
    topicsChartSeries.value = [{ data: buildSeriesData(newData) }];
  }
);

</script>

<template>
  <VCardText class="mt-0 pa-0 pb-4">
    <VRow>
      <VCol cols="12">
        <VueApexCharts class="ma-0 pa-0 " type="bar" height="300" :options="topicsChartConfig"
          :series="topicsChartSeries" />
      </VCol>
    </VRow>
  </VCardText>
</template>

<style scoped>
/* İsteğe bağlı ek stil */
</style>

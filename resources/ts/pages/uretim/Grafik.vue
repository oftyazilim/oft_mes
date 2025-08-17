<script setup lang="ts">
import VueApexCharts from 'vue3-apexcharts';
import { watch, ref } from 'vue';

// Reaktif tema renk değişkenleri ve grafik ayarları
const borderColor = 'rgba(var(--v-border-color), var(--v-border-opacity))';

// Props tanımı: barData ve verimData dizileri olarak bekleniyor
const props = defineProps<{
  barData: number[];
  // verimData: number[];
}>();

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
      horizontal: false,
      barHeight: '70%',
      distributed: true,
      borderRadius: 4,
      borderRadiusApplication: 'end',
    },
  },
  colors: [
    'rgba(var(--v-theme-primary),1)', 'rgba(var(--v-theme-warning),1)', 'rgba(var(--v-theme-error),1)',
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
      left: -1,
      right: -3,
    },
  },
  dataLabels: {
    enabled: true,
    style: {
      colors: ['#fff'],
      fontWeight: 300,
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
  labels: props.barData,
  xaxis: {
    categories: ['Klln %', 'Ürtk %', 'Klte %'],
    axisBorder: { show: false },
    axisTicks: { show: false },
    labels: {
      style: {
        colors: 'rgba(var(--v-theme-on-background), var(--v-disabled-opacity))',
        fontSize: '16px',
      },
    },
  },
  yaxis: {
    labels: {
      show: false,
      style: {
        colors: 'rgba(var(--v-theme-on-background), var(--v-disabled-opacity))',
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
    data: props.barData,
  },
]);

// props.barData değişimini izleyerek grafiği güncelle
watch(
  () => props.barData,
  (newData) => {
    topicsChartSeries.value = [{ data: newData }];
  }
);

</script>

<template>
  <!-- <VCard> -->

  <VCardText class="mt-0 pa-0 pb-4">
    <VRow>
      <VCol cols="12">
        <VueApexCharts class="ma-0 mt-2 pa-0" type="bar" height="200" :options="topicsChartConfig"
          :series="topicsChartSeries" />
      </VCol>

      <!-- <VCol cols="6">
              <VueSpeedometer :value="uretimHizi" :max-value="100" :min-value="0" :segments="5"
                :needle-transition-duration="1000" needle-transition="easeElastic"
                current-value-text="${value} adet/saat" ring-width="30" width="200" height="12"
                style="margin-block-end: -30px" />
              <h3 class="text-h6 mb-2 text-center">OEE %</h3>
        </VCol> -->
    </VRow>
  </VCardText>
  <!-- </VCard> -->
</template>

<style scoped>
/* İsteğe bağlı ek stil */
</style>

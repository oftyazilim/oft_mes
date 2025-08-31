<template>
	<div class="rollform-gauge-wrap">
		<div class="gauge-title">TM24</div>
		<DxCircularGauge
			class="rollform-gauge"
			:value="rpm"
			:tooltip="{ enabled: false }"
			:size="{ width: 300, height: 300 }"
		>
			<DxGeometry :start-angle="225" :end-angle="-45" />

			<DxScale :start-value="0" :end-value="150" :tick-interval="19" :minor-tick-interval="9.5">
				<DxTick :length="8" color="#666" />
				<DxMinorTick :length="4" color="#444" />
				<DxLabel :use-range-colors="true" :font="{ color: '#9aa4b2', size: 12 }" />
			</DxScale>

			<DxRangeContainer background-color="#20252b">
				<DxRange :start-value="0" :end-value="38" color="#9b1c31" />
				<DxRange :start-value="38" :end-value="75" color="#8c8c8c" />
				<DxRange :start-value="75" :end-value="94" color="#3aa757" />
				<DxRange :start-value="94" :end-value="131" color="#1e8e3e" />
				<DxRange :start-value="131" :end-value="150" color="#0a7b34" />
			</DxRangeContainer>

			<DxValueIndicator type="triangleNeedle" color="#cfd8dc" :spindle-size="10" :spindle-gap-size="6" :offset="0" />
			<DxSubvalueIndicator :size="0" />

			<DxTitle text="" />

			<DxExport :enabled="false" />
		</DxCircularGauge>

			<div class="gauge-center-value">
				{{ Math.round(rpm) }}
				<span class="unit">m/dk</span>
			</div>

		<div class="gauge-footer">
			<div class="digital-clock">{{ time }}</div>
			<div class="digital-date">{{ dateText }}</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import DxCircularGauge, {
  DxExport,
  DxGeometry,
  DxLabel,
  DxMinorTick,
  DxRange,
  DxRangeContainer,
  DxScale,
  DxSubvalueIndicator,
  DxTick,
  DxTitle,
  DxValueIndicator,
} from 'devextreme-vue/circular-gauge'
import { onMounted, onUnmounted, ref } from 'vue'

const rpm = ref(78)
const time = ref('16:38:42')
const dateText = ref('23 Haziran 2023 Cuma')

let timer: ReturnType<typeof setInterval> | null = null

onMounted(() => {
	// Demo: zamanı canlı akıt
	timer = setInterval(() => {
		const now = new Date()
		const hh = String(now.getHours()).padStart(2, '0')
		const mm = String(now.getMinutes()).padStart(2, '0')
		const ss = String(now.getSeconds()).padStart(2, '0')
		time.value = `${hh}:${mm}:${ss}`

		const days = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi']
		const months = ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık']
		dateText.value = `${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()} ${days[now.getDay()]}`
	}, 1000)
})

onUnmounted(() => {
	if (timer) clearInterval(timer)
})
</script>

<style scoped>
.rollform-gauge-wrap {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  padding: 12px;
  border-radius: 12px;
  background: #171b29; /* koyu arka plan */
  block-size: 360px;
  color: #cfd8dc;
  inline-size: 340px;
}

.gauge-title {
  position: absolute;
  color: #9af06e; /* yeşil başlık */
  font-weight: 700;
  inset-block-start: 6px;
  inset-inline-start: 12px;
}

.rollform-gauge :deep(.dxc-val-indicator path) {
  filter: drop-shadow(0 0 3px rgba(207, 216, 220, 80%));
}

.gauge-center-value {
  position: absolute;
  color: #cfd8dc;
  font-size: 40px;
  font-weight: 700;
  inset-block-start: 120px;
  text-shadow: 0 0 8px rgba(0, 0, 0, 60%);
}

.gauge-center-value .unit {
  display: block;
  font-size: 18px;
  margin-inline-start: 5px;
  opacity: 0.8;
}

.gauge-footer {
  position: absolute;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  inset-block-end: 20px;
}

.digital-clock {
  border-radius: 6px;
  background: #0a0f14;
  box-shadow: inset 0 0 8px rgba(0, 0, 0, 60%), 0 0 6px rgba(112, 225, 255, 30%);
  color: #70e1ff;
  font-family: DS-Digital, monospace;
  font-size: 20px;
  padding-block: 4px;
  padding-inline: 10px;
}

.digital-date {
  color: #98a6b3;
  font-size: 13px;
}
</style>

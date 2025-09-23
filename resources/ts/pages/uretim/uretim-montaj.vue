<template>
  <div v-if="!firstLoadDone" class="montaj2-loading-overlay">
    <div class="montaj2-loading-content">
      <div class="montaj2-spinner" aria-label="Yükleniyor" />
      <h2 class="montaj2-loading-title">Montaj Paneli Açılıyor</h2>
      <p class="montaj2-loading-hint">İlk veriler alınıyor: <strong>{{ readinessProgress }}</strong></p>
      <p class="montaj2-loading-sub">Kartlar, performans ve zamanlayıcılar hazırlanıyor...</p>
      <p class="montaj2-loading-timeout" v-if="safetyTimeoutHit"><span class="warn">Bazı veriler gecikti, yine de
          gösteriliyor.</span></p>
    </div>
  </div>
  <VContainer fluid class="pa-0 ma-0" :style="{ visibility: firstLoadDone ? 'visible' : 'hidden' }">
    <ProductionSlider @ilk-veri-hazir="onChildFirstData" />
  </VContainer>
  
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import ProductionSlider from './ProductionSlider.vue'

definePage({ meta: { action: ['manage', 'read'], subject: ['montaj', 'all'] } })

// Bu pencereyi isimlendir: menüden target: 'oft-montaj' ile çağrılınca mevcut sekme odaklansın
onMounted(() => { try { if (window.name !== 'oft-montaj') window.name = 'oft-montaj' } catch (e) { /* noop */ } })

// Readiness bayrakları: child içinde emit ile tetiklenecek plan (şimdilik sadece ilk veri sinyali)
const firstLoadDone = ref(false)
const childDataReady = ref(false)
const frameSettled = ref(false)
const safetyTimeoutHit = ref(false)

function onChildFirstData() { childDataReady.value = true }

// İlk frame sonrasında layout stabilize kabul
requestAnimationFrame(() => { frameSettled.value = true })

const allReady = computed(() => childDataReady.value && frameSettled.value)
watch(allReady, v => { if (v && !firstLoadDone.value) firstLoadDone.value = true })

// 10 sn güvenlik
setTimeout(() => { if (!firstLoadDone.value) { safetyTimeoutHit.value = true; firstLoadDone.value = true } }, 10000)

const readinessProgress = computed(() => [
  childDataReady.value ? 'Veri ✓' : 'Veri...',
  frameSettled.value ? 'Render ✓' : 'Render...'
].join(' / '))
</script>

<style scoped>
.montaj2-loading-overlay {
  position: fixed;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(3px);
  background: radial-gradient(circle at 30% 30%, rgba(90 130 200 / 12%), rgba(10 15 25 / 90%));
  inset: 0;
}

.montaj2-loading-content {
  border: 1px solid rgba(255 255 255 / 10%);
  border-radius: 18px;
  background: rgb(36, 46, 60);
  box-shadow: 0 8px 28px -4px rgba(0 0 0 / 55%), 0 0 0 1px rgba(255 255 255 / 6%) inset;
  max-inline-size: 480px;
  padding-block: 34px 42px;
  padding-inline: 40px;
  text-align: center;
}

.montaj2-spinner {
  border: 5px solid rgba(255 255 255 / 12%);
  border-radius: 50%;
  animation: montaj2-rot 1.05s linear infinite, montaj2-pulse 2.2s ease-in-out infinite;
  block-size: 66px;
  border-block-start-color: #3b82f6;
  box-shadow: 0 0 0 0 rgba(59 130 246 / 35%);
  inline-size: 66px;
  margin-block-end: 26px;
  margin-inline: auto;
}

.montaj2-loading-title {
  background: linear-gradient(92deg, #fff, #d0e4ff 28%, #9fc5ff 67%, #fff);
  background-clip: text;
  color: transparent;
  font-size: 25px;
  font-weight: 650;
  letter-spacing: 0.5px;
  margin-block: 0 14px;
  margin-inline: 0;
}

.montaj2-loading-hint {
  color: #d6e1f2;
  font-size: 15px;
  margin-block: 0 8px;
  margin-inline: 0;
}

.montaj2-loading-sub {
  color: #95a6bc;
  font-size: 13px;
  margin-block: 0 6px;
  margin-inline: 0;
}

.montaj2-loading-timeout {
  color: #f0c674;
  font-size: 12px;
  margin-block: 14px 0;
  margin-inline: 0;
}

.montaj2-loading-timeout .warn {
  color: #ffc266;
  font-weight: 600;
}

@keyframes montaj2-rot {
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
}

@keyframes montaj2-pulse {
  0%,
  100% {
    box-shadow: 0 0 0 0 rgba(59 130 246 / 35%);
  }

  50% {
    box-shadow: 0 0 0 12px rgba(59 130 246 / 0%);
  }
}
</style>

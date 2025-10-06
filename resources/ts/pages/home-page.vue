<script setup lang="ts">

import { computed, onMounted, ref } from 'vue'
import { VCard, VBtn, VTextField } from 'vuetify/components'
import { usePageTitleStore } from "@/stores/pageTitle";

// Yerel JSON: ağaç verisi
// tsconfig resolveJsonModule aktif; doğrudan import edebiliriz
// Yol: aynı klasörde agac.json
// eslint-disable-next-line @typescript-eslint/ban-ts-comment
// @ts-ignore

// Dinamik kelimeler (göz alıcı ama sakin döngü)
const words = ['Verimlilik', 'İzlenebilirlik', 'Şeffaflık', 'Hız', 'Kalite']
const activeIndex = ref(0)
const prefersReducedMotion = ref(false)
let rotateTimer: number | null = null
const pageTitleStore = usePageTitleStore();


onMounted(() => {
  document.title = "OFT - Ana Sayfa";
  pageTitleStore.setTitle("Ana Sayfa");
  try { prefersReducedMotion.value = window.matchMedia('(prefers-reduced-motion: reduce)')?.matches } catch (_) { }
  if (!prefersReducedMotion.value) {
    rotateTimer = window.setInterval(() => {
      activeIndex.value = (activeIndex.value + 1) % words.length
    }, 3200) // yumuşak geçiş aralığı
  }
})

// window.setTimeout = null;
</script>

<template>
  <VCard class="welcome-page">



    <div class="welcome-card">
      <h1 class="welcome-title gradient-cycle">
        <span class="title-text">OFT MES'e Hoşgeldiniz!</span>
        <span class="shine" aria-hidden="true" />
      </h1>
      <br>
      <br>
      <p class="welcome-desc">
        Üretim süreçlerinizi dijitalleştirmenin ve verimliliği artırmanın tam zamanı!
      </p>
      <br>
      <blockquote class="welcome-quote dynamic-quote">
        <span class="quote-main">“Başarı, küçük adımların toplamıdır.”</span>
        <span class="word-rotator" :class="{ reduced: prefersReducedMotion }">
          <transition-group name="rot-fade" tag="span">
            <span v-for="(w, i) in words" :key="w" v-show="i === activeIndex" class="rot-word">{{ w }}</span>
          </transition-group>
        </span>
      </blockquote>
    </div>
  </VCard>


</template>

<style scoped>
.welcome-page {
  display: flex;
  align-items: center;
  justify-content: center;

  /* background: linear-gradient(120deg, #536271 0%, #e0e7ef 100%); */
  min-block-size: 70vh;
}

.welcome-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  border-radius: 18px;

  /* background: #fff; */
  box-shadow: 0 4px 24px 0 rgba(60, 72, 100, 8%);
  inline-size: 100%;
  max-inline-size: 720px;
  padding-block: 2.5rem 2rem;
  padding-inline: 2rem;
  text-align: center;
}

.welcome-image {
  inline-size: 120px;
  margin-block-end: 1.5rem;
  user-select: none;
}

.welcome-title {
  position: relative;
  display: inline-block;
  overflow: hidden;
  font-size: 2rem;
  font-weight: 700;
  margin-block-end: 1rem;
}

.gradient-cycle {
  animation: title-gradient 14s linear infinite;
  background: linear-gradient(90deg, #1e293b, #334155, #2563eb, #1e293b);
  background-clip: text;
  background-size: 280% 100%;
  color: transparent;
}

.welcome-title .shine {
  position: absolute;
  animation: shine-move 6.5s ease-in-out infinite;
  background: linear-gradient(120deg, transparent 0%, rgba(255, 255, 255, 15%) 45%, rgba(255, 255, 255, 60%) 50%, rgba(255, 255, 255, 15%) 55%, transparent 100%);
  inset: 0;
  mix-blend-mode: overlay;
  pointer-events: none;
}

.home-tree {
  margin-block-start: 24px;
}

.tree-toolbar {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-block-end: 8px;
}

.tree-toolbar .root-field {
  max-inline-size: 280px;
}

.tree-toolbar .toolbar-actions {
  margin-inline-start: auto;
}

.welcome-desc {
  /* color: #4b5563; */
  font-size: 1.08rem;
  margin-block-end: 1.5rem;
}

.welcome-quote {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin: 0;
  color: #2563eb;
  font-size: 1.4rem;
  font-style: italic;
  gap: 0.75rem;
}

.dynamic-quote .word-rotator {
  position: relative;
  display: inline-block;

  /* Zıplamayı önlemek için sabit blok yüksekliği */
  block-size: 1.6rem;
  color: #479753b4;
  font-size: 24px;
  font-weight: 600;
  letter-spacing: 0.5px;
  line-height: 1.6rem;
  min-inline-size: 190px;
  text-align: center;
}

.dynamic-quote .word-rotator .rot-word {
  position: absolute;
  display: flex;
  align-items: center;
  justify-content: center;
  inset: 0;
}

.dynamic-quote .word-rotator.reduced {
  display: none;
}

.rot-fade-enter-active,
.rot-fade-leave-active {
  position: absolute;
  inset: 0;
  transition: all 0.65s cubic-bezier(0.55, 0.21, 0.17, 1.02);
}

.rot-fade-enter-from {
  filter: blur(5px);
  opacity: 0;
  transform: translateY(18px) scale(0.94);
}

.rot-fade-leave-to {
  filter: blur(5px);
  opacity: 0;
  transform: translateY(-18px) scale(0.94);
}

@keyframes title-gradient {
  0% {
    background-position: 0% 50%;
  }

  100% {
    background-position: 280% 50%;
  }
}

@keyframes shine-move {

  0%,
  45% {
    transform: translateX(-130%);
  }

  60% {
    transform: translateX(25%);
  }

  100% {
    transform: translateX(130%);
  }
}

@media (max-width: 600px) {
  .welcome-card {
    inline-size: 100%;
    max-inline-size: 98vw;
    padding-block: 1.2rem;
    padding-inline: 0.5rem;
  }

  .welcome-image {
    inline-size: 80px;
  }
}
</style>

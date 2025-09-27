<template>
  <!-- PNG varyantı (opsiyonel) -->
  <img
    v-if="variantComputed === 'png' && pngSrcComputed"
    class="brand-logo-img"
    :src="pngSrcComputed"
    alt="logo"
    aria-label="Logo"
  />

  <!-- Renkli orijinal SVG (çok renkli/detaylı) -->
  <img
    v-else-if="variantComputed === 'color'"
    class="brand-logo-img"
    :src="logoColorUrl"
    alt="logo"
    aria-label="Logo"
  />

  <!-- Monokrom siluet (tema rengi) -->
  <span v-else class="brand-logo mono text-primary" :style="monoStyle" role="img" aria-label="Logo" />
</template>

<script setup lang="ts">
import { computed } from 'vue';
// Mask için URL (siluet)
import logoMaskUrl from '@images/logo.svg?url';
// Renkli/ham SVG

const props = defineProps<{ variant?: 'mono' | 'color' | 'png'; pngSrc?: string }>()

const envVariant = (import.meta.env.VITE_APP_LOGO_VARIANT as 'mono' | 'color' | 'png' | undefined) || undefined
const envPng = (import.meta.env.VITE_APP_LOGO_PNG as string | undefined) || undefined

const variantComputed = computed(() => props.variant || envVariant || 'mono')
const pngSrcComputed = computed(() => props.pngSrc || envPng)

// CSS mask-image inline style (vendor bağımlılığı olmadan)
const monoStyle = {
  WebkitMaskImage: `url(${logoMaskUrl})`,
  maskImage: `url(${logoMaskUrl})`,
}

// Renkli SVG için URL (img ile kullanmak için)
const logoColorUrl = logoMaskUrl
</script>

<style scoped>
.brand-logo {
  display: inline-block;
  background: transparent;
  block-size: 34px;
  inline-size: 44px;
  line-height: 0;
  margin-inline-start: -30px;
}

.brand-logo.mono {
  background-color: currentcolor;
  filter: drop-shadow(0 0 1.5px rgba(var(--v-theme-on-surface), 0.75));
  mask-position: center;
  mask-repeat: no-repeat;
  mask-size: contain;

  /* Siluet belirginliği için hafif gölge */
}

.brand-logo-img {
  display: inline-block;
  block-size: 34px;
  inline-size: 44px;
}
</style>

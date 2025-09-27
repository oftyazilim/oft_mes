<template>
  <span class="app-logo" :style="logoStyle" role="img" aria-label="OFT logo" />
</template>

<script setup lang="ts">
// Vite: ?url ile gerçek dosya yolu alınır ve CSS mask-image'da kullanılabilir
import logoUrl from '@images/oft.svg?url';

const logoStyle = {
  WebkitMaskImage: `url(${logoUrl})`,
  maskImage: `url(${logoUrl})`,
}
</script>

<style scoped>
.app-logo {
  display: inline-block;
  background-color: currentcolor; /* tema rengi */
  block-size: 40px;
  inline-size: 40px; /* önceki boyutla uyumlu */
  mask-position: center;
  mask-repeat: no-repeat;
  mask-size: contain;
}
</style>

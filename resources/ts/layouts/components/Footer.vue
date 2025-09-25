<template>
  <div class="h-100 d-flex align-center justify-md-space-between justify-center">
    <!-- 👉 Footer: left content -->
    <span class="d-flex align-center text-medium-emphasis">
      &copy; {{ new Date().getFullYear() }} Made With
      <VIcon icon="tabler-heart-filled" color="error" size="1.25rem" class="mx-1" />
      By
      <a href="https://oftsoftware.com" target="_blank" rel="noopener noreferrer" class="text-primary ms-1">OFT
        Yazılım</a>
      <span class="ms-4 d-flex align-center" style="font-size: 0.75rem; gap: 4px; opacity: 0.8; white-space: nowrap;">
        <span>Son güncelleme: {{ buildTimeDisplay }}</span>
        <span v-if="appVersion">• v{{ appVersion }}</span>
      </span>
    </span>
    <!-- 👉 Footer: right content -->
    <span class="d-md-flex gap-x-4 text-primary d-none">
      <a href="https://canovate.com" target="noopener noreferrer">Canovate için kodlanmıştır...</a>
    </span>
  </div>
</template>

<script setup lang="ts">
// Global sabit build sırasında enjekte edilir
declare const __BUILD_TIME__: string | undefined;
declare const __APP_VERSION__: string | undefined;

function formatNow(): string {
  const d = new Date();
  const pad = (n: number) => String(n).padStart(2, '0');
  return `${pad(d.getDate())}.${pad(d.getMonth() + 1)}.${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

const buildTimeRaw = (typeof __BUILD_TIME__ !== 'undefined' && __BUILD_TIME__) ? __BUILD_TIME__ : '';
const buildTimeDisplay = buildTimeRaw || formatNow();
const appVersion = (typeof __APP_VERSION__ !== 'undefined' && __APP_VERSION__) ? __APP_VERSION__ : '';
</script>

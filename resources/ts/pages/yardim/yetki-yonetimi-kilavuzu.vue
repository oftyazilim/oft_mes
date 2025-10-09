<script setup lang="ts">
import { definePage } from 'unplugin-vue-router'
// Import markdown as raw text via Vite's ?raw
// Note: In production this is bundled; no server needed to fetch the file.
// If the MD file moves, update the import path accordingly.
// eslint-disable-next-line import/no-unresolved
import guideMd from '../../../../docs/yetki-yonetimi-kilavuzu.md?raw'

// definePage({
//   meta: { action: 'read', subject: 'genel', layout: 'default'  },
// })

const mdText = guideMd
</script>

<template>
  <VContainer fluid>
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardTitle>Yetki Yönetimi Kılavuzu</VCardTitle>
          <VCardSubtitle>Bu sayfa, depo içindeki markdown dokümanını gösterir.</VCardSubtitle>
          <VCardText>
            <div class="markdown-pre">
              {{ mdText }}
            </div>
            <div class="mt-4 text-medium-emphasis">
              PDF/Word sürümleri için Yardım menüsündeki örnek bağlantıları kullanabilirsiniz.
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </VContainer>

</template>

<style scoped>
.markdown-pre {
  font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji";
  line-height: 1.5;
  white-space: pre-wrap;
}
</style>

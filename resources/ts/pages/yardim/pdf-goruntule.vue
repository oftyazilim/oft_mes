<script setup lang="ts">
import { onMounted, ref } from 'vue'

definePage({
    meta: { action: 'read', subject: 'genel' },
})

// Default sample; allow query override ?src=/docs/your.pdf
const params = new URLSearchParams(location.search)
const src = params.get('src') || '/docs/sample.pdf'
const pdfUrl = ref(src)
const loadError = ref<string | null>(null)

onMounted(async () => {
    try {
        const res = await fetch(pdfUrl.value, { method: 'HEAD' })
        if (!res.ok) throw new Error(`HTTP ${res.status}`)
    }
    catch (e) {
        loadError.value = 'PDF dosyası bulunamadı veya yüklenemedi. Lütfen dosya yolunu kontrol edin.'
    }
})
</script>

<template>
    <VContainer fluid>
        <VCard>
            <VCardTitle>PDF Görüntüleyici</VCardTitle>
            <VCardText>
                <VAlert v-if="loadError" type="warning" class="mb-4">
                    {{ loadError }} (Kaynak: {{ pdfUrl }})
                </VAlert>
                <VAlert v-else type="info" class="mb-4">
                    PDF bu sayfada gömülü gösterilir. Gerekirse URL'ye ?src=/docs/dosya.pdf ekleyin.
                </VAlert>
                <div v-if="!loadError" class="pdf-frame">
                    <iframe :src="pdfUrl" title="PDF" frameborder="0" />
                </div>
            </VCardText>
        </VCard>
    </VContainer>
</template>

<style scoped>
.pdf-frame {
    block-size: 80vh;
}

.pdf-frame iframe {
    border: 1px solid rgba(0, 0, 0, 10%);
    block-size: 100%;
    inline-size: 100%;
}
</style>

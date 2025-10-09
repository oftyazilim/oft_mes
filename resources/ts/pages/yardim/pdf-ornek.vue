<script setup lang="ts">
import { definePage } from 'unplugin-vue-router';

// definePage({
//   meta: { action: 'read', subject: 'genel' },
// })

const pdfUrl = '/docs/sample.pdf'
</script>

<template>
    <VContainer>
        <VCard>
            <VCardTitle>Örnek PDF</VCardTitle>
            <VCardText>
                <VAlert type="info" class="mb-4">PDF dosyasını indirerek görüntüleyebilirsiniz.</VAlert>
                <VBtn :href="pdfUrl" target="_blank" prepend-icon="tabler-file">PDF’i Aç</VBtn>
            </VCardText>
        </VCard>
    </VContainer>
</template>

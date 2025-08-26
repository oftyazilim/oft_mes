<script setup lang="ts">
import { definePage } from 'unplugin-vue-router';

definePage({
  meta: { action: 'read', subject: 'genel' },
})

const audioUrl = '/docs/sample.mp3'
</script>

<template>
    <VContainer>
        <VCard>
            <VCardTitle>Örnek Ses</VCardTitle>
            <VCardText>
                <audio controls style="inline-size: 100%;">
                    <source :src="audioUrl" type="audio/mpeg" />
                    Tarayıcınız ses etiketini desteklemiyor.
                </audio>
            </VCardText>
        </VCard>
    </VContainer>
</template>

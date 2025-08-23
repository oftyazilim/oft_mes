<script setup lang="ts">
import { definePage } from 'unplugin-vue-router';

definePage({
    meta: { action: 'manage', subject: 'all' },
})

const videoUrl = '/docs/sample.mp4'
</script>

<template>
    <VContainer>
        <VCard>
            <VCardTitle>Örnek Video</VCardTitle>
            <VCardText>
                <video controls style=" inline-size: 100%;max-inline-size: 100%;">
                    <source :src="videoUrl" type="video/mp4" />
                    Tarayıcınız video etiketini desteklemiyor.
                </video>
            </VCardText>
        </VCard>
    </VContainer>
</template>

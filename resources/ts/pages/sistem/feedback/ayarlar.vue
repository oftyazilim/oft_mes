<script setup lang="ts">
import axios from 'axios'
import { onMounted, ref } from 'vue'

const emails = ref<string[]>([])
const loading = ref(false)
const saving = ref(false)
const input = ref('')
const error = ref<string | null>(null)

const load = async () => {
  loading.value = true
  error.value = null
  try {
    const { data } = await axios.get('/api/settings/notifications/feedback')
    emails.value = Array.isArray(data?.recipients) ? data.recipients : []
  } catch (e: any) {
    error.value = e?.message || 'Yükleme hatası'
  } finally {
    loading.value = false
  }
}

onMounted(load)

const addFromInput = () => {
  const raw = input.value.trim()
  if (!raw) return
  const parts = raw.split(/[;,\s]+/).map(s => s.trim()).filter(Boolean)
  for (const p of parts) {
    if (validateEmail(p) && !emails.value.includes(p)) emails.value.push(p)
  }
  input.value = ''
}

const validateEmail = (v: string) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)

const removeAt = (i: number) => {
  emails.value.splice(i, 1)
}

const save = async () => {
  saving.value = true
  error.value = null
  try {
    await axios.put('/api/settings/notifications/feedback', { recipients: emails.value })
  } catch (e: any) {
    error.value = e?.response?.data?.message || e?.message || 'Kaydetme hatası'
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <VCard :loading="loading">
    <VCardTitle class="d-flex align-center gap-3">
      <VIcon icon="tabler-bell" color="primary" />
      <span>Bildirim Ayarları</span>
      <VSpacer />
      <VBtn color="primary" :loading="saving" @click="save">Kaydet</VBtn>
    </VCardTitle>
    <VCardText>
      <p class="text-body-2">
        Geri bildirimler için e-posta alıcılarını yönetin. Birden fazla e-posta ekleyebilirsiniz.
      </p>

  <div class="d-flex gap-2 align-center" style="max-inline-size: 560px;">
        <VTextField v-model="input" label="E-posta ekle" density="compact" @keyup.enter="addFromInput" hide-details/>
        <VBtn variant="tonal" @click="addFromInput">Ekle</VBtn>
      </div>

      <div class="mt-4 d-flex flex-wrap gap-2">
        <VChip v-for="(mail,i) in emails" :key="mail" closable @click:close="removeAt(i)" color="primary" variant="tonal">
          {{ mail }}
        </VChip>
        <span v-if="!emails.length" class="text-medium-emphasis">Henüz alıcı eklenmedi.</span>
      </div>

      <VAlert v-if="error" type="error" class="mt-4" density="compact">{{ error }}</VAlert>

      <VDivider class="my-6" />
      <div class="text-caption">
        İpucu: Birden fazla alıcıyı virgül, noktalı virgül veya boşlukla ayırarak tek seferde ekleyebilirsiniz.
      </div>
    </VCardText>
  </VCard>
  
</template>

<style scoped>
.gap-2 { gap: 0.5rem; }
.gap-3 { gap: 0.75rem; }
</style>

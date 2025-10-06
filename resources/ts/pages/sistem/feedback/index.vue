<script setup lang="ts">
import axios from 'axios';
import { onMounted, ref, watch } from 'vue';
import { usePageTitleStore } from "@/stores/pageTitle";

interface Row { id:number; created_at:string; email?:string; category?:string; rating?:number; message:string; page_url?:string; status:string; screenshot_url?: string }

const rows = ref<Row[]>([])
const loading = ref(false)
const search = ref('')
const category = ref<string | null>(null)
const status = ref<string | null>(null)
const page = ref(1)
const perPage = ref(20)
const total = ref(0)
const pageTitleStore = usePageTitleStore();

// Detay popup state
const detailOpen = ref(false)
const selected = ref<Row | null>(null)
const openDetail = (r: Row) => {
  selected.value = r
  detailOpen.value = true
}

const load = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/api/feedback', { params: { page: page.value, per_page: perPage.value, search: search.value || undefined, category: category.value || undefined, status: status.value || undefined } })
    rows.value = data.data
    total.value = data.total
  } finally {
    loading.value = false
  }
}

onMounted(load)
watch([search, category, status, page, perPage], load)
  
document.title = "OFT - Geri Bildirimler";
pageTitleStore.setTitle("Geri Bildirimler");

const exportCsv = async () => {
  const res = await axios.get('/api/feedback/export', { responseType: 'blob' })
  const url = URL.createObjectURL(res.data)
  const a = document.createElement('a')
  a.href = url
  a.download = 'feedback.csv'
  a.click()
  URL.revokeObjectURL(url)
}

const setStatus = async (r: Row, s: 'new'|'in_progress'|'closed'|'invalid') => {
  await axios.put(`/api/feedback/${r.id}`, { status: s })
  await load()
}
</script>

<template>
  <VCard>
    <VCardTitle class="d-flex gap-3 align-center">
      <VIcon icon="tabler-message-dots" color="primary" />
      <span>Geri Bildirimler</span>
      <VSpacer />
  <VTextField v-model="search" density="compact" label="Ara" prepend-inner-icon="tabler-search" hide-details style="max-inline-size: 240px;" />
  <VSelect v-model="category" :items="['oneri','hata','istek','diger']" density="compact" label="Kategori" clearable hide-details style="max-inline-size: 180px;" />
  <VSelect v-model="status" :items="['new','in_progress','closed','invalid']" density="compact" label="Durum" clearable hide-details style="max-inline-size: 180px;" />
      <VBtn variant="tonal" @click="load">Yenile</VBtn>
      <VBtn color="secondary" variant="tonal" @click="exportCsv">CSV Dışa Aktar</VBtn>
    </VCardTitle>
    <VDataTable :items="rows" :loading="loading" :items-per-page="perPage" :page.sync="page" :headers="[
      { title: 'ID', key: 'id' },
  { title: 'Tarih', key: 'created_at' },
      { title: 'E-posta', key: 'email' },
      { title: 'Kategori', key: 'category' },
      { title: 'Puan', key: 'rating' },
      { title: 'Mesaj', key: 'message' },
      { title: 'Sayfa', key: 'page_url' },
      { title: 'Ekran', key: 'screenshot_url' },
      { title: 'Durum', key: 'status' },
      { title: 'İşlem', key: 'actions' },
    ]" class="pa-2">
      <template #item.id="{ item }">
        <div class="d-flex align-center gap-1">
          <span class="me-1">{{ item.id }}</span>
          <VBtn size="x-small" icon variant="text" color="primary" @click="openDetail(item)">
            <VIcon icon="tabler-eye" size="18" />
          </VBtn>
        </div>
      </template>
      <template #item.message="{ value }">
  <div style="max-inline-size: 520px; white-space: normal;">{{ value }}</div>
      </template>
      <template #item.created_at="{ value }">
        {{ formatDateTime(value) }}
      </template>
      <template #item.screenshot_url="{ value }">
        <a v-if="value" :href="value" target="_blank" rel="noopener noreferrer">
          <VAvatar size="36" variant="tonal">
            <VImg :src="value" alt="ekran" cover />
          </VAvatar>
        </a>
        <span v-else>-</span>
      </template>
      <template #item.actions="{ item }">
        <div class="d-flex gap-1">
          <VBtn size="small" variant="tonal" @click="setStatus(item, 'new')">Yeni</VBtn>
          <VBtn size="small" color="warning" variant="tonal" @click="setStatus(item, 'in_progress')">Devam</VBtn>
          <VBtn size="small" color="success" variant="tonal" @click="setStatus(item, 'closed')">Kapat</VBtn>
          <VBtn size="small" color="error" variant="tonal" @click="setStatus(item, 'invalid')">Uygunsuz</VBtn>
        </div>
      </template>
    </VDataTable>
  </VCard>

  <!-- Detay Dialog -->
  <VDialog v-model="detailOpen" max-width="720">
    <VCard>
      <VCardTitle class="d-flex align-center">
        <VIcon icon="tabler-eye" class="me-2" />
        Geri Bildirim #{{ selected?.id }}
        <VSpacer />
        <VBtn icon variant="text" @click="detailOpen=false"><VIcon icon="tabler-x" /></VBtn>
      </VCardTitle>
      <VCardText v-if="selected">
        <div class="mb-3 d-flex flex-wrap gap-2">
          <VChip size="small" color="primary" variant="tonal">{{ formatDateTime(selected.created_at) }}</VChip>
          <VChip size="small" variant="tonal">Kategori: {{ selected.category || '-' }}</VChip>
          <VChip size="small" variant="tonal">Puan: {{ selected.rating ?? '-' }}</VChip>
          <VChip size="small" variant="tonal">Durum: {{ selected.status }}</VChip>
        </div>
        <VDivider class="my-2" />
        <div class="mb-2">E‑posta: <strong>{{ selected.email || '-' }}</strong></div>
        <div class="mb-3">Sayfa: <a :href="selected.page_url" target="_blank">{{ selected.page_url }}</a></div>
        <VDivider class="my-2" />
        <div class="mb-1 text-medium-emphasis">Mesaj</div>
        <div style="white-space: pre-wrap;">{{ selected.message }}</div>
        <VDivider class="my-4" />
        <div class="d-flex flex-wrap gap-2 mb-2">
          <VBtn size="small" variant="tonal" @click="setStatus(selected, 'new')">Yeni</VBtn>
          <VBtn size="small" color="warning" variant="tonal" @click="setStatus(selected, 'in_progress')">Devam</VBtn>
          <VBtn size="small" color="success" variant="tonal" @click="setStatus(selected, 'closed')">Kapat</VBtn>
          <VBtn size="small" color="error" variant="tonal" @click="setStatus(selected, 'invalid')">Uygunsuz</VBtn>
        </div>
        <div v-if="selected.screenshot_url">
          <div class="text-medium-emphasis mb-1">Ekran Görüntüsü</div>
          <a :href="selected.screenshot_url" target="_blank">
            <img :src="selected.screenshot_url" alt="ekran" style="border: 1px solid #eee; border-radius: 8px; block-size: auto; max-inline-size: 100%;" />
          </a>
        </div>
      </VCardText>
      <VCardActions class="justify-end">
        <VBtn variant="text" @click="detailOpen=false">Kapat</VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

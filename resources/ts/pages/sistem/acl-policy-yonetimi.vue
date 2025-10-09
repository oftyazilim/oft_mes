<script setup lang="ts">
import { usePageTitleStore } from '@/stores/pageTitle';
import axios from 'axios';
import { computed, nextTick, onMounted, ref, watch } from 'vue';

// definePage({ meta: { action: ['manage'], subject: ['all'] } })
const pageTitleStore = usePageTitleStore()
const pageName = 'Policy Yönetimi'
const pageAlias = 'ACL'

pageTitleStore.setTitle(`${pageName} (${pageAlias})`)
document.title = `OFT - ${pageName} | ${pageAlias}`

interface Policy { id?: number; key: string; actions: string[]; subjects: string[]; description?: string }
const items = ref<Policy[]>([])
const loading = ref(false)
const editItem = ref<Policy | null>(null)
const isDialogOpen = ref(false)
const actionsSel = ref<string[]>([])
const subjectsSel = ref<string[]>([])
const search = ref('')

const filtered = computed(() => {
  const q = search.value.trim().toLowerCase()
  if (!q) return items.value
  return items.value.filter(i => i.key.toLowerCase().includes(q) || (i.description || '').toLowerCase().includes(q))
})

const keyOptions = computed(() => {
  const set = new Set<string>()
  for (const it of items.value) set.add(it.key)
  return Array.from(set).sort()
})

const actionOptions = computed(() => {
  const set = new Set<string>()
  for (const it of items.value) for (const a of it.actions || []) set.add(String(a))
  return Array.from(set).sort()
})
const subjectOptions = computed(() => {
  const set = new Set<string>()
  for (const it of items.value) for (const s of it.subjects || []) set.add(String(s))
  return Array.from(set).sort()
})
// Dialog görünürlüğü: direkt isDialogOpen üzerinden kontrol
watch(isDialogOpen, v => {
  if (v) nextTick(enableDialogDrag)
  else editItem.value = null
})

// Dialogu başlıktan sürüklenebilir yapmak için basit bir kurulum
function enableDialogDrag() {
  // Aktif overlay içerik düğümünü bul
  const contents = document.querySelectorAll('.v-overlay--active .v-overlay__content')
  const el = contents[contents.length - 1] as HTMLElement | undefined
  if (!el) return
  // İçeriği konumlandırılabilir hale getir
  el.style.position = 'absolute'
  el.style.inset = 'unset'

  const header = el.querySelector('.v-card-title') as HTMLElement | null
  if (!header) return
  header.style.cursor = 'move'

  let startX = 0, startY = 0, startLeft = 0, startTop = 0, dragging = false

  const onMouseMove = (e: MouseEvent) => {
    if (!dragging) return
    const dx = e.clientX - startX
    const dy = e.clientY - startY
    el.style.left = `${startLeft + dx}px`
    el.style.top = `${startTop + dy}px`
  }
  const onMouseUp = () => {
    dragging = false
    window.removeEventListener('mousemove', onMouseMove)
    window.removeEventListener('mouseup', onMouseUp)
  }
  const onMouseDown = (e: MouseEvent) => {
    // Sadece başlık bölgesinden sürükle
    dragging = true
    const rect = el.getBoundingClientRect()
    startX = e.clientX
    startY = e.clientY
    startLeft = rect.left
    startTop = rect.top
    window.addEventListener('mousemove', onMouseMove)
    window.addEventListener('mouseup', onMouseUp)
  }
  header.addEventListener('mousedown', onMouseDown, { passive: true })
}

watch(isDialogOpen, v => { if (v) nextTick(enableDialogDrag) })

async function load() {
  loading.value = true
  try {
    const { data } = await axios.get('/api/ability-policies')
    items.value = (data || []).map((d: any) => ({ id: d.id, key: d.key, actions: d.actions || [], subjects: d.subjects || [], description: d.description || '' }))
  } finally { loading.value = false }
}

function startCreate() {
  editItem.value = { key: '', actions: [], subjects: [], description: '' }
  actionsSel.value = []
  subjectsSel.value = []
  isDialogOpen.value = true
}
function startEdit(p: Policy) {
  editItem.value = { ...p }
  actionsSel.value = [...(p.actions || [])]
  subjectsSel.value = [...(p.subjects || [])]
  isDialogOpen.value = true
}

async function save() {
  if (!editItem.value) return
  const payload = {
    key: editItem.value.key.trim(),
    actions: [...actionsSel.value],
    subjects: [...subjectsSel.value],
    description: editItem.value.description?.trim() || null,
  }
  try {
    if (editItem.value.id) {
      await axios.put(`/api/ability-policies/${editItem.value.id}`, payload)
    } else {
      await axios.post('/api/ability-policies', payload)
    }
    // Başarıyla kaydedildi -> kapat ve listeyi tazele
    isDialogOpen.value = false
    editItem.value = null
    await load()
  } catch (e) {
    // Hata durumunda dialog açık kalsın ki kullanıcı düzeltebilsin
    console.error(e)
  }
}

function cancelDialog() {
  isDialogOpen.value = false
  editItem.value = null
}

async function remove(p: Policy) {
  if (!p.id) return
  await axios.delete(`/api/ability-policies/${p.id}`)
  await load()
}

onMounted(load)
</script>

<template>
  <VContainer>
    <VCard>
      <VCardTitle class="d-flex align-center justify-space-between">
        <span>ACL Policy Yönetimi</span>
        <div>
          <VTextField v-model="search" density="compact" label="Ara (key/description)" class="me-2" />
          <VBtn color="primary" @click="startCreate" variant="outlined" class="mt-2">Yeni Policy</VBtn>
        </div>
      </VCardTitle>
      <VCardText>
        <VDataTable :items="filtered" :loading="loading" :headers="[
          { title: 'Key', key: 'key' },
          { title: 'Actions', key: 'actions' },
          { title: 'Subjects', key: 'subjects' },
          { title: 'Description', key: 'description' },
          { title: '', key: 'act', sortable: false }]
        ">
          <template #item.actions="{ item }">
            <div>{{ item.actions?.join(', ') }}</div>
          </template>
          <template #item.subjects="{ item }">
            <div>{{ item.subjects?.join(', ') }}</div>
          </template>
          <template #item.act="{ item }" width="250">
            <div class="d-inline-flex align-center" style="gap: 6px;">
              <VBtn size="small" :icon="'tabler-edit'" variant="outlined" aria-label="Düzenle" @click="startEdit(item)" />
              <VBtn size="small" color="error" :icon="'tabler-trash'" variant="outlined" aria-label="Sil" @click="remove(item)" />
            </div>
          </template>
        </VDataTable>
      </VCardText>
    </VCard>

    <VDialog v-model="isDialogOpen" max-width="640">
      <VCard v-if="editItem">
        <VCardTitle>{{ editItem?.id ? 'Policy Düzenle' : 'Yeni Policy' }}</VCardTitle>
        <VCardText>
          <VCombobox class="mb-4" v-model="editItem.key" :items="keyOptions"
            label="Key (örn: route:dashboards-analytics)" hide-details="auto" clearable />

          <VCombobox class="mb-4" v-model="actionsSel" :items="actionOptions" label="Actions (çoklu)" multiple chips
            closable-chips hide-details="auto" clearable />

          <VCombobox class="mb-4" v-model="subjectsSel" :items="subjectOptions" label="Subjects (çoklu)" multiple chips
            closable-chips hide-details="auto" clearable />

          <VTextField class="mb-2" v-model="editItem.description" label="Açıklama" />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="cancelDialog">İptal</VBtn>
          <VBtn color="primary" @click="save">Kaydet</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </VContainer>

</template>



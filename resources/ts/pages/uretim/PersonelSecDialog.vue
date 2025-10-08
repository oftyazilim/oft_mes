<template>
  <VDialog v-model="dialog" max-width="800" @update:model-value="onDialogChange">
    <VCard class="personel-dialog-card">
      <div v-if="loading" class="dialog-loading-overlay d-flex flex-column align-center justify-center">
        <v-progress-circular indeterminate color="primary" size="48" class="mb-3" />
        <span style="font-weight: 600;">Kayıt yapılıyor...</span>
      </div>
      <VCardTitle class="text-h6 d-flex justify-space-between align-center">
        Personel Seçimi
        <div class="text-caption text-end">
          <div><strong>Seçilen:</strong> {{ secilenPersoneller.length }} kişi</div>
        </div>
      </VCardTitle>
      <div class="ps-6">
        <VBtn variant="outlined" @click="EkipAktar()" :disabled="loading">
          <VIcon start icon="tabler-users" />
          Ekip Aktar
        </VBtn>
      </div>

      <VCardText>
        <VRow>
          <!-- Sol Liste -->
          <VCol cols="6">
            <!-- <h5 class="text-subtitle-2">Tüm Personeller</h5> -->
            <DxDataGrid :data-source="kalanPersoneller" :height="600" key-expr="kimlik_no" :show-borders="true"
              :hover-state-enabled="true" @row-click="e => secById(e.data.kimlik_no)">
              <DxColumn data-field="ad_soyad" caption="Ad Soyad" sort-order="asc" />
              <DxColumn data-field="sicil_no" caption="Sicil No" width="100" :visible="false" />
              <DxSearchPanel :visible="true" :highlight-case-sensitive="false" />

              <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always" />

            </DxDataGrid>
          </VCol>

          <!-- Sağ Liste -->
          <VCol cols="6">
            <h5 class="text-subtitle-2 mt-9 mb-2">Ekip Listesi</h5>
            <VList class="border rounded" style="max-block-size: 535px; overflow-y: auto;">
              <VListItem v-for="(p, index) in secilenPersoneller" :key="p.kimlik_no" @click="kaldir(index)"
                class="cursor-pointer">
                <VListItemTitle>{{ p.ad_soyad }}</VListItemTitle>
              </VListItem>
            </VList>
          </VCol>
        </VRow>
      </VCardText>

      <VCardActions>
        <VSpacer />
        <VBtn variant="outlined" color="warning" @click="iptal" :disabled="loading">Vazgeç</VBtn>
        <VBtn @click="kaydet" variant="outlined" color="success" :disabled="loading">Kaydet</VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<script setup lang="ts">
import axios from 'axios'
import dayjs from 'dayjs'
import { DxColumn, DxDataGrid, DxScrolling, DxSearchPanel } from 'devextreme-vue/data-grid'
import { onMounted, ref, watch } from 'vue'

const emit = defineEmits(['kaydedildi', 'iptal'])
const dialog = defineModel<boolean>()
const props = defineProps<{ isemriID: number; istasyonId: number; guid: string }>()

type Personel = {
  register_id: number
  ad_soyad: string
  kimlik_no: string
  sicil_no: string
  istasyon_id: number
}

const tumPersoneller = ref<Personel[]>([])
const kalanPersoneller = ref<Personel[]>([])
const secilenPersoneller = ref<Personel[]>([])
const loading = ref(false)

onMounted(async () => {
  const { data } = await axios.get('/api/personeller')
  tumPersoneller.value = data
})

watch(dialog, async (acik) => {
  if (acik) {
    kalanPersoneller.value = tumPersoneller.value.map(p => ({ ...p }))
    try {
      const response = await axios.get('/api/aktif-ekipler', {
        params: {
          isemriID: props.isemriID,
          guid: props.guid,
        },
      })

      const aktifEkip = Array.isArray(response.data) ? response.data : Object.values(response.data)

      secilenPersoneller.value = aktifEkip.map((p: any) => ({
        register_id: p.register_id,
        ad_soyad: p.personel_full_name,
        kimlik_no: p.citizenship_no,
        istasyon_id: p.istasyon_id,
        sicil_no: p.sicil_no,
      }))

      const secilenIdSet = new Set(secilenPersoneller.value.map(p => p.register_id))
      kalanPersoneller.value = kalanPersoneller.value.filter(p => !secilenIdSet.has(p.register_id))
    } catch (err) {
      console.error('Aktif ekip yüklenemedi:', err)
    }
  }
})

const sec = (index: number) => {
  const secilen = kalanPersoneller.value.splice(index, 1)[0]
  secilenPersoneller.value.push(secilen)
}

const kaldir = (index: number) => {
  const geriAlinan = secilenPersoneller.value.splice(index, 1)[0]
  kalanPersoneller.value.push(geriAlinan)
}

const secById = (register_id: number) => {
  const index = kalanPersoneller.value.findIndex(p => p.register_id === register_id)
  if (index !== -1) sec(index)
}

const kaydet = async () => {
  if (loading.value) return
  loading.value = true
  const now = dayjs().format('YYYY-MM-DD HH:mm:ss')
  try {
    const { data: mevcutKayitlar } = await axios.get('/api/aktif-ekipler', {
      params: { isemriID: props.isemriID, guid: props.guid },
    })

    const mevcutSet = new Set(
      (Array.isArray(mevcutKayitlar) ? mevcutKayitlar : Object.values(mevcutKayitlar))
        .filter((p: any) => p.end_work_time === null)
        .map((p: any) => p.register_id)
    )
    const simdikiSet = new Set(secilenPersoneller.value.map(p => p.register_id))
    const eklenecekler = secilenPersoneller.value.filter(p => !mevcutSet.has(p.register_id))
    const kapatilacaklar = Array.from(mevcutSet).filter(id => !simdikiSet.has(id))

    if (eklenecekler.length) {
      const payload = eklenecekler.map(p => ({
        istasyon_id: props.istasyonId,
        worder_m_id: props.isemriID,
        register_id: p.register_id,
        sicil_no: p.sicil_no,
        personel_full_name: p.ad_soyad,
        citizenship_no: p.kimlik_no,
        co_id: 2517,
        start_work_time: now,
        end_work_time: null,
        guid: props.guid,
      }))
      console.log('Eklenecekler:', payload)
      await axios.post('/api/aktif-ekipler', payload)
    }

    if (kapatilacaklar.length) {
      await axios.put('/api/aktif-ekipler/kapat', {
        register_ids: kapatilacaklar,
        guid: props.guid,
        end_work_time: now,
      })
    }

    emit('kaydedildi')
  } catch (err: any) {
    // Backend dönen hata gövdesini konsola yaz ve kullanıcıya kısa bildirim göster
    const status = err?.response?.status
    const data = err?.response?.data
    console.error('KAYIT HATASI:', status, data || err)
  } finally {
    loading.value = false
    dialog.value = false
  }
}

const iptal = () => {
  dialog.value = false
  emit('iptal')
}

const onDialogChange = (val: boolean) => {
  if (!val) emit('iptal')
}

const EkipAktar = () => {
  const secilenIdSet = new Set(secilenPersoneller.value.map(p => Number(p.register_id)))

  const aktarilacaklar: Personel[] = []

  for (let i = kalanPersoneller.value.length - 1; i >= 0; i--) {
    const p = kalanPersoneller.value[i]
    const id = Number(p.register_id)
    if (p.istasyon_id === props.istasyonId && !secilenIdSet.has(id)) {
      aktarilacaklar.push(p)
      kalanPersoneller.value.splice(i, 1)
    }
  }

  const zatenEklenmisSet = new Set(secilenPersoneller.value.map(p => Number(p.register_id)))
  const temizAktarilacaklar = aktarilacaklar.filter(p => !zatenEklenmisSet.has(Number(p.register_id)))

  secilenPersoneller.value.push(...temizAktarilacaklar)
}
</script>

<style scoped>
.cursor-pointer {
  cursor: pointer;
}

.personel-dialog-card {
  position: relative;
}

.dialog-loading-overlay {
  position: absolute;
  z-index: 50;
  backdrop-filter: blur(2px);
  background: rgba(255, 255, 255, 80%);
  inset: 0;
}
</style>

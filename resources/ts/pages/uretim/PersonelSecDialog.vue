<template>
  <VDialog v-model="dialog" max-width="800" @update:model-value="onDialogChange">
    <VCard class="personel-dialog-card">
      <div v-if="busy" class="dialog-loading-overlay d-flex flex-column align-center justify-center">
        <v-progress-circular indeterminate color="primary" size="48" class="mb-3" />
        <span style="font-weight: 600;">{{ loading ? 'Kayıt yapılıyor...' : 'Yükleniyor...' }}</span>
      </div>
      <VCardTitle class="text-h6 d-flex justify-space-between align-center">
        Personel Seçimi
        <div class="text-caption text-end">
          <div><strong>Seçilen:</strong> {{ secilenPersoneller.length }} kişi</div>
        </div>
      </VCardTitle>
      <div class="ps-6">
        <VBtn variant="outlined" @click="EkipAktar()" :disabled="busy">
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
              :hover-state-enabled="false" :repaint-changes-only="true" :focused-row-enabled="false"
              :auto-navigate-to-focused-row="false" @row-click="onRowClick">
              <DxColumn data-field="ad_soyad" caption="Ad Soyad" sort-order="asc" />
              <DxColumn data-field="sicil_no" caption="Sicil No" width="100" :visible="false" />
              <DxSearchPanel :visible="true" :highlight-case-sensitive="false" />

              <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always" />

              <DxSelection mode="none" />

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
        <VBtn variant="outlined" color="warning" @click="iptal" :disabled="busy">Vazgeç</VBtn>
        <VBtn @click="kaydet" variant="outlined" color="success" :disabled="busy">Kaydet</VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<script setup lang="ts">
import axios from 'axios'
import dayjs from 'dayjs'
import { DxColumn, DxDataGrid, DxScrolling, DxSearchPanel, DxSelection } from 'devextreme-vue/data-grid'
import { computed, onMounted, ref, watch } from 'vue'

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
const personellerHazir = ref(false)
const initializing = ref(false)
const busy = computed(() => loading.value || initializing.value)

function normalizePersonel(p: any): Personel {
  return {
    register_id: Number(p.register_id),
    ad_soyad: String(p.ad_soyad ?? ''),
    kimlik_no: String(p.kimlik_no ?? ''),
    sicil_no: String(p.sicil_no ?? ''),
    istasyon_id: Number(p.istasyon_id ?? 0),
  }
}

async function yuklePersoneller() {
  if (personellerHazir.value) return
  const { data } = await axios.get('/api/personeller')
  const arr = Array.isArray(data) ? data : Object.values(data)
  tumPersoneller.value = arr.map(normalizePersonel)
  personellerHazir.value = true
}

onMounted(async () => {
  try {
    await yuklePersoneller()
  } catch (e) {
    console.error('Personeller yüklenemedi', e)
  }
})

watch(dialog, async (acik) => {
  if (acik) {
    initializing.value = true
    // Dialog açılırken personel listesi hazır değilse yükle
    if (!personellerHazir.value) {
      try { await yuklePersoneller() } catch (e) { console.error('Personeller yüklenemedi', e) }
    }
    // Kalan listeyi tüm personellerden başlat (kopyalayarak)
    kalanPersoneller.value = tumPersoneller.value.map(p => ({ ...p }))
    try {
      const response = await axios.get('/api/aktif-ekipler', {
        params: {
          isemriID: props.isemriID,
          guid: props.guid,
        },
      })

      const raw = response?.data ?? []
      const aktifEkip = Array.isArray(raw) ? raw : Object.values(raw || {})

      secilenPersoneller.value = aktifEkip.map((p: any) => ({
        register_id: Number(p.register_id),
        ad_soyad: p.personel_full_name,
        kimlik_no: String(p.citizenship_no ?? p.kimlik_no ?? ''),
        istasyon_id: Number(p.istasyon_id ?? 0),
        sicil_no: String(p.sicil_no ?? ''),
      }))

      // Seçilenleri citizenship_no (kimlik_no) üzerinden eşleştirip soldan düş
      const secilenKimSet = new Set(
        secilenPersoneller.value
          .map(p => String(p.kimlik_no))
          .filter(v => v.length > 0)
      )
      kalanPersoneller.value = kalanPersoneller.value.filter(p => !secilenKimSet.has(String(p.kimlik_no)))
    } catch (err) {
      console.error('Aktif ekip yüklenemedi:', err)
    } finally {
      initializing.value = false
    }
  } else {
    // Dialog kapanırken sağ ve sol listeleri temizle
    secilenPersoneller.value = []
    kalanPersoneller.value = []
  }
})

const sec = (index: number) => {
  if (busy.value) return
  const secilen = kalanPersoneller.value.splice(index, 1)[0]
  if (!secilen) return
  // Zaten ekli ise tekrar ekleme
  if (secilenPersoneller.value.some(p => p.kimlik_no === secilen.kimlik_no)) return
  secilenPersoneller.value.push(secilen)
}

const kaldir = (index: number) => {
  if (busy.value) return
  const geriAlinan = secilenPersoneller.value.splice(index, 1)[0]
  if (!geriAlinan) return
  const already = kalanPersoneller.value.some(p => p.kimlik_no === geriAlinan.kimlik_no)
  if (!already) kalanPersoneller.value.push(geriAlinan)
}

const secById = (kimlik_no: string) => {
  if (busy.value) return
  // Sağ listede varsa yapma
  if (secilenPersoneller.value.some(p => p.kimlik_no === kimlik_no)) return
  const index = kalanPersoneller.value.findIndex(p => p.kimlik_no === kimlik_no)
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

    const mevcutArr = Array.isArray(mevcutKayitlar) ? mevcutKayitlar : Object.values(mevcutKayitlar || {})
    const aktifler = mevcutArr.filter((p: any) => p && p.end_work_time === null)
    const mevcutKimSet = new Set(
      aktifler.map((p: any) => String(p.citizenship_no ?? p.kimlik_no ?? ''))
    )
    const simdikiKimSet = new Set(secilenPersoneller.value.map(p => String(p.kimlik_no)))
    const eklenecekler = secilenPersoneller.value.filter(p => !mevcutKimSet.has(String(p.kimlik_no)))
    const kapatilacakKimlikler = Array.from(mevcutKimSet).filter(id => !simdikiKimSet.has(String(id)))

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
      await axios.post('/api/ekip-kaydet', payload)
    }

    if (kapatilacakKimlikler.length) {
      await axios.put('/api/aktif-ekipler/kapat', {
        kimlikler: kapatilacakKimlikler,
        guid: props.guid,
        end_work_time: now,
        worder_m_id: props.isemriID,
        istasyon_id: props.istasyonId,
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

const onDialogChange = (_val: boolean) => {
// Kapanışta iptal event'i göndermiyoruz; iptal butonu zaten emit ediyor.
}

const EkipAktar = () => {
  if (busy.value) return
  // citizenship_no (kimlik_no) üzerinden karşılaştır
  const secilenKimSet = new Set(secilenPersoneller.value.map(p => p.kimlik_no))

  const aktarilacaklar: Personel[] = []

  for (let i = kalanPersoneller.value.length - 1; i >= 0; i--) {
    const p = kalanPersoneller.value[i]
    const id = p.kimlik_no
    if (p.istasyon_id === props.istasyonId && !secilenKimSet.has(id)) {
      aktarilacaklar.push(p)
      kalanPersoneller.value.splice(i, 1)
    }
  }

  const zatenEklenmisSet = new Set(secilenPersoneller.value.map(p => p.kimlik_no))
  const temizAktarilacaklar = aktarilacaklar.filter(p => !zatenEklenmisSet.has(p.kimlik_no))

  secilenPersoneller.value.push(...temizAktarilacaklar)
}

function onRowClick(e: any) {
  if (busy.value) return
  secById(e?.data?.kimlik_no)
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

<template>
  <div class="page-rollform pa-0">
    <div class="rollform-header">
      <!-- Üst satır: Gauge | Durum Bilgileri | Üretim Rakamları | Operatör -->
      <VRow class="match-height">

        <VCol cols="12" md="3" sm="6">
          <VCard class="pa-3">
            <!-- Sol: OEE Gauge ve saat/tarih/KPI -->
            <section>
              <VCardTitle class="durum-title" :style="{ backgroundColor: statusColor }">
                {{ worksInfo?.statu_id === 0
                ? 'KAPALI'
                : worksInfo?.statu_id === 1
                ? 'DURUYOR'
                : 'ÇALIŞIYOR' }}
              </VCardTitle>
              <hr>
              <div class="gauge-wrap">
                <div class="badge-left">{{ pageName }}</div>
                <DxCircularGauge class="oee-gauge" :value="worksInfo?.speed" :subvalues="worksInfo?.speed_target"
                  :tooltip="gaugeTooltip" :size="gaugeSize">
                  <DxGeometry :start-angle="225" :end-angle="-45" />
                  <DxScale :start-value="0" :end-value="worksInfo?.speed_max" :tick-interval="10"
                    :minor-tick-interval="5">
                    <DxTick :length="8" color="#666" />
                    <DxMinorTick :length="4" color="#444" />
                    <DxLabel :use-range-colors="true" :font="gaugeLabelFont" />
                  </DxScale>
                  <DxRangeContainer background-color="#20252b" :offset="10" :width="10">
                    <DxRange :start-value="0" :end-value="0.40 * worksInfo?.speed_max" color="#ce6978" />
                    <DxRange :start-value="0.40 * worksInfo?.speed_max" :end-value="0.70 * worksInfo?.speed_max"
                      color="#caa93b" />
                    <DxRange :start-value="0.70 * worksInfo?.speed_max" :end-value="worksInfo?.speed_max"
                      color="#1e8e3e" />
                  </DxRangeContainer>
                  <DxValueIndicator type="triangleNeedle" :spindle-size="18" :spindle-gap-size="9" :offset="10"
                    :width="8" />
                  <!-- <DxTitle text="OEE %" /> -->
                  <DxExport :enabled="false" />
                </DxCircularGauge>
                <div class="gauge-center-value ">
                  <div class="label" style="font-size: 16px; margin-block: -10px;">Hız</div>
                  <div style="color: goldenrod;"> {{ worksInfo?.speed }} </div>
                  <!-- <span class="unit">%</span> -->
                </div>
              </div>

              <div class="clock-wrap">
                <div class="digital-clock">{{ time }}</div>
                <div class="digital-date">{{ dateText }}</div>
              </div>

              <div class="kpi-row">
                <div class="kpi">
                  <div>Meço %</div>
                  <div class="kpi-value">
                    {{ kpi.availability }}
                    <div class="pb-1 ms-1 me-1">
                      <VProgressLinear :model-value="kpi.availability ?? 0" color="info" height="4" :rounded="true" />
                    </div>
                  </div>
                </div>
                <div class="kpi">
                  <div>Performans %</div>
                  <div class="kpi-value">{{ kpi.performance }}
                    <div class="pb-1 ms-1 me-1">
                      <VProgressLinear :model-value="kpi.performance ?? 0" color="success" height="4" :rounded="true" />
                    </div>
                  </div>
                </div>
                <div class="kpi">
                  <div>Kalite %</div>
                  <div class="kpi-value">{{ kpi.quality }}
                    <div class="pb-1 ms-1 me-1">
                      <VProgressLinear :model-value="kpi.quality ?? 0" color="error" height="4" :rounded="true" />
                    </div>
                  </div>
                </div>
              </div>
              <VRow>
                <VCol cols="12" class="text-center">
                  <div class="oee-title">OEE % <span class="kpi-oee" :class="oeeColorClass">{{ kpi.oee }}</span></div>
                </VCol>
              </VRow>
            </section>
          </VCard>
        </VCol>

        <VCol cols="12" md="4" sm="6">
          <VCard class="pa-3">
            <VCardTitle class="panel-title pa-0">DURUM BİLGİLERİ</VCardTitle>
            <hr>
            <!-- Orta: Durum Bilgileri -->
            <section class="panel status-panel">
              <div class="form-row mt-2">
                <label>Duruş Sebebi (F10 – Yeni Duruş)</label>
                <AppSelect v-model="selectedSebep" :items="durusSebepleri" item-title="description"
                  item-value="break_reason_code" return-object single-line placeholder="Seçiniz..." variant="outlined"
                  :disabled="!isDurusModu" :class="{ 'app-select--force-disabled': !isDurusModu }" />
                <AppTextarea v-model="durusAciklamasi" placeholder="Açıklama..." disabled />
              </div>

              <div class="status-actions justify-between">
                <VBtn variant="tonal" color="error" size="small">Açıklama Gir</VBtn>
                <VSwitch color="error" v-model="arizali" label="Arızalı" value="false" :height="10" />
              </div>

              <div class="status-actions justify-between">
                <VBtn variant="tonal" color="warning" size="small" width="48%">
                  F9 - Çay Molası
                  <VIcon end icon="tabler-mug" />
                </VBtn>
                <VBtn variant="tonal" color="warning" size="small" width="48%">
                  F10 - Yemek Molası
                  <VIcon end icon="tabler-bowl-spoon" />
                </VBtn>
              </div>

              <div class="status-metrics mt-4 text-center">
                <div>
                  <div class="label">Durum Süresi</div>
                  <div class="metric-time">{{ durumSuresi }}</div>
                </div>
                <div>
                  <div class="label">Vardiya</div>
                  <div class="metric-time">{{ vardiyaSuresi }}</div>
                </div>
              </div>

              <div class="uclu text-center mt-4">
                <div>
                  <div class="label">Çalışma</div>
                  <div class="metric-time ok">00:00</div>
                </div>
                <div>
                  <div class="label">Duruş</div>
                  <div class="metric-time warn">00:00</div>
                </div>
                <div>
                  <div class="label">Boşta</div>
                  <div class="metric-time">02:00</div>
                </div>
              </div>
            </section>
          </VCard>
        </VCol>

        <VCol cols="12" md="5" sm="6">
          <VCard class="pa-3">
            <VCardTitle class="panel-title pa-0">ÜRETİM RAKAMLARI</VCardTitle>
            <hr>
            <!-- Sağ: Üretim Rakamları -->
            <section class="panel status-panel">
              <VProgressLinear
                :model-value="((worksInfo?.net_qty + worksInfo?.counter) / worksInfo?.order_qty * 100) ?? 0"
                color="warning" height="10" :rounded="true" class="my-4" />

              <VRow>
                <VCol cols="5">
                  <div>
                    <div width="60%">
                      <div class="label">İş&nbsp;Emri&nbsp;Miktarı</div>
                      <div class="sayac siparis digit">{{ fmt0(worksInfo?.order_qty) }}</div>
                    </div>
                    <div width="60%">
                      <div class="label">Üretilen (uyumsoft)</div>
                      <div class="sayac siparis digit">{{ fmt0(worksInfo?.net_qty) }}</div>
                    </div>
                    <div width="60%">
                      <div class="label">Sayaç</div>
                      <div class="sayac net digit">{{ fmt0(worksInfo?.counter) }}</div>
                    </div>
                    <div width="60%">
                      <div class="label">Üretilen (uyum + sayaç)</div>
                      <div class="sayac net digit">{{ fmt0(worksInfo?.net_qty + worksInfo?.counter) }}</div>
                    </div>

                    <div width="60%">
                      <div class="label">Kalan</div>
                      <div class="sayac kalan digit">{{ fmt0((worksInfo?.order_qty ?? 0) - worksInfo?.counter -
                        (worksInfo?.net_qty ?? 0)) }}</div>
                    </div>

                  </div>
                </VCol>

                <VCol cols="7">
                  <div>

                    <VRow>
                      <VCol cols="6">
                        <div class="label">Hurda</div>
                        <div class="sayac hurda digit">{{ fmt0(worksInfo?.scrap_qty) }}</div>
                      </VCol>
                      <VCol cols="6">
                        <div class="label">O.A.%</div>
                        <div class="sayac net digit">{{ worksInfo?.net_qty === 0 || worksInfo?.order_qty === 0 ? 0 :
                          ((worksInfo?.net_qty + worksInfo?.counter) / worksInfo?.order_qty * 100).toFixed(0) }}</div>
                      </VCol>
                    </VRow>


                    <div class="mt-10">
                      <div>İş Emri No:</div>
                      <div class="info">{{ worksInfo?.worder_no }}</div>
                    </div>
                    <div width="60%">
                      <div>Ürün Kodu:</div>
                      <div class="info">{{ worksInfo?.item_code }}</div>
                    </div>
                    <div width="60%">
                      <div>Ürün Adı:</div>
                      <div class="info">{{ worksInfo?.item_name }}</div>
                    </div>
                  </div>
                  <div width="60%">
                    <div>Ürün Boyu:</div>
                    <div class="info">{{ fmt0(worksInfo?.item_length) }}</div>
                  </div>

                  <div class="status-actions mt-11 mb-0">
                    <VBtn id="hurdaGir" variant="outlined" color="error" width="100%" @click="openHurdaDialog">
                      F8 - Hurda Girişi
                      <VIcon end icon="tabler-trash" />
                    </VBtn>
                  </div>

                </VCol>

              </VRow>

            </section>
          </VCard>
        </VCol>

      </VRow>
    </div>

    <!-- Hurda Giriş Dialog -->
    <VDialog v-model="hurdaDialog" max-width="400">
      <VCard>
        <VCardTitle>Hurda Girişi</VCardTitle>
        <VCardText>
          <VTextField ref="hurdaInputRef" v-model="hurdaInput" label="Hurda Miktarı" type="number" hide-details
            autofocus @keydown.enter.prevent="kaydetHurda" @keydown.esc.prevent="cancelHurda" />
          <div class="text-caption mt-2">Enter = Kaydet, Esc = İptal</div>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" color="grey" @click="cancelHurda">İptal (Esc)</VBtn>
          <VBtn variant="elevated" color="error" @click="kaydetHurda">Kaydet (Enter)</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>





    <!-- Aksiyonlar ve tablo alanı -->
    <div class="mid-actions">
      <div class="action-left">
        <button class="btn">İş Emrini Kapat</button>
        <button class="btn btn-primary">Çalışmaya Başla</button>
        <button class="btn">Operasyon Gir</button>
        <button class="btn">Teknik Resim</button>
        <button class="btn">Ürün Etiketi Bas</button>
        <button class="btn icon">🔍 Ara</button>
      </div>
      <div class="action-right">
        <button class="btn btn-warn">Vardiya Bitir</button>
        <button class="btn icon">🟨</button>
        <button class="btn icon">🗂️</button>
        <button class="btn icon">⏻</button>
      </div>
    </div>

    <section class="panel grid-panel">
      <DxDataGrid class="orders-grid" :data-source="isEmirleri" :hover-state-enabled="true"
        :row-alternation-enabled="false" :show-borders="false" :column-auto-width="true"
        :column-resizing-mode="'nextColumn'" :column-min-width="80" height="360">
        <DxColumn type="buttons" width="30" :buttons="actionButtons" />
        <DxColumn data-field="grp" caption="GRP" width="60" :visible="false" />
        <DxColumn data-field="isEmriID" caption="İŞ EMRİ ID" width="140" />
        <DxColumn data-field="isEmriNo" caption="İŞ EMRİ NO" width="140" />
        <DxColumn data-field="stokKodu" caption="STOK KODU" width="120" />
        <DxColumn data-field="stokAdi" caption="STOK ADI" />
        <DxColumn data-field="plnAd" caption="PLN.AD" width="90" data-type="number" :format="{
          type: 'fixedPoint',
          precision: 0,
          thousandsSeparator: ',',
        }" />
        <DxColumn data-field="ilvAd" caption="İLV.AD" width="90" data-type="number" :format="{
          type: 'fixedPoint',
          precision: 0,
          thousandsSeparator: ',',
        }" />
        <DxColumn data-field="klnAd" caption="KLN.AD" width="90" data-type="number" :format="{
          type: 'fixedPoint',
          precision: 0,
          thousandsSeparator: ',',
        }" />
        <DxColumn data-field="urtAd" caption="ÜRT.AD" width="90" data-type="number" :format="{
          type: 'fixedPoint',
          precision: 0,
          thousandsSeparator: ',',
        }" />
        <DxColumn data-field="hurda" caption="HURDA" width="90" data-type="number" :format="{
          type: 'fixedPoint',
          precision: 0,
          thousandsSeparator: ',',
        }" />
        <DxColumn data-field="plnSure" caption="PLN.SÜRE" width="100" data-type="number" :format="{
          type: 'fixedPoint',
          precision: 0,
          thousandsSeparator: ',',
        }" />
        <DxColumn data-field="grcSure" caption="GRÇ.SÜRE" width="100" data-type="number" :format="{
          type: 'fixedPoint',
          precision: 0,
          thousandsSeparator: ',',
        }" />
      </DxDataGrid>
    </section>

    <!-- Alt sekmeler -->
    <div class="bottom-tabs">
      <button class="tab active">İş Emirleri</button>
      <button class="tab">Duruşlar</button>
      <button class="tab">Hurdalar</button>
      <button class="tab">Ölçümler</button>
      <button class="tab">Kısayollar</button>
    </div>

    <!-- İş Emri Aktivasyon Diyaloğu -->
    <VDialog v-model="activateDialog" max-width="420" @keydown.esc.prevent.stop="activateDialog = false">
      <VCard>
        <VCardTitle>İş Emrini Aktif Et</VCardTitle>
        <VCardText>
          <form @submit.prevent="confirmActivate">
            <div class="mb-2"><strong>{{ activateRow?.isEmriNo }}</strong> – {{ activateRow?.stokKodu }}</div>
            <div class="mb-2">{{ activateRow?.stokAdi }}</div>
            <VTextField ref="activateLengthInput" v-model.number="activateLength" type="number" label="Ürün Boyu (mm)"
              variant="outlined" density="compact" autofocus @keydown.enter.prevent="confirmActivate"
              @keydown.esc.prevent.stop="activateDialog = false" />
            <button type="submit" style="display: none;"></button>
          </form>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="activateDialog = false">İptal</VBtn>
          <VBtn color="primary" variant="flat" @click="confirmActivate" :disabled="!activateLength">Onayla</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Duruş Sebebi Seçim Popup (ProductionCard akışı) -->
    <DxPopup v-model:visible="popupDurusSecGosterVisible" :width="500" :height="200" :hide-on-outside-click="false"
      :show-close-button="false" :drag-enabled="false" :close-on-back-button="false" :defer-rendering="false"
      :focus-state-enabled="false" :shading="true" :shading-color="'rgba(0,0,0,0.5)'" :on-hiding="onPopupHiding"
      titleTemplate="title">
      <template #content>
        <VRow class="pa-4">
          <AppSelect v-model="selectedSebep" :items="durusSebepleri" item-title="description"
            item-value="break_reason_code" return-object label="Duruş Sebebi" single-line placeholder="Seçiniz..."
            variant="outlined" :disabled="!isDurusModu" :class="{ 'app-select--force-disabled': !isDurusModu }" />
        </VRow>
      </template>
      <template #title>
        <p class="popup-title">Duruş Sebebini Giriniz</p>
      </template>
      <DxToolbarItem widget="dxButton" toolbar="bottom" location="center"
        :options="{ ...kaydetOptions, onClick: durusSebebiKaydet, disabled: durusKayitLoading }" />

    </DxPopup>
  </div>
</template>


<script setup lang="ts">
import { usePageTitleStore } from '@/stores/pageTitle';
import axios from 'axios';
import DxCircularGauge, {
  DxExport,
  DxGeometry,
  DxLabel,
  DxMinorTick,
  DxRange,
  DxRangeContainer,
  DxScale,
  DxTick,
  DxValueIndicator
} from 'devextreme-vue/circular-gauge';
import DxDataGrid, { DxColumn } from 'devextreme-vue/data-grid';
import { DxPopup, DxToolbarItem } from 'devextreme-vue/popup';
import notify from 'devextreme/ui/notify';
import { computed, nextTick, onActivated, onMounted, onUnmounted, ref, watch } from 'vue';

const pageTitleStore = usePageTitleStore();

const selectedSebep = ref<{
  break_reason_code: string;
  description: string;
} | null>(null);
// Duruş sebebini sayfa yenilemelerine karşı korumak için storage key helper
function reasonStorageKey() {
  try {
    return `rollform:lastDurusReason:${userData?.value?.istasyon_id ?? 'unknown'}`
  } catch { return 'rollform:lastDurusReason:unknown' }
}
function restoreLastReason(): boolean {
  try {
    const raw = localStorage.getItem(reasonStorageKey())
    if (!raw) return false
    const obj = JSON.parse(raw)
    if (obj && obj.break_reason_code && obj.break_reason_code !== '000') {
      // Sadece geçerli (placeholder olmayan) kodları geri yükle
      selectedSebep.value = { break_reason_code: obj.break_reason_code, description: obj.description }
      return true
    }
  } catch { /* ignore */ }
  return false
}
const durusSebepleri = ref([]);
const durusAciklamasi = ref('');
const arizali = ref(false);

const format = {
  type: 'thousands',
  precision: 1,
};
const customizeText = ({ valueText }: { valueText: string }) => `${valueText} °C`;
const worksInfo = ref<{
  station_id: number;
  statu_id: number;
  speed: number;
  speed_max: number;
  speed_target: number;
  counter: number;
  worder_id: number;
  worder_no: string;
  item_id: number;
  item_code: string;
  item_name: string;
  item_length: number;
  order_qty: number;
  net_qty: number;
  scrap_qty: number;
  wstation_code?: string;
  wstation_name: string;
  // API statu_time: ISO string | epoch(ms/sec) | elapsed seconds;APIstatu_time
  statu_time?: number | string | Date | null
} | null>(null)

// Başlık: istasyon bilgilerine bağla
const pageName = computed(() => worksInfo.value?.wstation_code ?? '...')
const pageAlias = computed(() => worksInfo.value?.wstation_name ?? '...')

const statusColor = computed(() => {
  const id = worksInfo.value?.statu_id
  if (id === 0) return 'rgba(192, 167, 167, 0.30)'
  if (id === 1) return 'rgba(180, 50, 50, 1.00)'
  if (id === 2) return 'green'
  return '#4b7027' // varsayılan (eski stil)
})

// Duruş modu: sadece statu_id === 1 iken seçim yapılabilsin
const isDurusModu = computed(() => worksInfo.value?.statu_id === 1)

pageTitleStore.setTitle(`${pageName.value} (${pageAlias.value})`)
document.title = `OFT - ${pageName.value} | ${pageAlias.value}`

// Başlık her zaman görünsün: aktif olduğunda ve başlık değiştiğinde geri set et
const desiredTitle = computed(() => `${pageName.value} (${pageAlias.value})`)
// OEE renk sınıfı
const oeeColorClass = computed(() => {
  const v = Number(kpi.value.oee || 0)
  if (v < 50) return 'oee-low'
  if (v < 70) return 'oee-mid'
  if (v < 85) return 'oee-high'
  return 'oee-top'
})
const applyPageTitle = () => {
  pageTitleStore.setTitle(desiredTitle.value)
  document.title = `OFT - ${pageName.value} | ${pageAlias.value}`
}
onMounted(async () => {
  await nextTick()
  applyPageTitle()
  // statu_time bazlı Durum Süresi'ni ilk anda hesapla
  computeDurumSuresi()
  // İlk KPI
  fetchKpiDirect()
  kpiTimer = setInterval(fetchKpiDirect, 10000)

  // Demo: zamanı canlı akıt ve worksInfo'yu periyodik çek
  timer = setInterval(() => {
    const now = new Date()
    const hh = String(now.getHours()).padStart(2, '0')
    const mm = String(now.getMinutes()).padStart(2, '0')
    const ss = String(now.getSeconds()).padStart(2, '0')
    time.value = `${hh}:${mm}:${ss}`

    const days = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi']
    const months = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık']
    dateText.value = `${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()} ${days[now.getDay()]}`

    // statu_time'a bağlı Durum Süresi'ni her saniye güncelle
    computeDurumSuresi()
  }, 1000)

  info = setInterval(() => {
    fetchWorksInfo()
  }, 1000)

  // Sayfa yüklenince iş emirlerini getir
  fetchIsEmirleri()
  // Duruş sebeplerini de ilk yüklemede al
  durusSebepleriniAl()
  window.addEventListener('keydown', handleShortcut)
  window.addEventListener('keydown', handleHurdaShortcut)
})

onActivated(() => {
  applyPageTitle()
})

const stopTitleWatch = watch(
  () => (pageTitleStore as any).title,
  (t: string) => {
    if (t !== desiredTitle.value)
      applyPageTitle()
  },
  { flush: 'post' }
)

onUnmounted(() => {
  stopTitleWatch()
  window.removeEventListener('keydown', handleShortcut)
  window.removeEventListener('keydown', handleHurdaShortcut)
})

// worksInfo değişince başlığı ve Durum Süresi'ni güncelle
watch(() => worksInfo.value, () => { applyPageTitle(); computeDurumSuresi() })

const userData = useCookie<any>("userData");

// Basit sayı biçimlendirme: binlik ayraç ve 0 ondalık (tr-TR)
function fmt0(v: unknown): string {
  const n = Number(v ?? 0)
  if (!Number.isFinite(n)) return '0'
  return new Intl.NumberFormat('tr-TR', { maximumFractionDigits: 0, minimumFractionDigits: 0 }).format(Math.floor(n))
}

const durumSuresi = ref('00:00:00')
const vardiyaSuresi = ref('00:00')
const time = ref('00:00:00')
const dateText = ref('...')

// statu_time -> HH:mm:ss hesaplama
function toHHMMSS(totalSeconds: number): string {
  const sec = Math.max(0, Math.floor(totalSeconds || 0))
  const h = Math.floor(sec / 3600)
  const m = Math.floor((sec % 3600) / 60)
  const s = sec % 60
  const pad = (n: number) => String(n).padStart(2, '0')
  return `${pad(h)}:${pad(m)}:${pad(s)}`
}
function computeDurumSuresi() {
  const v = worksInfo.value?.statu_time as unknown
  if (v == null) { durumSuresi.value = '00:00:00'; return }
  let seconds = 0
  if (typeof v === 'number') {
    if (v > 1e12) {
      // epoch ms
      seconds = Math.max(0, Math.floor((Date.now() - v) / 1000))
    } else if (v > 1e9) {
      // epoch sec
      seconds = Math.max(0, Math.floor(Date.now() / 1000 - v))
    } else {
      // already elapsed seconds
      seconds = Math.max(0, Math.floor(v))
    }
  } else if (typeof v === 'string') {
    const t = Date.parse(v)
    if (!Number.isNaN(t)) seconds = Math.max(0, Math.floor((Date.now() - t) / 1000))
  } else if (v instanceof Date) {
    seconds = Math.max(0, Math.floor((Date.now() - v.getTime()) / 1000))
  }
  durumSuresi.value = toHHMMSS(seconds)
}

function findSebepByDescription(substr: string) {
  const lower = substr.toLowerCase()
  return (durusSebepleri.value as any[]).find(s => String(s.description || '').toLowerCase().includes(lower))
}

function handleShortcut(e: KeyboardEvent) {
  if (!isDurusModu.value) return
  if (e.key === 'F9') {
    const tea = findSebepByDescription('çay ve yemek') || findSebepByDescription('cay ve yemek') || findSebepByDescription('çay & yemek')
    if (tea) {
      selectedSebep.value = { break_reason_code: tea.break_reason_code, description: tea.description }
      e.preventDefault()
    }
  } else if (e.key === 'F10') {
    const combo = findSebepByDescription('çay ve yemek') || findSebepByDescription('cay ve yemek') || findSebepByDescription('çay & yemek')
    if (combo) {
      selectedSebep.value = { break_reason_code: combo.break_reason_code, description: combo.description }
      e.preventDefault()
    }
  }
}

// Hurda popup state ve işlemleri
const hurdaDialog = ref(false)
const hurdaInput = ref('')
const hurdaInputRef = ref<HTMLInputElement | null>(null)

function openHurdaDialog() {
  hurdaInput.value = ''
  hurdaDialog.value = true
  nextTick(() => hurdaInputRef.value?.focus())
}

async function kaydetHurda() {
  const qty = Number(hurdaInput.value)
  if (!Number.isFinite(qty) || qty <= 0) { cancelHurda(); return }
  try {
    await axios.post('/api/uretim-rollform/hurda-gir', { station_id: userData.value?.istasyon_id, qty })
    hurdaDialog.value = false
    fetchWorksInfo()
    fetchKpiDirect()
  } catch (e) { console.error('hurda kayıt hata', e) }
}

function cancelHurda() { hurdaInput.value = ''; hurdaDialog.value = false }
function handleHurdaShortcut(e: KeyboardEvent) { if (e.key === 'F8') { openHurdaDialog(); e.preventDefault() } }

// Durum bilgileri
const durusSebebi = ref('uretim-disi')
// Yeni KPI alanları
const kpiLoaded = ref(false)
const kpi = ref({ availability: 0, performance: 0, quality: 0, oee: 0 })
const shiftInfo = ref<{ id: string; start: string; end: string } | null>(null)

async function fetchKpiDirect() {
  try {
    const stationId = userData.value?.istasyon_id
    if (!stationId) return
    const { data } = await axios.get('/api/uretim-rollform/kpi', { params: { station_id: stationId } })
    if (data?.kpi) {
      const pct = (v: any) => Math.round(Number(v || 0) * 100)
      kpi.value = {
        availability: pct(data.kpi.availability),
        performance: pct(data.kpi.performance),
        quality: pct(data.kpi.quality),
        oee: pct(data.kpi.oee),
      }
      if (data.shift) {
        shiftInfo.value = {
          id: data.shift.id,
          start: data.shift.start.substring(11, 16),
          end: data.shift.end.substring(11, 16)
        }
      }
      kpiLoaded.value = true
    }
  } catch (err) {
    console.error('fetchKpiDirect error', err)
  }
}

// Grid satırları (örnek)
type Row = {
  isEmriID: number
  grp: number
  isEmriNo: string
  stokKodu: string
  stokAdi: string
  plnAd: number
  ilvAd: number
  klnAd: number
  urtAd: number
  hurda: number
  plnSure: number
  grcSure: number
}
const isEmirleri = ref<Row[]>([])

// Grid 'Aktif Et' aksiyon butonu
const actionButtons = [{
  hint: 'Aktif Et',
  text: 'Aktif Et',
  icon: 'login',
  onClick: (e: any) => openActivateDialog(e?.row?.data as Row),
}]

// Aktivasyon diyaloğu state
const activateDialog = ref(false)
const activateLength = ref<number | null>(null)
const activateRow = ref<Row | null>(null)

function openActivateDialog(row: Row) {
  activateRow.value = row
  activateLength.value = null
  activateDialog.value = true
}

async function confirmActivate() {
  if (!activateRow.value) return
  const length = Number(activateLength.value ?? 0)
  const stationId = userData.value?.istasyon_id
  if (!stationId) return
  const payload = {
    wstation_id: stationId,
    worder_id: Number(activateRow.value.isEmriID ?? 0),
    worder_no: activateRow.value.isEmriNo,
    item_id: 0,
    item_code: activateRow.value.stokKodu,
    item_name: activateRow.value.stokAdi,
    item_length: length,
    order_qty: Number(activateRow.value.plnAd ?? 0),
    net_qty: Number(activateRow.value.urtAd ?? 0),
    scrap_qty: Number(activateRow.value.hurda ?? 0),
  }
  try {
    await axios.post('/api/uretim-rollform/activate-workorder', payload)
    activateDialog.value = false
    // Aktif iş emri ve sayıları tazele
    fetchWorksInfo()
  } catch (e) {
    console.error('Aktivasyon hatası', e)
  }
}

let timer: ReturnType<typeof setInterval> | null = null
let info: ReturnType<typeof setInterval> | null = null
let kpiTimer: ReturnType<typeof setInterval> | null = null
let selectedSebepSaveTimer: ReturnType<typeof setTimeout> | null = null

async function fetchWorksInfo() {
  try {
    if (!userData.value.istasyon_id) { worksInfo.value = null; return }
    const res = await axios.get('/api/kapasite-works-info', { params: { station_id: userData.value.istasyon_id } })
    const arr = Array.isArray(res.data) ? res.data : []
    worksInfo.value = arr.length > 0 ? arr[0] : null
    computeDurumSuresi()
  } catch (e) {
    console.error('WorksInfo alınamadı', e)
    worksInfo.value = null
  }
}

onUnmounted(() => {
  if (timer) clearInterval(timer)
  if (info) clearInterval(info)
  if (kpiTimer) clearInterval(kpiTimer)
  if (selectedSebepSaveTimer) clearTimeout(selectedSebepSaveTimer)
})

async function fetchIsEmirleri() {
  try {
    const istasyon = userData.value?.istasyon_kodu ?? '1511'
    const { data } = await axios.get('/api/uretim-rollform/is-emirleri', { params: { istasyon } })
    const mapped: Row[] = (data || []).map((r: any) => ({
      grp: 0,
      isEmriID: r.isemri_id,
      isEmriNo: r.isemri_no,
      stokKodu: r.stok_kodu,
      stokAdi: r.stok_adi,
      plnAd: Number(r.siparis_miktari ?? 0),
      ilvAd: 0,
      klnAd: Number(r.kalan_miktar ?? 0),
      urtAd: Number(r.uretilen_net_miktar ?? 0),
      hurda: Number(r.toplam_hurda_miktari ?? 0),
      plnSure: Number(r.operasyon_suresi ?? 0),
      grcSure: 0,
    }))
    isEmirleri.value = mapped
  } catch (e) {
    console.error('İş emirleri alınamadı', e)
  }
}

// --- Duruş Sebepleri Akışı (ProductionCard'dan port) ---
const popupDurusSecGosterVisible = ref(false)
const durusKayitLoading = ref(false)
const allowPopupClose = ref(true)
// Sayfa yenilemesi ile gelen ilk statu değerlendirmesini ayırt etmek için
const firstStatuEvaluation = ref(true)

const kaydetOptions = {
  width: 100,
  marginBottom: 30,
  type: 'success',
  text: 'Kaydet',
  stylingMode: 'contained',
}

function durusSebepleriniAl() {
  if (!userData.value?.istasyon_id) return
  axios.get('/api/durus-sebepleri-al', { params: { istasyon: userData.value.istasyon_id } })
    .then(r => { durusSebepleri.value = r.data?.durusSebepleri || [] })
    .catch(err => { console.error('Duruş sebepleri alınamadı', err) })
}

function durusSebebiGir() {
  // Varsayılan placeholder
  selectedSebep.value = { break_reason_code: '000', description: 'GİRİLMEDİ' }
  popupDurusSecGosterVisible.value = true
}

async function durusSebebiKaydet() {
  if (durusKayitLoading.value) return
  if (!selectedSebep.value) {
    notify({ message: 'Lütfen bir duruş sebebi seçiniz.', type: 'error', displayTime: 3000 })
    return
  }
  if (!worksInfo.value?.worder_id) {
    notify({ message: 'Aktif iş emri yok.', type: 'warning', displayTime: 2500 })
    popupDurusSecGosterVisible.value = false
    return
  }
  try {
    durusKayitLoading.value = true
    allowPopupClose.value = false
    await axios.post('/api/duruskaydet-mekanik', {
      istasyonID: userData.value.istasyon_id,
      selectedDurus: selectedSebep.value,
    })
    notify({ message: 'Duruş kaydedildi.', type: 'success', displayTime: 2000 })
    // Persist edileni güncelle
    try { localStorage.setItem(reasonStorageKey(), JSON.stringify(selectedSebep.value)) } catch { /* ignore */ }
  } catch (err) {
    console.error('Duruş kaydedilemedi', err)
    notify({ message: 'Duruş kaydedilemedi.', type: 'error', displayTime: 3000 })
  } finally {
    durusKayitLoading.value = false
    allowPopupClose.value = true
    popupDurusSecGosterVisible.value = false
    fetchWorksInfo()
  }
}

function onPopupHiding(e: any) {
  if (!allowPopupClose.value) e.cancel = true
}

// Popup kapanınca sebep seçildiyse (ProductionCard emit karşılığı) ekstra refresh
watch(popupDurusSecGosterVisible, (y, o) => { if (o && !y) fetchWorksInfo() })

// (İkinci onMounted kaldırıldı; yukarıda birleşik çağrı var)

// Statu 1 (DURUYOR) olduğunda popup'ı otomatik aç (tekrar tekrar açmayı engelle)
const lastStatuId = ref<number | null>(null)
watch(() => worksInfo.value?.statu_id, async (nv, ov) => {
  // statu 1'e yeni geçiş -> popup aç
  if (nv === 1 && lastStatuId.value !== 1) {
    if (firstStatuEvaluation.value) {
      // Sadece ilk değerlendirmede (sayfa yeni yüklendi) restore dene
      const restored = restoreLastReason()
      if (!restored && !popupDurusSecGosterVisible.value) durusSebebiGir()
    } else {
      // Runtime'da statu üretim->duruş geçti, her zaman kullanıcı seçsin
      if (!popupDurusSecGosterVisible.value) durusSebebiGir()
    }
  } else if (nv !== 1) {
    // statu 1 dışına çıktıysa ve popup açıksa otomatik kaydet + kapat
    if (popupDurusSecGosterVisible.value) {
      if (selectedSebep.value) {
        try {
          await durusSebebiKaydet()
        } catch (e) {
          // Kaydetme hata verirse en azından popup'ı kapatalım (isteğe göre iptal edilebilir)
          popupDurusSecGosterVisible.value = false
        }
      } else {
        popupDurusSecGosterVisible.value = false
      }
    }
    // Ardından seçimi sıfırla
    selectedSebep.value = null
  }
  lastStatuId.value = nv ?? null
  if (firstStatuEvaluation.value) firstStatuEvaluation.value = false
})

// Seçilen duruş sebebi değiştiğinde otomatik kaydet (debounce 250ms)
watch(selectedSebep, (nv, ov) => {
  if (selectedSebepSaveTimer) clearTimeout(selectedSebepSaveTimer)
  if (!nv) return
  selectedSebepSaveTimer = setTimeout(async () => {
    // Koşulları timer anında tekrar doğrula
    if (!selectedSebep.value) return
    if (worksInfo.value?.statu_id !== 1) return
    if (durusKayitLoading.value) return
    if (selectedSebep.value.break_reason_code === '000') return
    try {
      await durusSebebiKaydet()
// Kaydet başarılı ise localStorage zaten durusSebebiKaydet içinde güncellendi
    } catch (e) {
      /* sessiz */
    }
  }, 250)
})
</script>

<style scoped>
/* stylelint-disable order/properties-order */
.page-rollform {
  display: flex;
  flex-direction: column;
  gap: 12px;
  padding-block: 10px;
  padding-inline: 10px;
}

.panel-title {
  display: flex;
  align-items: center;
  justify-content: center;

  /* color: #c8cbd6; */
  font-weight: 700;
  margin-block-end: 8px;
}

.durum-title {
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px 10px 0 0;
  border-style: 3px yellow solid;
  background-color: #4b7027;
  font-weight: 700;
  font-size: 20px;
  margin-block-end: 8px;
  padding: 1%;
  margin: 0%;
  text-align: center;
}

.badge-left {
  position: absolute;
  inset-block-start: 10px;
  inset-inline-start: 6px;
  z-index: 2;

  /* transform: rotate(-45deg); */
  color: rgb(217, 185, 5);
  font-weight: 800;
  font-size: 24px;
}

.top-grid {
  display: grid;
  gap: 10px;
  grid-template-columns: 420px 1fr 520px 280px;
}

/* Gauge */
.gauge-panel {
  position: relative;
}

.gauge-wrap {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* stylelint-disable-next-line selector-pseudo-class-no-unknown */
.oee-gauge :deep(.dxc-val-indicator path) {
  filter: drop-shadow(0 0 3px rgba(207, 216, 220, 80%));
  stroke: #cfd8dc;
  stroke-width: 2.5px;
}

.gauge-center-value {
  position: absolute;
  font-size: 50px;
  font-weight: 700;

  /* text-shadow: 0 0 8px rgba(0, 0, 0, 60%); */
  inset-block-start: 200px;
}

.gauge-center-value .unit {
  display: block;
  font-size: 16px;
  opacity: 0.8;
}

.clock-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  margin-block-start: -25px;
}

.digital-clock {
  font-family: "Segoe UI", Verdana, "Helvetica Neue", Arial, sans-serif;
  font-size: 22px;
  font-weight: 700;
  padding-block: 4px;
  padding-inline: 15px;
  box-shadow: inset 0 0 8px rgba(0, 0, 0, 60%), 0 0 6px rgba(112, 225, 255, 30%);
  border-radius: 6px;
}

.digital-date {
  /* color: #98a6b3; */
  font-size: 13px;
}

.kpi-row {
  display: grid;
  gap: 8px;
  grid-template-columns: repeat(3, 1fr);
  margin-block-start: 0;
}

.kpi {
  text-align: center;
  padding: 0%;
  margin-block: 5px -10px;
}

.kpi-value {
  /* background-color: #2a3142; */
  font-family: "Segoe UI", Verdana, "Helvetica Neue", Arial, sans-serif;
  font-size: 28px;
  font-weight: 600;
  border: 1px solid;
  border-radius: 8px;
}

.kpi-oee {
  /* background-color: #2a3142; */
  font-family: "Segoe UI", Verdana, "Helvetica Neue", Arial, sans-serif;
  font-size: 44px;
  font-weight: 600;
}

.oee-low {
  color: #d32f2f !important;
}

.oee-mid {
  color: #caa93b !important;
}

.oee-high {
  color: #ff9800 !important;
}

.oee-top {
  color: #4cb651 !important;
}

.oee-title {
  /* background-color: #2a3142; */
  font-family: "Segoe UI", Verdana, "Helvetica Neue", Arial, sans-serif;
  font-size: 18px;
  font-weight: 600;
  margin-block: 5px -20px;
}

/* Durum Paneli */
.status-panel .form-row {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.select-dark {
  border-radius: 8px;
  padding: 8px;
}

.status-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-block: 8px;
}

.status-actions.justify-between {
  justify-content: space-between;
  align-items: center;
}

.status-metrics {
  display: grid;
  gap: 8px;
  grid-template-columns: 1fr 1fr;
}

.metric-box {
  /* background: #101521; */
  border: 1px solid;
  border-radius: 8px;
  padding: 8px;
}

.metric-title {
  color: #a5adbd;
  font-size: 12px;
  margin-block-end: 2px;
}

.metric-time {
  /* background-color: #2a3142; */
  font-family: "Segoe UI", Verdana, "Helvetica Neue", Arial, sans-serif;
  font-size: 28px;
  font-weight: 800;
  border: 1px solid;
  border-radius: 8px;
}

.metric-time.warn {
  color: #e88181;
}

.metric-time.ok {
  color: #38d39f;
}

.metric-time.tum {
  color: #9fbcf2;
}

.metric-time.kalan {
  color: #b5af59;
}

.digit {
  font-size: 40px;
}

.uclu {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
  margin-block-start: 8px;
}

.ikili {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 8px;
  margin-block-start: 8px;
}

.timer {
  /* background: #101521; */
  border: 1px solid #80848e;
  border-radius: 8px;
  padding: 8px;
  text-align: center;
}

.kare {
  background: #4fe96d;
  inline-size: 50px;
  block-size: 30px;
  border: 1px solid #80848e;
  border-radius: 8px;
  padding: 8px;
}

.timer-title {
  color: #a5adbd;
  font-size: 12px;
  margin-block-end: 2px;
}

.timer-value {
  font-weight: 800;
  color: #ffc107;
}

.timer-value.ok {
  color: #38d39f;
}

.timer-value.warn {
  color: #ff7043;
}

/* Üretim Paneli */
.production-panel .prod-row {
  display: grid;
  align-items: center;
  gap: 10px;
  grid-template-columns: 1fr auto;
  margin-block-start: 8px;
}

.prod-box {
  /* background: #101521; */
  border: 1px solid;
  border-radius: 8px;
  padding: 8px;
}

.prod-box.big {
  padding: 12px;
}

.prod-title {
  color: #a5adbd;
  font-size: 12px;
  margin-block-end: 6px;
}

.prod-value {
  font-weight: 800;
  font-size: 26px;
}

.prod-value.accent {
  color: #ffd54f;
}

.prod-value.big {
  font-size: 64px;
  color: #52ff6a;
}

.prod-value.remain {
  font-size: 54px;
  color: #ffd54f;
}

.sayac {
  /* background-color: #2a3142; */
  font-size: 40px;
  font-weight: 800;
  text-align-last: center;
  border-radius: 10px;
  border: rgb(131, 114, 23) solid 1px;
}

.sayac.net {
  color: #14d417;
}

.sayac.hurda {
  color: #ff7043;
}

.sayac.siparis {
  color: #437bff;
}

.sayac.kalan {
  color: #ffc400;
}

.info {
  /* background-color: #2a3142; */
  font-size: 24px;
  font-weight: 800;
  text-align-last: left;
  block-size: 30px;

  /* border-bottom: 2px solid hsl(0, 38%, 88%); */
}

.info-urun-adi {
  /* background-color: #2a3142; */
  font-size: 16px;
  font-weight: 800;
  text-align-last: left;
  block-size: 30px;

  /* border-bottom: 2px solid hsl(0, 38%, 88%); */
}

.vr-counters {
  display: flex;
  gap: 8px;
}

.vr-box {
  background: #2a3142;
  border-radius: 8px;
  color: #e6eaf2;
  padding-block: 8px;
  padding-inline: 8px;
}

.vr-box span {
  font-weight: 800;
  margin-inline-start: 6px;
}

.vr-box.danger {
  background: #5c2a2a;
}

.level-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
}

.level-title {
  font-size: 14px;
  color: #a5adbd;
}

.prod-foot {
  display: grid;
  grid-template-columns: 1fr 4fr 2fr;
  gap: 8px;
  margin-block-start: 8px;
}

.input-like {
  /* background: #d2b200; */

  /* color: #161616; */
  border-radius: 8px;
  padding-block: 8px;
  padding-inline: 10px;
  font-size: 20px;
  font-weight: bold;
  text-align: start;
}

/* Operatör Paneli */
.operator-panel .avatar-card {
  /* background: #101521; */
  border: 1px solid #2a3142;
  border-radius: 10px;
  display: grid;
  grid-template-columns: 70px 1fr;
  align-items: center;
  gap: 10px;
  margin-block-end: 12px;
  padding-block: 10px;
  padding-inline: 10px;
}

/* .operator-panel .avatar { inline-size: 70px; block-size: 70px; border-radius: 50%; background: radial-gradient(circle at 35% 35%, #8ad0ff, #2c74b3); border: 6px solid #2a3142; }
.operator-panel .op-input { background: #202737; border-radius: 8px; inline-size: 100%; block-size: 16px; } */

/* Aksiyonlar */
.mid-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
}

.action-left,
.action-right {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

/* Grid */
.grid-panel {
  padding: 0;
}

.orders-grid {
  inline-size: 100%;
}

/* Alt sekmeler */
.bottom-tabs {
  display: flex;
  gap: 14px;
  align-items: center;
}

.tab {
  background: transparent;
  color: #73a1ff;
  border: none;
  padding-block: 10px;
  padding-inline: 6px;
  font-weight: 700;
  cursor: pointer;
}

.tab.active {
  color: #ffb300;
}

@media (max-width: 1400px) {
  .top-grid {
    grid-template-columns: 360px 1fr 460px 240px;
  }
}

@media (max-width: 1200px) {
  .top-grid {
    grid-template-columns: 1fr;
  }
}

.label {
  /* text-decoration-line: underline; */

  /* text-decoration-style: solid; */

  /* text-decoration-color: rgb(206, 190, 190); */

  /* text-decoration-thickness: 2px; */

  /* margin-block-end: 5px; */
  text-align-last: center;
}

/* AppSelect zorlama disable görünümü */
.app-select--force-disabled {
  opacity: 0.55;
  pointer-events: none !important;
  filter: grayscale(0.4);
}
</style>

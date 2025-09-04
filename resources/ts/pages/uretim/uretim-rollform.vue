<template>
  <div class="page-rollform pa-0">
    <div class="rollform-header">
      <!-- Üst satır: Gauge | Durum Bilgileri | Üretim Rakamları | Operatör -->
      <VRow class="match-height">

        <VCol cols="12" md="3" sm="6">
          <VCard class="pa-3">
            <!-- Sol: OEE Gauge ve saat/tarih/KPI -->
            <section>
              <VCardTitle class="durum-title" :style="{ backgroundColor: statusColor }">{{ worksInfo?.statu_id === 0 ?
                'KAPALI' : worksInfo?.statu_id === 1 ?
                  'DURUYOR' : 'ÇALIŞIYOR' }}</VCardTitle>
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

                <div class="gauge-center-value " style="color: goldenrod;">
                  {{ worksInfo?.speed }}
                  <!-- <span class="unit">%</span> -->
                </div>
              </div>

              <div class="clock-wrap">
                <div class="digital-clock">{{ time }}</div>
                <div class="digital-date">{{ dateText }}</div>
              </div>

              <div class="kpi-row">
                <div class="kpi">
                  <div class="kpi-title">Kılınlık</div>
                  <div class="kpi-value">%{{ kpi.kilinlik }}</div>
                </div>
                <div class="kpi">
                  <div class="kpi-title">Ürünlük</div>
                  <div class="kpi-value">%{{ kpi.urunluk }}</div>
                </div>
                <div class="kpi">
                  <div class="kpi-title">Kalite</div>
                  <div class="kpi-value">%{{ kpi.kalite }}</div>
                </div>
              </div>
            </section>
          </VCard>
        </VCol>

        <VCol cols="12" md="3" sm="6">
          <VCard class="pa-3">
            <VCardTitle class="panel-title pa-0">DURUM BİLGİLERİ</VCardTitle>
            <hr>
            <!-- Orta: Durum Bilgileri -->
            <section class="panel status-panel">
              <div class="form-row mt-2">
                <label>Duruş Sebebi (F10 – Yeni Duruş)</label>
                <AppSelect v-model="selectedSebep" :items="durusSebepleri" item-title="description"
                  item-value="break_reason_code" return-object single-line placeholder="Seçiniz..."
                  variant="outlined" />
                <AppTextarea v-model="durusAciklamasi" placeholder="Açıklama..." disabled />
              </div>

              <div class="status-actions justify-between">
                <VBtn variant="tonal" color="error" size="small">Açıklama Gir</VBtn>
                <VSwitch color="error" v-model="arizali" label="Arızalı" value="false" :height="10" />
              </div>

              <div class="status-actions justify-between">
                <VBtn variant="tonal" color="warning" size="small" width="48%">
                  Çay Molası
                  <VIcon end icon="tabler-mug" />
                </VBtn>
                <VBtn variant="tonal" color="warning" size="small" width="48%">
                  Yemek Molası
                  <VIcon end icon="tabler-bowl-spoon" />
                </VBtn>
              </div>

              <div class="status-metrics mt-3 text-center">
                <div>
                  <div class="label">Durum Süresi</div>
                  <div class="metric-time">{{ durumSuresi }}</div>
                </div>
                <div>
                  <div class="label">Vardiya</div>
                  <div class="metric-time">{{ vardiyaSuresi }}</div>
                </div>
              </div>

              <div class="uclu text-center mt-3">
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

        <VCol cols="12" md="6" sm="6">
          <VCard class="pa-3">
            <VCardTitle class="panel-title pa-0">ÜRETİM RAKAMLARI</VCardTitle>
            <hr>
            <!-- Sağ: Üretim Rakamları -->
            <section class="panel status-panel">

              <VRow>
                <VCol cols="9">
                  <div class="uclu text-center mt-3">
                    <div class="ms-4 me-4" width="60%">
                      <div class="label">İş&nbsp;Emri&nbsp;Miktarı</div>
                      <div class="metric-time digit">{{ worksInfo?.order_qty }}</div>
                    </div>
                    <div>
                      <div class="label">Üretilen Net</div>
                      <div class="metric-time digit">{{ worksInfo?.net_qty }}</div>
                    </div>
                    <div>
                      <div class="label">Kalan</div>
                      <div class="metric-time digit">{{ worksInfo?.order_qty - worksInfo?.net_qty }}</div>
                    </div>
                  </div>
                </VCol>

                <VCol cols="3">
                  <div class="ikili text-center mt-3">
                    <div>
                      <div class="label">Hurda</div>
                      <div class="metric-time warn digit">{{ worksInfo?.scrap_qty }}</div>
                    </div>
                    <div>
                      <div class="label">O.A.%</div>
                      <div class="metric-time digit">{{ worksInfo?.net_qty === 0 ? 0 : (worksInfo?.net_qty /
                        worksInfo?.order_qty * 100).toFixed(0) }}
                      </div>
                    </div>
                  </div>
                </VCol>
              </VRow>

              <VProgressLinear :model-value="(worksInfo?.net_qty / worksInfo?.order_qty * 100) ?? 0" color="warning"
                height="10" :rounded="true" class="my-4" />

              <VRow>
                <VCol cols="3">
                  <div class="mt-0 label">İş Emri No:</div>
                  <div class="mt-2 label">Ürün Kodu:</div>
                  <div class="mt-2 label">Ürün Adı:</div>
                  <div class="mt-2 label">Ürün Boyu:</div>
                </VCol>
                <VCol cols="9" class="input-like">
                  <div>{{ worksInfo?.worder_no }}</div>
                  <div>{{ worksInfo?.item_code }}</div>
                  <div>{{ worksInfo?.item_name }}</div>
                  <div>{{ worksInfo?.item_length }}</div>
                </VCol>
              </VRow>

            </section>
          </VCard>
        </VCol>

      </VRow>
    </div>





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
      <DxDataGrid class="orders-grid" :data-source="rows" :hover-state-enabled="true" :row-alternation-enabled="false"
        :show-borders="false" :column-auto-width="true" :column-resizing-mode="'nextColumn'" :column-min-width="80"
        height="360">
        <DxColumn data-field="grp" caption="GRP" width="60" />
        <DxColumn data-field="isEmriNo" caption="İŞ EMRİ NO" width="140" />
        <DxColumn data-field="stokKodu" caption="STOK KODU" width="120" />
        <DxColumn data-field="stokAdi" caption="STOK ADI" />
        <DxColumn data-field="plnAd" caption="PLN.AD" width="90" data-type="number" format=",#" />
        <DxColumn data-field="ilvAd" caption="İLV.AD" width="90" data-type="number" format=",#" />
        <DxColumn data-field="klnAd" caption="KLN.AD" width="90" data-type="number" format=",#" />
        <DxColumn data-field="urtAd" caption="ÜRT.AD" width="90" data-type="number" format=",#" />
        <DxColumn data-field="hurda" caption="HURDA" width="90" data-type="number" format=",#" />
        <DxColumn data-field="plnSure" caption="PLN.SÜRE" width="100" data-type="number" />
        <DxColumn data-field="grcSure" caption="GRÇ.SÜRE" width="100" data-type="number" />
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
import { computed, nextTick, onActivated, onMounted, onUnmounted, ref, watch } from 'vue';

const pageTitleStore = usePageTitleStore();

const selectedSebep = ref<{
  break_reason_code: string;
  description: string;
} | null>(null);
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
  // API statu_time: ISO string | epoch(ms/sec) | elapsed seconds
  statu_time?: number | string | Date | null;
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

pageTitleStore.setTitle(`${pageName.value} (${pageAlias.value})`)
document.title = `OFT - ${pageName.value} | ${pageAlias.value}`

// Başlık her zaman görünsün: aktif olduğunda ve başlık değiştiğinde geri set et
const desiredTitle = computed(() => `${pageName.value} (${pageAlias.value})`)
const applyPageTitle = () => {
  pageTitleStore.setTitle(desiredTitle.value)
  document.title = `OFT - ${pageName.value} | ${pageAlias.value}`
}

onMounted(async () => {
  await nextTick()
  applyPageTitle()
  // statu_time bazlı Durum Süresi'ni ilk anda hesapla
  computeDurumSuresi()

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
})

// worksInfo değişince başlığı ve Durum Süresi'ni güncelle
watch(() => worksInfo.value, () => { applyPageTitle(); computeDurumSuresi() })

const userData = useCookie<any>("userData");

// Gauge ve diğer bileşenlere verilen obje tipindeki sabit props'lar.
// Bunları template içinde inline tanımlamak her render'da yeni referans oluşturur ve
// DevExtreme bileşeninde yeniden-init'e yol açarak ibrenin 0'a düşüp tekrar hedefe gitmesine sebep olabilir.

const gaugeTooltip = { enabled: false }
const gaugeSize = { width: 300, height: 300 }
const gaugeLabelFont = { color: '#9aa4b2', size: 14 }
const levelGaugeSize = { width: 70, height: 160 }
const hizSubvalues: number[] = [85]
const levelGaugeTooltip = { enabled: false }
const levelSubvalues: number[] = []

// Üst gauge (HIZ)
const statu = ref(0)
const hiz = ref(0)
const isEmriMiktari = ref(0)
const uretilen = ref(0)
const kalan = ref(0)
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

// Durum bilgileri
const durusSebebi = ref('uretim-disi')
const kpi = ref({ kilinlik: 0, urunluk: 0, kalite: 0 })

// Grid satırları (örnek)
type Row = {
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
const rows = ref<Row[]>([
  { grp: 0, isEmriNo: 'IEN-24056890', stokKodu: '040334P00', stokAdi: 'S70S 42U 19 TAŞIYICI DİKME', plnAd: 5000, ilvAd: 10, klnAd: 4855, urtAd: 155, hurda: 2, plnSure: 360, grcSure: 1260 },
  { grp: 0, isEmriNo: 'IEN-25016653', stokKodu: '040322P01', stokAdi: 'S70S 26U BOY PROFİLİ', plnAd: 1000, ilvAd: 5000, klnAd: 3311, urtAd: 2689, hurda: 0, plnSure: 360, grcSure: 1260 },
  { grp: 0, isEmriNo: 'IEN-25023926', stokKodu: '040334P00', stokAdi: 'S70S 42U 19 TAŞIYICI DİKME', plnAd: 2000, ilvAd: 20000, klnAd: 1400, urtAd: 2000, hurda: 15, plnSure: 1400, grcSure: 5081 },
])

let timer: ReturnType<typeof setInterval> | null = null
let info: ReturnType<typeof setInterval> | null = null


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
  margin-block-start: -20px;
}

.digital-clock {
  font-family: Verdana, monospace;
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
  margin-block-start: 10px;
}

.kpi {
  text-align: center;
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
  font-size: 24px;
  font-weight: 800;
  margin: -9px;
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
  /* text-decoration-line: underline;   */

  /* text-decoration-style: solid;       solid | double | dotted | dashed | wavy */

  /* text-decoration-color: rgb(206, 190, 190);        çizgi rengi */

  /* text-decoration-thickness: 2px;  */
  margin-block-end: 5px;
}
</style>

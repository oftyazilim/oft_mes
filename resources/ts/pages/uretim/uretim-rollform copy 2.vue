<template>
  <div class="page-rollform pa-0">
    <!-- Üst satır: Gauge | Durum Bilgileri | Üretim Rakamları | Operatör -->
    <div class="top-grid">
      <!-- Sol: OEE Gauge ve saat/tarih/KPI -->
      <section class="panel gauge-panel">
        <header class="panel-title">
          <span class="badge-left">1511</span>
        </header>

        <div class="gauge-wrap">
          <DxCircularGauge
            class="oee-gauge"
            :value="oee"
            :tooltip="{ enabled: false }"
            :size="{ width: 300, height: 300 }"
          >
            <DxGeometry :start-angle="225" :end-angle="-45" />
            <DxScale :start-value="0" :end-value="100" :tick-interval="10" :minor-tick-interval="5">
              <DxTick :length="8" color="#666" />
              <DxMinorTick :length="4" color="#444" />
              <DxLabel :use-range-colors="true" :font="{ color: '#9aa4b2', size: 12 }" />
            </DxScale>
            <DxRangeContainer background-color="#20252b">
              <DxRange :start-value="0" :end-value="40" color="#9b1c31" />
              <DxRange :start-value="40" :end-value="70" color="#caa93b" />
              <DxRange :start-value="70" :end-value="100" color="#1e8e3e" />
            </DxRangeContainer>
            <DxValueIndicator type="triangleNeedle" color="#cfd8dc" :spindle-size="10" :spindle-gap-size="6" :offset="0" />
            <DxSubvalueIndicator :size="0" />
            <DxTitle text="OEE %" />
            <DxExport :enabled="false" />
          </DxCircularGauge>

          <div class="gauge-center-value">
            {{ Math.round(oee) }}
            <span class="unit">%</span>
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

      <!-- Orta: Durum Bilgileri -->
      <section class="panel status-panel">
        <header class="panel-title">Durum Bilgileri</header>

        <div class="form-row">
          <label>Duruş Sebebi (F1–Yeni Drs)</label>
          <select class="select-dark" v-model="durusSebebi">
            <option value="uretim-disi">ÜRETİM DIŞI</option>
            <option value="bakim">BAKIM</option>
            <option value="ariza">ARIZA</option>
          </select>
        </div>

        <div class="status-actions">
          <button class="btn btn-sm">Açıklama</button>
          <button class="btn btn-sm">Çalışır</button>
          <button class="btn btn-sm">Arızalı</button>
          <button class="btn btn-sm">Çay</button>
          <button class="btn btn-sm btn-warn">Duruş Başlat</button>
          <button class="btn btn-sm">Yemek</button>
        </div>

        <div class="status-metrics">
          <div class="metric-box">
            <div class="metric-title">Durum Süresi</div>
            <div class="metric-time warn">04:58:54</div>
          </div>
          <div class="metric-box">
            <div class="metric-title">Vardiya</div>
            <div class="metric-time">04:59</div>
          </div>
        </div>

        <div class="status-timers">
          <div class="timer">
            <div class="timer-title">Çalışma</div>
            <div class="timer-value ok">00:00</div>
          </div>
          <div class="timer">
            <div class="timer-title">Duruş</div>
            <div class="timer-value warn">00:00</div>
          </div>
          <div class="timer">
            <div class="timer-title">Boşta</div>
            <div class="timer-value">02:00</div>
          </div>
        </div>
      </section>

      <!-- Sağ: Üretim Rakamları -->
      <section class="panel production-panel">
        <header class="panel-title">Üretim Rakamları</header>

        <div class="prod-row">
          <div class="prod-box">
            <div class="prod-title">İş Emri Miktarı</div>
            <div class="prod-value accent">5.000 + 10</div>
          </div>
          <div class="vr-counters">
            <div class="vr-box">Vr Büküm <span>0</span></div>
            <div class="vr-box">Vr Hrd <span>0</span></div>
            <div class="vr-box danger">IE Hrd <span>2</span></div>
          </div>
        </div>

        <div class="prod-row">
          <div class="prod-box big">
            <div class="prod-title">Üretilen Net Miktar</div>
            <div class="prod-value big">{{ uretim.netMiktar }}</div>
          </div>
          <div class="level-box">
            <div class="level-title">0/1</div>
            <DxLinearGauge
              class="level-gauge"
              :value="uretim.netMiktar"
              :size="{ width: 70, height: 160 }"
              :tooltip="{ enabled: false }"
              :subvalues="[]"
            >
              <DxLRScale :start-value="0" :end-value="200" :tick-interval="50" :minor-tick-interval="25" />
              <DxLRRangeContainer background-color="#1e232a">
                <DxLRRange :start-value="0" :end-value="160" color="#1e8e3e" />
                <DxLRRange :start-value="160" :end-value="200" color="#9b1c31" />
              </DxLRRangeContainer>
              <DxLRValueIndicator color="#cfd8dc" />
            </DxLinearGauge>
          </div>
        </div>

        <div class="prod-row">
          <div class="prod-box">
            <div class="prod-title">Kalan Miktar</div>
            <div class="prod-value remain">{{ uretim.kalanMiktar.toLocaleString('tr-TR') }}</div>
          </div>
        </div>

        <div class="prod-foot">
          <div class="input-like">{{ urun.urunKodu }}</div>
          <div class="input-like">{{ urun.urunAdi }}</div>
          <div class="input-like strong">{{ urun.isEmriNo }}</div>
        </div>
      </section>

      <!-- Çok sağ: Operatör ve Yrd. -->
      <!-- <section class="panel operator-panel">
        <header class="panel-title">Operatör ve Yrd.</header>
        <div class="avatar-card">
          <div class="avatar" />
          <div class="op-input" />
        </div>
        <div class="avatar-card">
          <div class="avatar" />
          <div class="op-input" />
        </div>
      </section> -->
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
      <DxDataGrid
        class="orders-grid"
        :data-source="rows"
        :hover-state-enabled="true"
        :row-alternation-enabled="false"
        :show-borders="false"
        :column-auto-width="true"
        :column-resizing-mode="'nextColumn'"
        :column-min-width="80"
        height="360"
      >
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
import DxCircularGauge, {
  DxExport,
  DxGeometry,
  DxLabel,
  DxMinorTick,
  DxRange,
  DxRangeContainer,
  DxScale,
  DxSubvalueIndicator,
  DxTick,
  DxTitle,
  DxValueIndicator,
} from 'devextreme-vue/circular-gauge'
import DxDataGrid, { DxColumn } from 'devextreme-vue/data-grid'
import DxLinearGauge, {
  DxRange as DxLRRange,
  DxRangeContainer as DxLRRangeContainer,
  DxScale as DxLRScale,
  DxValueIndicator as DxLRValueIndicator,
} from 'devextreme-vue/linear-gauge'
import { onMounted, onUnmounted, ref } from 'vue'

// Üst gauge (OEE)
const oee = ref(62)
const time = ref('16:38:42')
const dateText = ref('23 Haziran 2023 Cuma')

// Durum bilgileri
const durusSebebi = ref('uretim-disi')
const kpi = ref({ kilinlik: 0, urunluk: 0, kalite: 0 })

// Üretim rakamları
const uretim = ref({ netMiktar: 155, kalanMiktar: 4855 })

// Ürün satırı
const urun = ref({ urunKodu: 'O40334P00', urunAdi: 'S70S 42U 19 TAŞIYICI DİKME', isEmriNo: 'IEN-24056890' })

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

onMounted(() => {
	// Demo: zamanı canlı akıt
	timer = setInterval(() => {
		const now = new Date()
		const hh = String(now.getHours()).padStart(2, '0')
		const mm = String(now.getMinutes()).padStart(2, '0')
		const ss = String(now.getSeconds()).padStart(2, '0')
		time.value = `${hh}:${mm}:${ss}`

		const days = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi']
		const months = ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık']
		dateText.value = `${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()} ${days[now.getDay()]}`
	}, 1000)
})

onUnmounted(() => {
	if (timer) clearInterval(timer)
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

.panel {
  border-radius: 10px;
  background: #171b29;
  box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 4%);
  color: #cfd8dc;
  padding-block: 10px;
  padding-inline: 10px;
}

.panel-title {
  display: flex;
  align-items: center;
  justify-content: space-between;
  color: #c8cbd6;
  font-weight: 700;
  margin-block-end: 8px;
}

.badge-left {
  transform: rotate(-40deg);
  color: #ffd54f;
  font-weight: 800;
  font-size: 30px;
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

.oee-gauge :deep(.dxc-val-indicator path) {
  filter: drop-shadow(0 0 3px rgba(207, 216, 220, 80%));
}

.gauge-center-value {
  position: absolute;
  font-size: 40px;
  font-weight: 700;
  text-shadow: 0 0 8px rgba(0, 0, 0, 60%);
  inset-block-start: 120px;
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
  margin-block-start: 6px;
}

.digital-clock {
  font-family: DS-Digital, monospace;
  background: #0a0f14;
  font-size: 22px;
  padding-block: 4px;
  padding-inline: 10px;
  box-shadow: inset 0 0 8px rgba(0, 0, 0, 60%), 0 0 6px rgba(112, 225, 255, 30%);
  color: #70e1ff;
  border-radius: 6px;
}

.digital-date { color: #98a6b3; font-size: 13px; }

.kpi-row {
  display: grid;
  gap: 8px;
  grid-template-columns: repeat(3, 1fr);
  margin-block-start: 10px;
}

.kpi {
  text-align: center;
}
.kpi-title { color: #9aa4b2; font-size: 12px; }
.kpi-value { color: #c8cbd6; font-weight: 700; }

/* Durum Paneli */
.status-panel .form-row { display: flex; flex-direction: column; gap: 6px; }

.select-dark {
  background: #101521;
  color: #e6eaf2;
  border: 1px solid #2a3142;
  border-radius: 8px;
  padding: 8px;
}
.status-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-block: 8px; }
.btn { background: #2a3142; border: none; cursor: pointer; color: #e6eaf2; border-radius: 6px; padding-block: 6px; padding-inline: 10px; }
.btn-sm { font-size: 12px; padding-block: 4px; padding-inline: 8px; }
.btn-primary { background: #3a6ff7; }
.btn-warn { background: #f0623a; }
.btn.icon { font-size: 14px; }

.status-metrics { display: grid; gap: 8px; grid-template-columns: 1fr 1fr; }
.metric-box { background: #101521; border: 1px solid #2a3142; border-radius: 8px; padding: 8px; }
.metric-title { color: #a5adbd; font-size: 12px; margin-block-end: 6px; }
.metric-time { font-size: 28px; font-weight: 800; }
.metric-time.warn { color: #ff5252; }
.metric-time.ok { color: #38d39f; }

.status-timers { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-block-start: 8px; }
.timer { background: #101521; border: 1px solid #2a3142; border-radius: 8px; padding: 8px; text-align: center; }
.timer-title { color: #a5adbd; font-size: 12px; margin-block-end: 4px; }
.timer-value { font-weight: 800; color: #ffc107; }
.timer-value.ok { color: #38d39f; }
.timer-value.warn { color: #ff7043; }

/* Üretim Paneli */
.production-panel .prod-row { display: grid; align-items: center; gap: 10px; grid-template-columns: 1fr auto; margin-block-end: 8px; }
.prod-box { background: #101521; border: 1px solid #2a3142; border-radius: 8px; padding: 8px; }
.prod-box.big { padding: 12px; }
.prod-title { color: #a5adbd; font-size: 12px; margin-block-end: 6px; }
.prod-value { font-weight: 800; font-size: 26px; }
.prod-value.accent { color: #ffd54f; }
.prod-value.big { font-size: 64px; color: #52ff6a; }
.prod-value.remain { font-size: 54px; color: #ffd54f; }
.vr-counters { display: flex; gap: 8px; }
.vr-box { background: #2a3142; border-radius: 8px; color: #e6eaf2; padding-block: 8px; padding-inline: 8px; }
.vr-box span { font-weight: 800; margin-inline-start: 6px; }
.vr-box.danger { background: #5c2a2a; }

.level-box { display: flex; flex-direction: column; align-items: center; gap: 6px; }
.level-title { font-size: 14px; color: #a5adbd; }

.prod-foot { display: grid; grid-template-columns: 1fr 2fr 1fr; gap: 8px; margin-block-start: 8px; }
.input-like { background: #d2b200; color: #161616; border-radius: 8px; padding-block: 8px; padding-inline: 10px; font-weight: 800; }
.input-like.strong { background: #fc0; }

/* Operatör Paneli */
.operator-panel .avatar-card {
  background: #101521;
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
.operator-panel .avatar { inline-size: 70px; block-size: 70px; border-radius: 50%; background: radial-gradient(circle at 35% 35%, #8ad0ff, #2c74b3); border: 6px solid #2a3142; }
.operator-panel .op-input { background: #202737; border-radius: 8px; inline-size: 100%; block-size: 16px; }

/* Aksiyonlar */
.mid-actions { display: flex; justify-content: space-between; align-items: center; gap: 10px; }

.action-left,
 .action-right { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

/* Grid */
.grid-panel { padding: 0; }
.orders-grid { inline-size: 100%; }

/* Alt sekmeler */
.bottom-tabs { display: flex; gap: 14px; align-items: center; }
.tab { background: transparent; color: #73a1ff; border: none; padding-block: 10px; padding-inline: 6px; font-weight: 700; cursor: pointer; }
.tab.active { color: #ffb300; }

@media (max-width: 1400px) {
  .top-grid { grid-template-columns: 360px 1fr 460px 240px; }
}

@media (max-width: 1200px) {
  .top-grid { grid-template-columns: 1fr; }
}
</style>

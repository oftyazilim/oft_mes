<template>
  <!-- İlk yükleme overlay -->
  <div v-if="initialLoading" class="loading-overlay">
    <div class="loader-box">
      <div class="spinner" aria-hidden="true"></div>
      <h2>Veriler yükleniyor</h2>
      <p>Makine durumları, OEE ve haftalık istatistikler getiriliyor. Lütfen bekleyin…</p>
    </div>
  </div>
  <VRow class="match-height">

    <!-- 👉 Günlük OEE değerlendirmesi -->
    <VCol cols="12" md="4">
      <VCard height="260">
        <VCardText>
          <h5 class="text-h5">Günlük Genel Değerlendirme</h5>
        </VCardText>
        <div class="summary-kpis px-4">
          <div class="kpi"><span>Çlş.</span><strong>{{ fmtPct(daySummary.availability) }}</strong>
            <VProgressLinear :model-value="daySummary.availability * 100" color="primary" height="10"></VProgressLinear>
          </div>
          <div class="kpi"><span>Perf.</span><strong>{{ fmtPct(daySummary.performance) }}</strong>
            <VProgressLinear :model-value="daySummary.performance * 100" color="primary" height="10"></VProgressLinear>
          </div>
          <div class="kpi border-e pe-2"><span>Kalite</span><strong>{{ fmtPct(daySummary.quality) }}</strong>
            <VProgressLinear :model-value="daySummary.quality * 100" color="primary" height="10"></VProgressLinear>
          </div>
          <div class="kpi oee" :class="oeeClass(daySummary.oee)"><span>OEE</span><strong>{{ fmtPct(daySummary.oee)
              }}</strong>
            <VProgressLinear :model-value="daySummary.oee * 100" color="success" height="10"></VProgressLinear>
          </div>
        </div>
        <div class="dash-actions mt-3">
          <button class="refresh-btn" @click="manualRefresh" :disabled="refreshing || loading">
            <span v-if="refreshing">⟳ Yenileniyor...</span>
            <span v-else>⟳ Yenile</span>
          </button>
          <small v-if="lastUpdated" class="last-updated">Güncellendi: {{ formatTime(lastUpdated) }}</small>
          <small v-if="transientEmpty" class="warn"
            title="API geçici boş yanıt verdi; önceki veriler gösteriliyor">Geçici boş yanıt</small>
          <small v-else-if="emptyData && !loading" class="warn">Veri yok</small>
          <small v-if="backoffMs > basePoll && !loading" class="hint">Bekleme: {{ Math.round(backoffMs / 1000)
            }}sn</small>
        </div>
      </VCard>
    </VCol>

    <!-- 👉 Haftalık Çalışma (dxChart) -->
    <VCol cols="12" md="4" sm="6">
      <VCard height="260">
        <VCardText class="pa-5">
          <h5 class="text-h5">Haftalık Çalışma / Duruş (dk)</h5>
        </VCardText>
        <div class="weekly-vertical ma-0 ms-2">
            <div class="dx-weekly-wrap" ref="dxWrapEl">
              <DxChart :data-source="dxWeeklyData" :size="{ height: 180, width: dxWidth || undefined }">
                <DxMargin :right="16" :left="8" :top="8" :bottom="0" />
                <DxCommonSeriesSettings argument-field="label" type="stackedBar" />
                <DxSeries value-field="work" name="Çalışma" color="#189c49" />
                <DxSeries value-field="stop" name="Duruş" color="#efb803" />

                <DxArgumentAxis discrete-axis-division-mode="betweenLabels" />
                <DxValueAxis>
                  <!-- <DxTitle text="dk" /> -->
                  <DxGrid :visible="true" />
                </DxValueAxis>

                <DxTooltip :enabled="true" :shared="true" :customize-tooltip="dxWeeklyTooltip" />
                <DxLegend position="bottom" horizontal-alignment="center" :visible="false" />
              </DxChart>
            </div>
        </div>
      </VCard>
    </VCol>

    <!-- 👉 Haftalık Sayaç (adet) -->
    <VCol cols="12" md="4" sm="6">
      <VCard height="260">
        <VCardText class="pa-5">
          <h5 class="text-h5">Haftalık Sayaç (adet)</h5>
        </VCardText>
        <div class="weekly-vertical ma-0 ms-2">
            <div class="dx-weekly-wrap" ref="dxWrapEl">
              <DxChart :data-source="weeklyCounts" :size="{ height: 180, width: dxWidth || undefined }">
                <DxMargin :right="16" :left="8" :top="8" :bottom="0" />
                <DxCommonSeriesSettings argument-field="label" type="bar" />
                <DxSeries value-field="count" name="Sayaç" color="rgba(115, 103, 240, 0.8)" />

                <DxArgumentAxis discrete-axis-division-mode="betweenLabels" />
                <DxValueAxis>
                  <!-- <DxTitle text="dk" /> -->
                  <DxGrid :visible="true" />
                </DxValueAxis>

                <DxTooltip :enabled="true" :shared="true" :customize-tooltip="dxWeeklyTooltip" />
                <DxLegend position="bottom" horizontal-alignment="center" :visible="false" />
              </DxChart>
            </div>
        </div>
      </VCard>
    </VCol>


    <!-- 👉 Earning Reports Weekly Overview -->
    <VCol cols="12" md="6">
      <h1>Earning Reports Weekly Overview</h1>
    </VCol>

    <!-- 👉 Support Tracker -->
    <VCol cols="12" md="6">
      <h1>Support Tracker</h1>
    </VCol>

    <!-- 👉 Sales by Countries -->
    <VCol cols="12" sm="6" lg="4">
      <h1>Sales by Countries</h1>
    </VCol>

    <!-- 👉 Total Earning -->
    <VCol cols="12" sm="6" lg="4">
      <h1>Total Earning</h1>
    </VCol>

    <!-- 👉 Monthly Campaign State -->
    <VCol cols="12" sm="6" lg="4">
      <h1>Monthly Campaign State</h1>
    </VCol>

    <!-- 👉 Source Visits -->
    <VCol cols="12" sm="6" lg="4">
      <h1>Source Visits</h1>
    </VCol>

    <!-- 👉 Project Table -->
    <VCol cols="12" lg="8">
      <h1>pjjjpjpjpo</h1>
    </VCol>
  </VRow>
</template>



<script setup lang="ts">
import axios from 'axios';
import DxChart, { DxArgumentAxis, DxCommonSeriesSettings, DxGrid, DxLegend, DxMargin, DxSeries, DxTitle, DxTooltip, DxValueAxis } from 'devextreme-vue/chart';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
// --- Ticker (sağ boşluk) ---
const baseTickerMessages = ref<string[]>([
  'Üretimde süreklilik için proaktif bakım planı devrede',
  'Operatör güvenliği: Koruyucu ekipman kontrolü yapılmalı',
  'Enerji verimliliği izleniyor – gereksiz duruşları azaltın',
  'Kalite ilk seferde doğru üretimle başlar',
  'Anlık OEE analizi ile darboğazları tespit edin'
])
// Sonsuz akış illüzyonu için iki kez katla
const tickerMessagesDoubled = computed(() => [...baseTickerMessages.value, ...baseTickerMessages.value])
const tickerPos = ref(0)
const pauseTicker = ref(false)
let tickerRaf: number | null = null
// Ticker hız seçenekleri (px/sn)
const speedOptions = [60, 100, 140, 200]
const speedIndex = ref(2) // varsayılan 140
const tickerSpeed = computed(() => speedOptions[speedIndex.value] || 140)
const tickerTrackEl = ref<HTMLElement | null>(null)
const tickerInnerWidth = ref(0)
const firstCycleWidth = ref(0)
let lastTs = 0

const tickerStyle = computed(() => ({ transform: `translateX(${tickerPos.value}px)`, inlineSize: tickerInnerWidth.value + 'px' }))

function measureTicker() {
  const el = tickerTrackEl.value
  if (!el) return
  const children = el.querySelectorAll('.ticker-item')
  if (!children.length) return
  // İlk setin gerçek genişliğini, ilk öğenin left'i + sonraki ilk öğenin left farkı üzerinden değil;
  // yarı uzunluk indeksine kadar offsetWidth + gap toplayarak ölç.
  const half = Math.floor(children.length / 2)
  let sum = 0
  for (let i = 0; i < half; i++) {
    const c = children[i] as HTMLElement
    sum += c.offsetWidth
    // Son eleman değilse gap ekle (gap = 42px)
    if (i < half - 1) sum += 42
  }
  firstCycleWidth.value = sum
  tickerInnerWidth.value = sum * 2
  // Pozisyon yarım genişliği aştıysa düzgün devam etsin diye mod al
  if (tickerPos.value < -firstCycleWidth.value) {
    tickerPos.value = ((tickerPos.value % firstCycleWidth.value) + firstCycleWidth.value) % firstCycleWidth.value * -1
  }
}

function stepTicker(ts: number) {
  if (!lastTs) lastTs = ts
  const delta = (ts - lastTs) / 1000
  lastTs = ts
  if (!pauseTicker.value && tickerInnerWidth.value) {
    tickerPos.value -= tickerSpeed.value * delta
    if (firstCycleWidth.value && tickerPos.value <= -firstCycleWidth.value) {
      tickerPos.value += firstCycleWidth.value
    }
  }
  tickerRaf = requestAnimationFrame(stepTicker)
}

onMounted(() => {
  requestAnimationFrame(() => {
    measureTicker()
    tickerRaf = requestAnimationFrame(stepTicker)
  })
  window.addEventListener('resize', measureTicker)
  // DxChart responsive gözlemleyici
  dxRO = new ResizeObserver(() => measureDxWidth())
  if (dxWrapEl.value) dxRO.observe(dxWrapEl.value)
  // İlk ölçüm
  requestAnimationFrame(() => measureDxWidth())
})
onBeforeUnmount(() => { if (tickerRaf) cancelAnimationFrame(tickerRaf) })
onBeforeUnmount(() => { window.removeEventListener('resize', measureTicker) })
onBeforeUnmount(() => {
  if (dxRO) {
    try { if (dxWrapEl.value) dxRO.unobserve(dxWrapEl.value) } catch { }
    dxRO.disconnect()
    dxRO = null
  }
})

type MachineStatus = 'running' | 'stopped' | 'offline';

interface MachineCard {
  id: number;
  name: string;
  code?: string;
  operator?: string;
  breakDescription?: string;
  statuTime?: string | number | Date | null;
  itemCode?: string;
  itemName?: string;
  counter?: number;
  status: MachineStatus;
  availability: number; // 0..1
  performance: number;  // 0..1
  quality: number;      // 0..1
  oee: number;          // 0..1
  workRatio: number;    // 0..100
  stopRatio: number;    // 0..100
  segments?: Segment[]; // Zaman sıralı segmentler (varsa)
}

type SegmentType = 'calisma' | 'durus' | 'ayar' | 'mola' | 'kalan' | 'running' | 'stopped' | 'offline' | 'work' | 'stop';
interface Segment { type: SegmentType | string; duration: number; sebep?: string; reason?: string }

interface DaySummary { availability: number; performance: number; quality: number; oee: number }
interface Reason { name: string; minutes: number }
interface WeeklyDay { date: string; label: string; work: number; stop: number }
interface WeeklyCount { date: string; label: string; count: number }

const machines = ref<MachineCard[]>([]);
const daySummary = ref<DaySummary>({ availability: 0, performance: 0, quality: 1, oee: 0 });
const downtimeReasons = ref<Reason[]>([]);
const weekly = ref<WeeklyDay[]>([]);
const weeklyCounts = ref<WeeklyCount[]>([]);
const loading = ref<boolean>(true);
const isSlow = ref<boolean>(false);
// Stale-while-revalidate ve hata/backoff durum değişkenleri
const refreshing = ref<boolean>(false);
const lastUpdated = ref<Date | null>(null);
const emptyData = ref<boolean>(false);          // İlk veri hiç yoksa
const transientEmpty = ref<boolean>(false);     // Önceden veri vardı, bu poll boş döndü
const errorCount = ref<number>(0);
const basePoll = 10000; // ms
const backoffMs = ref<number>(basePoll);
const meta = ref<any>(null);
// İlk yükleme (hiç başarı yokken) durumunu ayırt et
const initialLoading = computed(() => loading.value && !lastUpdated.value);

const fmtPct = (v: number) => isFinite(v) ? `%${Math.round(v * 100)}` : '-';
const oeeClass = (v: number) => {
  const pct = v * 100;
  if (pct < 50) return 'oee-red';
  if (pct < 70) return 'oee-yellow';
  if (pct < 85) return 'oee-orange';
  return 'oee-green';
};
const statusText = (s: MachineStatus) => s === 'running' ? 'Çalışıyor' : s === 'stopped' ? 'Duruyor' : 'Kapalı';
const reasonWidth = (mins: number) => `${Math.min(100, mins)}%`;
const weeklyWidth = (mins: number) => `${Math.min(100, Math.round(mins))}%`;
const maxWeekly = () => Math.max(1, ...weekly.value.map(w => (w.work + w.stop)));
const weeklyHeight = (mins: number) => `${Math.round((mins / maxWeekly()) * 100)}%`;
const maxWeeklyCount = () => Math.max(1, ...weeklyCounts.value.map(w => w.count));
const weeklyCountHeight = (cnt: number) => `${Math.round((cnt / maxWeeklyCount()) * 100)}%`;

// Yerel tarihi YYYY-MM-DD formatında üret (UTC kaymasını engelle)
function toLocalYMD(date: Date): string {
  const y = date.getFullYear();
  const m = String(date.getMonth() + 1).padStart(2, '0');
  const d = String(date.getDate()).padStart(2, '0');
  return `${y}-${m}-${d}`;
}

// Haftanın 7 günü için baseline oluştur (bugün dahil geriye doğru 7 gün)
const weeklyBaseline = computed<WeeklyDay[]>(() => {
  const days: WeeklyDay[] = [];
  const today = new Date();
  const day = today.getDay();
  const offsetToMonday = (day + 6) % 7; // Pazartesi=0
  const start = new Date(today);
  start.setHours(0, 0, 0, 0);
  start.setDate(today.getDate() - offsetToMonday);
  const TR_DAYS = ['Paz', 'Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt'];
  for (let i = 0; i < 7; i++) {
    const d = new Date(start);
    d.setDate(start.getDate() + i);
    // ISO (UTC) yerine yerel tarih kullan
    const ymd = toLocalYMD(d);
    const label = TR_DAYS[d.getDay()] || '';
    days.push({ date: ymd, label, work: 0, stop: 0 });
  }
  return days;
});

// API weekly verisini baseline ile birleştir
const weeklyMerged = computed<WeeklyDay[]>(() => {
  const map = new Map<string, WeeklyDay>();
  for (const b of weeklyBaseline.value) map.set(b.date, { ...b });
  for (const w of weekly.value) {
    const key = String(w.date);
    if (!map.has(key)) continue; // sadece bu haftanın günleri
    const prev = map.get(key)!;
    map.set(key, {
      date: key,
      label: prev.label,
      work: Number((w as any).work || 0),
      stop: Number((w as any).stop || 0),
    });
  }
  return weeklyBaseline.value.map(b => map.get(b.date) || b);
});

// ApexCharts serileri ve seçenekleri
const apexWeeklySeries = computed(() => [
  { name: 'Çalışma', data: weeklyMerged.value.map(d => d.work) },
  { name: 'Duruş', data: weeklyMerged.value.map(d => d.stop) },
]);

const apexWeeklyOptions = computed(() => ({
  chart: { stacked: true, toolbar: { show: false }, width: '100%' },
  plotOptions: { bar: { horizontal: false, columnWidth: '70%', borderRadius: 4 } },
  dataLabels: { enabled: false },
  grid: { strokeDashArray: 3, borderColor: 'rgba(255,255,255,0.08)' },
  xaxis: { categories: weeklyBaseline.value.map(d => d.label) },
  yaxis: { labels: { formatter: (v: number) => `${Math.round(v)}` }, title: { text: 'dk' } },
  tooltip: { shared: true, intersect: false },
  colors: ['#189c49', '#efb803'],
}))

// DxChart veri ve tooltip
const dxWeeklyData = computed(() => weeklyMerged.value.map(d => ({ label: d.label, work: d.work, stop: d.stop })));
function dxWeeklyTooltip(arg: any) {
  const pts = Array.isArray(arg.points) ? arg.points : [arg];
  const lines = pts.map((p: any) => `${p.seriesName}: ${Math.round(p.value)}`);
  return { text: lines.join('\n') };
}

// DxChart responsive: kapsayıcı genişliği ölç, grafik genişliğine bağla
const dxWrapEl = ref<HTMLElement | null>(null)
const dxWidth = ref<number | undefined>(undefined)
let dxRO: ResizeObserver | null = null

function measureDxWidth() {
  const el = dxWrapEl.value
  if (!el) return
  // padding ve border dahil değil; clientWidth yeterli
  const w = Math.max(0, Math.floor(el.clientWidth))
  dxWidth.value = w || undefined
}

// Mini barları tek satır çok renkli bara dönüştürmek için oranları normalize eden yardımcı
function segs(m: MachineCard) {
  const w = Math.max(0, Math.min(100, Number(m.workRatio) || 0));
  const s = Math.max(0, Math.min(100, Number(m.stopRatio) || 0));
  const total = w + s;
  // Toplam 100'ü aşarsa orantılı daralt
  if (total > 100) {
    const f = 100 / total;
    const nw = Math.round(w * f * 100) / 100;
    const ns = Math.round(s * f * 100) / 100;
    return { work: nw, stop: ns, remain: 0 };
  }
  const remain = Math.max(0, 100 - total);
  return {
    work: Math.round(w * 100) / 100,
    stop: Math.round(s * 100) / 100,
    remain: Math.round(remain * 100) / 100,
  };
}

// Tip-renk haritası (ProductionCard ile uyumlu)
const typeColorMap: Record<string, string> = {
  calisma: '#189c49',    // yeşil
  running: '#189c49',
  work: '#189c49',
  durus: '#efb803',      // sarı
  stopped: '#efb803',
  stop: '#efb803',
  ayar: '#a59ef7',
  mola: '#f4b73f',
  kalan: 'lightgray',
  offline: '#9e9e9e',
};

function normalizeSegType(t: string): SegmentType {
  const map: Record<string, SegmentType> = {
    running: 'calisma',
    work: 'calisma',
    stopped: 'durus',
    stop: 'durus',
    setup: 'ayar',
    break: 'mola',
    idle: 'kalan',
  };
  const k = (t || '').toLowerCase();
  return (map[k] as SegmentType) || (k as SegmentType);
}

function segmentTotal(segments: Segment[]): number {
  return segments.reduce((sum, s) => sum + (Number(s.duration) || 0), 0) || 0;
}

function truncate(val: string, max = 20) {
  return val.length > max ? val.slice(0, max) + '…' : val
}

function getSegmentStyle(seg: Segment, all: Segment[]) {
  const total = Math.max(1, segmentTotal(all));
  const widthPercent = Math.max(0, (Number(seg.duration) || 0) / total * 100);
  const color = typeColorMap[normalizeSegType(String(seg.type))] || '#888';
  return { inlineSize: `${widthPercent}%`, backgroundColor: color, borderRadius: '999px' } as const;
}

function getSegmentTooltip(seg: Segment & { code?: string }, m: MachineCard) {
  const t = normalizeSegType(String(seg.type));
  if (t === 'calisma') {
    // Çalışıyor: ürün adı göster
    return m.itemName || m.itemCode || 'Çalışıyor';
  }
  if (t === 'durus') {
    // Duruyor: duruş sebebi göster
    return seg.reason || seg.sebep || seg.code || 'Duruyor';
  }
  // Diğerleri: tip etiketi
  const labels: Record<string, string> = {
    calisma: 'Çalışma', durus: 'Duruş', ayar: 'Ayar', mola: 'Mola', kalan: 'Kalan', offline: 'Kapalı',
  };
  return labels[t] || String(seg.type);
}

// Kart üst şeridi için statü bazlı koyu arka plan
function statusBaseColor(s: MachineStatus): string {
  if (s === 'running') return 'var(--v-theme-success, #22c55e)'
  if (s === 'stopped') return 'var(--v-theme-warning, #fb923c)'
  return 'var(--v-theme-on-surface-variant, #9e9e9e)'
}

function cardTopStyle(m: MachineCard) {
  const base = statusBaseColor(m.status)
  // Fallback için hex tabanlı koyulaştırma (var() fallback renkleriyle uyumlu)
  const baseHex = (m.status === 'running') ? '#298e4e' : (m.status === 'stopped') ? '#cc3f3f' : '#605050'
  const darkHex = darkenHexWithBlack(baseHex, 0.26)
  return {
    // color-mix desteklenirse bunu kullanır; desteklenmiyorsa backgroundIgnored -> backgroundColor devreye girer
    background: `color-mix(in oklab, ${base} 26%, #000)`,
    backgroundColor: darkHex,
  } as const
}

function darkenHexWithBlack(hex: string, ratio: number) {
  // ratio: 0..1 arası siyaha karışım
  const { r, g, b } = hexToRgb(hex)
  const rr = Math.round(r * (1 - ratio))
  const gg = Math.round(g * (1 - ratio))
  const bb = Math.round(b * (1 - ratio))
  return rgbToHex(rr, gg, bb)
}

function hexToRgb(hex: string) {
  let h = hex.replace('#', '')
  if (h.length === 3) {
    h = h.split('').map(c => c + c).join('')
  }
  const num = parseInt(h, 16)
  return { r: (num >> 16) & 255, g: (num >> 8) & 255, b: num & 255 }
}

function rgbToHex(r: number, g: number, b: number) {
  const toHex = (n: number) => n.toString(16).padStart(2, '0')
  return `#${toHex(r)}${toHex(g)}${toHex(b)}`
}

let slowTimer: number | null = null;
const loadError = ref<string | null>(null);
async function loadData(signal?: AbortSignal) {
  try {
    const firstLoad = machines.value.length === 0;
    if (firstLoad) {
      loading.value = true;
    } else {
      refreshing.value = true;
    }
    if (slowTimer) { clearTimeout(slowTimer); slowTimer = null; }
    slowTimer = window.setTimeout(() => { isSlow.value = true; }, 2500);
    loadError.value = null;
    const { data } = await axios.get('/api/dash-bukum/overview', { signal, timeout: 10000 });
    // console.log('API veri:', data);
    const newMachines = (data?.machines || []).map((m: any) => ({
      id: Number(m.id),
      name: String(m.name ?? ''),
      operator: m.operator || undefined,
      breakDescription: m.break_description ?? m.breakDescription ?? m.break_desc ?? undefined,
      statuTime: m.statu_time ?? m.statuTime ?? undefined,
      itemCode: m.itemCode ?? m.item_code ?? undefined,
      itemName: m.itemName ?? m.item_name ?? undefined,
      counter: (m.counter !== undefined && m.counter !== null) ? Number(m.counter) : undefined,
      status: (m.status ?? 'stopped') as MachineStatus,
      availability: Number(m.availability) || 0,
      performance: Number(m.performance) || 0,
      quality: Number(m.quality) || 1,
      oee: Number(m.oee) || 0,
      workRatio: Number(m.workRatio) || 0,
      stopRatio: Number(m.stopRatio) || 0,
      code: m.code || '',
      segments: Array.isArray(m.segments) ? m.segments : (Array.isArray(m.barData) ? m.barData : (Array.isArray(m.timeline) ? m.timeline : undefined)),
    }));
    meta.value = data?.meta || null;
    if (newMachines.length === 0) {
      if (machines.value.length === 0) {
        // İlkten beri boş
        emptyData.value = true;
      } else {
        // Geçici boş – önceki veriyi koru
        transientEmpty.value = true;
      }
    } else {
      machines.value = newMachines;
      emptyData.value = false;
      transientEmpty.value = false;
    }
    daySummary.value = data?.daySummary || daySummary.value;
    downtimeReasons.value = data?.reasons || downtimeReasons.value;
    weekly.value = data?.weekly || weekly.value;
    weeklyCounts.value = (data?.weeklyCounts || data?.weekly_counts || []).map((d: any) => ({
      date: String(d.date),
      label: String(d.label ?? ''),
      count: Number(d.count ?? 0),
    }));
    lastUpdated.value = new Date();
    // Başarılı yükleme -> backoff resetle
    errorCount.value = 0;
    backoffMs.value = basePoll;
  } catch (e: any) {
    errorCount.value += 1;
    // Üstel backoff (maks 60s)
    backoffMs.value = Math.min(basePoll * Math.pow(2, errorCount.value), 60000);
    const status = e?.response?.status;
    if (status === 401) {
      loadError.value = 'Yetki hatası (401).';
    } else if (status === 404) {
      loadError.value = 'Endpoint bulunamadı (404).';
    } else if (status === 500) {
      loadError.value = 'Sunucu hatası (500).';
    } else if (e?.code === 'ECONNABORTED') {
      loadError.value = 'Zaman aşımı.';
    } else if (e?.name === 'CanceledError' || e?.message === 'canceled') {
      // abort gösterme
    } else {
      loadError.value = 'Beklenmeyen hata.';
    }
  } finally {
    loading.value = false;
    refreshing.value = false;
    if (slowTimer) { clearTimeout(slowTimer); slowTimer = null; }
    if (isSlow.value) setTimeout(() => { isSlow.value = false; }, 400);
  }
}

const POLL_MS = basePoll; // eski referans sabit
let timer: number | null = null;
let inFlight = false;
let controller: AbortController | null = null;

function schedule(nextMs = backoffMs.value) {
  if (timer) {
    clearTimeout(timer);
  }
  timer = window.setTimeout(tick, nextMs);
}

async function tick() {
  if (document.hidden) {
    // Sekme gizliyken daha uzun bekle (en az 30sn veya backoff)
    schedule(Math.max(30000, backoffMs.value));
    return;
  }
  if (inFlight) {
    // Hâlâ önceki istek sürüyorsa küçük bir gecikmeyle tekrar dene
    schedule(500);
    return;
  }
  inFlight = true;
  if (controller) controller.abort();
  controller = new AbortController();
  try {
    await loadData(controller.signal);
  } finally {
    inFlight = false;
    schedule(POLL_MS);
  }
}

function handleVisibility() {
  if (document.hidden) {
    if (controller) controller.abort();
    if (timer) {
      clearTimeout(timer);
      timer = null;
    }
  } else {
    // Görünür olduğunda hemen tazele
    schedule(0);
  }
}

function manualRefresh() {
  if (inFlight) return;
  if (timer) { clearTimeout(timer); timer = null; }
  schedule(0);
}

function formatTime(d: Date | null) {
  if (!d) return '-';
  return d.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
}

// --- Statü süresi (statu_time -> now farkı) ---
const nowTick = ref<number>(Date.now());
let durTimer: number | null = null;

function parseStatuTime(v: unknown): number | null {
  if (v === null || v === undefined) return null;
  if (typeof v === 'number') {
    // Saniye mi milisaniye mi?
    return v > 1e12 ? v : (v > 1e10 ? v : Math.round(v * 1000));
  }
  const d = v instanceof Date ? v : new Date(String(v));
  const ms = d.getTime();
  return Number.isFinite(ms) ? ms : null;
}

function fmtHHMMSS(totalSeconds: number): string {
  const h = Math.floor(totalSeconds / 3600);
  const m = Math.floor((totalSeconds % 3600) / 60);
  const s = Math.floor(totalSeconds % 60);
  const pad = (n: number) => String(n).padStart(2, '0');
  return `${pad(h)}:${pad(m)}:${pad(s)}`;
}

function durationSince(statuTime: MachineCard['statuTime']): string {
  const t = parseStatuTime(statuTime as any);
  if (!t) return '-';
  const deltaSec = Math.max(0, Math.floor((nowTick.value - t) / 1000));
  return fmtHHMMSS(deltaSec);
}

// Kısa süre formatı: 2h 03m, 12m 05s, 45s
function durationSinceShort(statuTime: MachineCard['statuTime']): string {
  const t = parseStatuTime(statuTime as any);
  if (!t) return '-';
  let sec = Math.max(0, Math.floor((nowTick.value - t) / 1000));
  const h = Math.floor(sec / 3600); sec %= 3600;
  const m = Math.floor(sec / 60); const s = sec % 60;
  const pad = (n: number) => String(n).padStart(2, '0');
  if (h > 0) return `${h}h ${pad(m)}m`;
  if (m > 0) return `${m}m ${pad(s)}s`;
  return `${s}s`;
}

// Sebep fallback: API break_description yoksa segmentlerden son duruş sebebini dene
function displayReason(m: MachineCard): string | undefined {
  if (m.breakDescription) return String(m.breakDescription);
  const segs = Array.isArray(m.segments) ? m.segments : [];
  for (let i = segs.length - 1; i >= 0; i--) {
    const t = normalizeSegType(String(segs[i]?.type));
    if (t === 'durus') {
      const r = (segs[i] as any).reason || (segs[i] as any).sebep || (segs[i] as any).code;
      if (r) return String(r);
    }
  }
  return undefined;
}

onMounted(async () => {
  document.addEventListener('visibilitychange', handleVisibility);
  await tick();
  // Her kart için süre göstergesini canlı güncelle
  durTimer = window.setInterval(() => { nowTick.value = Date.now(); }, 1000);
});
onBeforeUnmount(() => {
  document.removeEventListener('visibilitychange', handleVisibility);
  if (controller) controller.abort();
  if (timer) clearTimeout(timer);
  if (durTimer) clearInterval(durTimer);
});
</script>










<style scoped>
/* stylelint-disable order/properties-order, @stylistic/declaration-colon-space-after, declaration-block-single-line-max-declarations, at-rule-empty-line-before, rule-empty-line-before, no-descending-specificity, no-duplicate-selectors, @stylistic/max-line-length */
.dash-container {
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding-block: 0;
  padding-inline: 0;
}

.dash-header {
  display: grid;
  grid-template-columns: minmax(250px, 25%) 1fr;
  gap: 16px;
  align-items: stretch;
}

.header-left {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.summary-card.stretch {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.summary-card.stretch .summary-kpis {
  margin-block-start: auto;
}

.title-block h1 {
  margin: 0;
  font-size: 26px;
}

.header-right {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  align-items: end;
}

.dash-header h1 {
  margin: 0;
  font-size: 26px;
}

.muted {
  color: #7d7d7d;
  font-size: 13px;
}

.header-metrics {
  display: flex;
  gap: 16px;
}

.weekly-card {
  border: 1px solid var(--v-theme-outline-variant, #a19e9e);
  border-radius: 12px;
  background: rgb(var(--v-theme-surface));
  padding-block: 12px;
  padding-inline: 12px;
  box-shadow: 0 5px 5px rgba(0, 0, 0, 24%), 0 2px 4px rgba(0, 0, 0, 10%);
}

.weekly-vertical {
  display: flex;
  inline-size: 100%;
  align-items: stretch;
}

.wv-bars {
  display: grid;
  grid-auto-flow: column;
  grid-auto-columns: minmax(32px, 1fr);
  gap: 10px;
  align-items: end;
  inline-size: 100%;
  margin-block: -40px;
}

.wv-col {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  inline-size: 100%;
  margin-block-start: -40px;
}

.wv-stack {
  position: relative;
  inline-size: 100%;
  block-size: 140px;
  display: flex;

  /* ApexCharts içeriği, bileşenin kendi stilleriyle birlikte genişler; ek :deep kuralı kullanılmadı */
  gap: 2px;
  border: 1px solid var(--v-theme-outline-variant, #626262);
  border-radius: 6px;
  background: var(--v-theme-surface-variant, #222);
  padding: 2px;
}

.wv-val {
  position: absolute;
  inset-inline-start: 50%;
  transform: translate(-50%, 0);
  inset-block-start: 2px;
  font-size: 16px;
  font-weight: 600;
  color: #fff;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 70%);
  pointer-events: none;
  line-height: 1;
  white-space: nowrap;
}

.wv-seg {
  position: relative;
  display: flex;
  align-items: flex-start;
  justify-content: center;
}

.wv-seg.work .wv-val {
  color: #e8fce9;
}

.wv-seg.stop .wv-val {
  color: #2b2200;
  text-shadow: 0 1px 2px rgba(255, 255, 255, 40%);
}

.wv-seg {
  inline-size: 100%;
  border-radius: 2px;
}

.wv-seg.work {
  background: #189c49;
}

.wv-seg.stop {
  background: #efb803;
}

.wv-fill {
  inline-size: 100%;
  border-radius: 2px;
}

.wv-day {
  color: #bdbdbd;
  font-size: 12px;
  text-align: center;
}

.weekly-count-card {
  border: 1px solid var(--v-theme-outline-variant, #656565);
  border-radius: 12px;
  background: rgb(var(--v-theme-surface));
  padding-block: 12px;
  padding-inline: 12px;
  box-shadow: 0 5px 5px rgba(0, 0, 0, 24%), 0 2px 4px rgba(0, 0, 0, 10%);
}

.wvc-bars {
  display: grid;
  grid-auto-flow: column;
  grid-auto-columns: minmax(32px, 1fr);
  gap: 10px;
  align-items: end;
  inline-size: 100%;
}

.wvc-col {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
}

.wvc-stack {
  inline-size: 100%;
  block-size: 140px;
  border: 1px solid var(--v-theme-outline-variant, #5d5d5d);
  border-radius: 6px;
  background: var(--v-theme-surface-variant, #222);
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding: 2px;
}

.wvc-label {
  position: absolute;
  inset-inline-start: 50%;
  transform: translate(-50%, 0);
  inset-block-start: 2px;
  font-size: 16px;
  font-weight: 600;
  color: #fff;
  text-shadow: 0 1px 3px rgba(0, 0, 0, 80%);
  pointer-events: none;
  line-height: 1;
  white-space: nowrap;
}

.wvc-bar {
  position: relative;
  display: flex;
  align-items: flex-start;
  justify-content: center;
}

.wvc-bar {
  inline-size: 100%;
  background: linear-gradient(180deg, #60a5fa, #2563eb);
  border-radius: 2px;
}

.wvc-fill {
  inline-size: 100%;
  border-radius: 2px;
}

.wvc-day {
  color: #bdbdbd;
  font-size: 12px;
  text-align: center;
}

.summary-card {
  border: 1px solid var(--v-theme-outline-variant, #4e4e4e);
  border-radius: 12px;
  background: rgb(var(--v-theme-surface));
  min-inline-size: 250px;
  padding-block: 14px;
  padding-inline: 16px;
  box-shadow: 0 5px 5px rgba(0, 0, 0, 24%), 0 2px 4px rgba(0, 0, 0, 10%);
}

.summary-title {
  font-weight: 600;
  margin-block-end: 8px;
}

.summary-kpis {
  display: grid;
  grid-template-columns: repeat(4, minmax(80px, 1fr));
  gap: 10px;
}

.summary-kpis .kpi {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  background: var(--v-theme-surface-variant, #171717);
}

.summary-kpis .kpi strong {
  font-size: 36px;
}

.summary-kpis .kpi.oee {
  border-width: 2px;
}

.dash-actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
  margin-block-start: 10px;
}

.refresh-btn {
  /* background: var(--v-theme-primary, #3b82f6);
  color: #fff; */
  cursor: pointer;
  padding-block: 30px;
  padding-inline: 20px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 4px;
}

.refresh-btn[disabled] {
  opacity: 0.6;
  cursor: default;
}

.last-updated {
  color: #9ca3af;
  font-size: 14px;
}

.warn {
  color: #fbbf24;
  font-size: 11px;
  font-weight: 600;
}

.hint {
  color: #6b7280;
  font-size: 11px;
}

.cards-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(260px, 1fr));
  gap: 16px;
}

.machine-card {
  display: flex;
  flex-direction: column;
  gap: 10px;
  border: 1px solid var(--v-theme-outline-variant, #5a5959);
  border-radius: 12px;
  background: rgb(var(--v-theme-surface));
  padding-block: 12px;
  padding-inline: 12px;
  box-shadow: 0 5px 5px rgba(0, 0, 0, 26%), 0 3px 10px rgba(0, 0, 0, 12%);
}

.card-top {
  color: #88bbd3;
  border-radius: 10px 10px 0 0;
  padding-inline: 10px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

.machine-name {
  margin: 0;
  font-size: 15px;
  color: #d4dff6;
}

.operator-name {
  margin: 0;
  color: #ffa500;

  /* turuncu */
  font-size: 17px;

  /* bir tık büyük */
  font-weight: 600;
}

.meta-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  border: 1px dashed var(--v-theme-outline-variant, #4e4d4d);
  border-radius: 8px;
  background: var(--v-theme-surface-variant, #141414);
  padding-block: 6px;
  padding-inline: 8px;
}

.meta-left {
  display: flex;
  align-items: center;
  gap: 8px;
  min-inline-size: 0;
}

.meta-name {
  color: var(--v-theme-on-surface, #e5e5e5);
  font-size: 18px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  border: 1px solid var(--v-theme-outline-variant, #4b4a4a);
  border-radius: 999px;
  padding-block: 2px;
  padding-inline: 8px;
  font-size: 16px;
  background: color-mix(in oklab, var(--v-theme-primary, #3b82f6) 12%, transparent);
  color: var(--v-theme-primary, #3b82f6);
}

.pill.code {
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}

.status {
  display: flex;
  align-items: center;
  border-radius: 999px;
  gap: 8px;
  font-size: 14px;
  font-weight: 600;
  padding-block: 6px;
  padding-inline: 10px;
}

.status .dot {
  position: relative;
  background: #888;
  border-radius: 10px;
  block-size: 20px;
  inline-size: 50px;
}

.status .duration {
  margin-inline-start: 6px;
  font-size: 18px;
  opacity: 0.9;
}

.status.running {
  background: color-mix(in oklab, var(--v-theme-success, #22c55e) 18%, transparent);
  color: var(--v-theme-success, #22c55e);
  border: 1px solid color-mix(in oklab, var(--v-theme-success, #22c55e) 40%, transparent);
}

.status.running .dot {
  background: #22c55e;
}

.status.running .dot::after {
  content: "";
  position: absolute;
  inset: 0;
  border-radius: inherit;
  box-shadow: 0 0 0 0 rgba(34, 197, 94, 60%);
  animation: ping-green 1.6s cubic-bezier(0, 0, 0.2, 1) infinite;
}

.status.stopped {
  background: color-mix(in oklab, var(--v-theme-warning, #fb923c) 18%, transparent);
  color: var(--v-theme-warning, #fb923c);
  border: 1px solid color-mix(in oklab, var(--v-theme-warning, #fb923c) 40%, transparent);
}

.status.stopped .dot {
  background: #fb923c;
  animation: blink-orange 1.2s ease-in-out infinite;
}

.status.offline {
  background: var(--v-theme-surface-variant, #2b2b2b);
  color: var(--v-theme-on-surface-variant, #b5b5b5);
  border: 1px solid var(--v-theme-outline-variant, #4a4a4a);
}

.status.offline .dot {
  background: #9e9e9e;
}

.kpi-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
}

.kpi-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0;
  border: 1px solid var(--v-theme-outline-variant, #2a2a2a);
  border-radius: 8px;
  background: var(--v-theme-surface-variant, #161616);
  padding: 2px;
}

.kpi-item strong {
  font-size: 30px;
}

.kpi-item.oee {
  border-width: 2px;
}

.mini-bars {
  display: grid;
  grid-template-columns: 1fr;
  gap: 10px;
}

.mini-bar {
  display: flex;
  align-items: center;
  gap: 10px;
}

.mini-bar-label {
  color: #bdbdbd;
  font-size: 12px;
  inline-size: 68px;
}

.bar {
  overflow: hidden;
  flex: 1;
  border: 1px solid var(--v-theme-outline-variant, #2a2a2a);
  border-radius: 999px;
  background: var(--v-theme-surface-variant, #222);
  block-size: 10px;
}

.fill {
  border-radius: 999px;
  block-size: 100%;
}

.fill.work {
  background: linear-gradient(90deg, #22c55e, #16a34a);
}

.fill.stop {
  background: linear-gradient(90deg, #fb923c, #f97316);
}

.fill.reason {
  background: linear-gradient(90deg, #60a5fa, #2563eb);
}

/* Yeni: Çalışma + Duruş tek satır çok renkli bar */
.multi-bar {
  overflow: hidden;
  display: flex;
  gap: 1px;
  border: 1px solid var(--v-theme-outline-variant, #2a2a2a);
  border-radius: 999px;
  background: var(--v-theme-surface-variant, #222);
  block-size: 10px;
}

.multi-seg {
  block-size: 100%;
}

.multi-seg.work {
  background: linear-gradient(90deg, #22c55e, #16a34a);
}

.multi-seg.stop {
  background: linear-gradient(90deg, #fb923c, #f97316);
}

.multi-seg.remain {
  background: repeating-linear-gradient(45deg, #2a2a2a, #2a2a2a 6px, #1a1a1a 6px, #1a1a1a 12px);
  opacity: 0.5;
}

/* Ek bilgi satırı: Süre ve duruş sebebi */
.status-extra {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  border: 1px dashed var(--v-theme-outline-variant, #4e4d4d);
  border-radius: 8px;
  background: #693d3d;
  padding-block: 6px;
  padding-inline: 8px;
}

.status-extra .label {
  color: #bdbdbd;
  font-size: 12px;
  margin-inline-end: 6px;
}

.status-extra .value {
  font-size: 14px;
  font-weight: 600;
}

.status-extra .reason {
  max-inline-size: 32ch;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.charts-grid {
  display: grid;
  grid-template-columns: 25% 74.4%;
  gap: 10px;
  align-items: stretch;
}

/* Ticker */
.ticker-card {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.ticker-shell {
  position: relative;
  overflow: hidden;
  border: 1px solid var(--v-theme-outline-variant, #2a2a2a);
  border-radius: 8px;
  background: var(--v-theme-surface-variant, #2a2a2a);
  padding-block: 12px;
  padding-inline: 20px 56px;
  min-block-size: 100px;
  display: flex;
  align-items: center;
  max-inline-size: 100%;
}

/* Fade mask */
.ticker-shell::before,
.ticker-shell::after {
  content: "";
  position: absolute;
  inset-block: 0;
  inline-size: 120px;
  pointer-events: none;
  z-index: 2;
}

.ticker-shell::before {
  inset-inline-start: 0;
  background: linear-gradient(90deg, var(--v-theme-surface-variant, #1a1b1b), var(--v-theme-font-variant, #1a1b1b00));
}

.ticker-shell::after {
  inset-inline-end: 0;
  background: linear-gradient(270deg, var(--v-theme-surface-variant, #1a1b1b), var(--v-theme-font-variant, #1a1b1b00));
}

.ticker-track {
  display: inline-flex;
  gap: 42px;
  white-space: nowrap;
  will-change: transform;
}

.ticker-item {
  font-size: 60px;
  line-height: 1.02;
  letter-spacing: 1.5px;
  color: rgba(68 157 65 / 100%);
  opacity: 0.9;
  position: relative;
  font-weight: 600;
  text-shadow: 4px 4px 10px rgba(0 0 0 / 55%);
}

.ticker-item::after {
  content: "•";
  margin-inline-start: 48px;
  color: #334155;
  opacity: 0.35;
  text-shadow: none;
}

.ticker-item:last-child::after {
  content: "";
}

.ticker-pause {
  position: absolute;
  inset-block: 8px auto;
  inset-inline-end: 8px;
  inline-size: 32px;
  block-size: 32px;
  border-radius: 8px;
  border: 1px solid rgba(255 255 255 / 15%);
  background: rgba(255 255 255 / 6%);
  backdrop-filter: blur(4px);
  color: #e2e8f0;
  cursor: pointer;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.3s, transform 0.3s;
  z-index: 3;
}

.ticker-pause:hover {
  background: rgba(255 255 255 / 12%);
  transform: translateY(-2px);
}

.ticker-pause:active {
  transform: translateY(0);
}

.ticker-hint {
  font-size: 11px;
  opacity: 0.55;
  letter-spacing: 0.5px;
}

.ticker-controls {
  position: absolute;
  inset-block-end: 6px;
  inset-inline-end: 48px;
  display: flex;
  gap: 4px;
  z-index: 3;
}

.speed-btn {
  background: rgba(255 255 255 / 8%);
  border: 1px solid rgba(255 255 255 / 18%);
  color: #e2f0e5;
  font-size: 11px;
  padding-block: 4px;
  padding-inline: 6px;
  border-radius: 6px;
  cursor: pointer;
  min-inline-size: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.25s, transform 0.25s;
}

.speed-btn:hover {
  background: rgba(255 255 255 / 14%);
}

.speed-btn:active {
  transform: scale(0.9);
}

.speed-btn.active {
  background: rgba(127 204 94 / 16.9%);
  border-color: rgba(105 241 75 / 56.1%);
}

@media (prefers-reduced-motion: reduce) {
  .ticker-controls {
    display: none !important;
  }
}

/* Veri / hata uyarıları */
.alert {
  display: flex;
  align-items: center;
  gap: 10px;
  border: 1px solid var(--v-theme-outline-variant, #444);
  border-radius: 8px;
  padding-block: 8px;
  padding-inline: 10px;
  font-size: 14px;
  background: linear-gradient(90deg, rgba(255 255 255 / 4%), rgba(255 255 255 / 2%));
}

.alert.error {
  border-color: #dc2626;
  color: #fda4a4;
}

.alert.empty {
  border-color: #64748b;
  color: #cbd5e1;
}

.alert .msg {
  flex: 1;
  opacity: 0.85;
}

.retry-btn {
  background: var(--v-theme-primary, #2563eb);
  color: #fff;
  border: none;
  border-radius: 6px;
  padding-block: 6px;
  padding-inline: 12px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.retry-btn.subtle {
  background: rgba(255 255 255 / 10%);
  color: #e2e8f0;
}

.retry-btn:hover {
  filter: brightness(1.12);
}

.retry-btn:active {
  filter: brightness(0.95);
}

@media (prefers-reduced-motion: reduce) {
  .ticker-track {
    transform: none !important;
  }
}

.chart-card {
  background: rgb(var(--v-theme-surface));
  border: 1px solid var(--v-theme-outline-variant, #262626);
  border-radius: 12px;
  padding: 12px;
  box-shadow: 0 5px 5px rgba(0, 0, 0, 24%), 0 2px 4px rgba(0, 0, 0, 10%);
}

.stacked-bars {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.stacked-row {
  display: flex;
  align-items: center;
  gap: 10px;
}

.row-label {
  color: #bdbdbd;
  font-size: 16px;
  white-space: nowrap;
  flex: 0 0 auto;
}

.row-bars {
  overflow: hidden;
  display: flex;
  flex: 1;
  border: 1px solid var(--v-theme-outline-variant, #2a2a2a);
  border-radius: 6px;
  background: var(--v-theme-surface-variant, #222);
  block-size: 14px;
}

.row-bars .seg {
  block-size: 100%;
}

.row-bars .seg.work {
  background: #189c49;
}

.row-bars .seg.stop {
  background: #efb803;
}

.reason-bars {
  display: grid;
  grid-template-columns: max-content 1fr auto;
  gap: 8px 10px;
}

.reason-row {
  display: contents;
}

.reason-row .row-label {
  white-space: nowrap;
}

.reason-row .bar {
  overflow: hidden;
  border: 1px solid var(--v-theme-outline-variant, #2a2a2a);
  border-radius: 6px;
  background: var(--v-theme-surface-variant, #222);
  block-size: 12px;
}

.reason-row .value {
  inline-size: 64px;
  text-align: end;
  font-size: 12px;
  color: #bdbdbd;
}

/* OEE renkleri */
.oee-red {
  border-color: #ef4444 !important;
}

.oee-yellow {
  border-color: #eab308 !important;
}

.oee-orange {
  border-color: #f97316 !important;
}

.oee-green {
  border-color: #22c55e !important;
}

/* Responsive */
@media (max-width: 1280px) {
  .dash-header {
    grid-template-columns: 1fr;
  }

  .header-left {
    order: 1;
  }

  .header-right {
    order: 2;
    grid-template-columns: 1fr 1fr;
  }

  .summary-card.stretch {
    flex: initial;
  }

  .cards-grid {
    grid-template-columns: repeat(2, minmax(260px, 1fr));
  }

  .charts-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 720px) {
  .dash-header {
    grid-template-columns: 1fr;
  }

  .cards-grid {
    grid-template-columns: 1fr;
  }

  .kpi-row {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* Skeletons */
.skeleton .status .dot {
  opacity: 0.5;
}

.sk-line {
  block-size: 12px;
  background: linear-gradient(90deg, rgba(200, 200, 200, 10%), rgba(200, 200, 200, 25%), rgba(200, 200, 200, 10%));
  border-radius: 6px;
  animation: sk 1.2s infinite ease-in-out;
}

.sk-fill {
  background: linear-gradient(90deg, rgba(200, 200, 200, 15%), rgba(200, 200, 200, 35%), rgba(200, 200, 200, 15%)) !important;
  animation: sk 1.2s infinite ease-in-out;
}

.slow-hint {
  align-self: flex-end;
  color: #bdbdbd;
  font-size: 12px;
}

/* İlk yükleme overlay */
.loading-overlay {
  position: fixed;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: radial-gradient(circle at center, rgba(0 0 0 / 55%), rgba(0 0 0 / 85%));
  backdrop-filter: blur(2px);
  z-index: 5000;
  padding-block: 20px;
  padding-inline: 20px;
  text-align: center;
}

.loader-box {
  max-inline-size: 520px;
  display: flex;
  flex-direction: column;
  gap: 18px;
  background: rgba(30 30 30 / 75%);
  border: 1px solid #3a3a3a;
  border-radius: 20px;
  padding-block: 42px 46px;
  padding-inline: 48px;
  box-shadow: 0 18px 48px rgba(0 0 0 / 55%), 0 4px 16px rgba(0 0 0 / 40%);
  animation: scale-in 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

.loader-box h2 {
  margin: 0;
  font-size: 24px;
  letter-spacing: 0.5px;
  font-weight: 600;
  background: linear-gradient(90deg, #60a5fa, #38bdf8);
  background-clip: text;
  color: transparent;
}

.loader-box p {
  margin: 0;
  font-size: 15px;
  line-height: 1.45;
  color: #d1d5db;
}

.spinner {
  inline-size: 72px;
  block-size: 72px;
  border-radius: 50%;
  border: 6px solid rgba(255 255 255 / 15%);
  border-inline-start-color: #3b82f6;
  border-block-end-color: #0ea5e9;
  animation: spin 1s linear infinite, glow 2.2s ease-in-out infinite;
  margin-block: 0 4px;
  margin-inline: auto;
  position: relative;
}

.spinner::after {
  content: "";
  position: absolute;
  inset: 6px;
  border-radius: inherit;
  border: 4px solid rgba(255 255 255 / 8%);
  border-block-start-color: rgba(255 255 255 / 35%);
  animation: spin 2.2s linear infinite reverse;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

@keyframes glow {

  0%,
  100% {
    box-shadow: 0 0 0 0 rgba(59 130 246 / 35%);
  }

  50% {
    box-shadow: 0 0 0 12px rgba(59 130 246 / 0%);
  }
}

@keyframes scale-in {
  0% {
    transform: scale(0.92) translateY(8px);
    opacity: 0;
  }

  100% {
    transform: scale(1) translateY(0);
    opacity: 1;
  }
}

.w-20 {
  inline-size: 20%;
}

.w-40 {
  inline-size: 40%;
}

.w-60 {
  inline-size: 60%;
}

.mt-1 {
  margin-block-start: 4px;
}

.ml-auto {
  margin-inline-start: auto;
}

@keyframes sk {
  0% {
    opacity: 0.5;
  }

  50% {
    opacity: 1;
  }

  100% {
    opacity: 0.5;
  }
}

@keyframes ping-green {
  0% {
    box-shadow: 0 0 0 0 rgba(34, 197, 94, 60%);
  }

  70% {
    box-shadow: 0 0 0 10px rgba(34, 197, 94, 0%);
  }

  100% {
    box-shadow: 0 0 0 10px rgba(34, 197, 94, 0%);
  }
}

@keyframes blink-orange {
  0% {
    opacity: 0.6;
  }

  50% {
    opacity: 1;
  }

  100% {
    opacity: 0.6;
  }
}

.wv-chart {
  margin-block-start: -30px;
  inline-size: 100% !important;
  block-size: 300px !important;
}

/* ApexCharts kapsayıcı tam genişlikte olsun */
.apex-weekly-wrap {
  flex: 1 1 100%;
  inline-size: 100%;
  min-inline-size: 0;
}

/* ApexCharts içeriği, bileşenin kendi stilleriyle birlikte genişler; ek :deep kuralı kullanılmadı */
.dx-weekly-wrap {
  flex: 1 1 100%;
  inline-size: 100%;
  min-inline-size: 0;
}
</style>

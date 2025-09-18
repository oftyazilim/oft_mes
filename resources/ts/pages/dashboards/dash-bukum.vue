<template>
  <div class="dash-container">
    <!-- İlk yükleme overlay -->
    <div v-if="initialLoading" class="loading-overlay">
      <div class="loader-box">
        <div class="spinner" aria-hidden="true"></div>
        <h2>Veriler yükleniyor</h2>
        <p>Makine durumları, OEE ve haftalık istatistikler getiriliyor. Lütfen bekleyin…</p>
      </div>
    </div>
    <header class="dash-header">
      <div class="header-left">
        <div class="title-block ms-4">
          <h1>Büküm Dashboard</h1>
          <!-- <p class="muted">Full HD ekran için optimize, mobilde akıcı.</p> -->
        </div>
        <div class="summary-card stretch">
          <div class="summary-title">Günlük Genel Değerlendirme</div>
          <div class="summary-kpis">
            <div class="kpi"><span>Uyg.</span><strong>{{ fmtPct(daySummary.availability) }}</strong></div>
            <div class="kpi"><span>Perf.</span><strong>{{ fmtPct(daySummary.performance) }}</strong></div>
            <div class="kpi"><span>Kalite</span><strong>{{ fmtPct(daySummary.quality) }}</strong></div>
            <div class="kpi oee" :class="oeeClass(daySummary.oee)"><span>OEE</span><strong>{{ fmtPct(daySummary.oee)
                }}</strong></div>
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
        </div>
      </div>
      <div class="header-right">
        <div class="weekly-card">
          <h4>Haftalık Çalışma / Duruş (dk)</h4>
          <div class="weekly-vertical mt-2">
            <div class="wv-bars">
              <template v-if="loading">
                <div v-for="i in 7" :key="'wv-skel-' + i" class="wv-col">
                  <div class="wv-stack">
                    <div class="wv-fill sk-fill" :style="{ blockSize: (20 + i * 6) + '%' }"></div>
                  </div>
                  <div class="wv-day">&nbsp;</div>
                </div>
              </template>
              <template v-else>
                <div v-for="d in weekly" :key="d.date" class="wv-col">
                  <div class="wv-stack" :title="d.work + ' dk çalışma / ' + d.stop + ' dk duruş'">
                    <div class="wv-seg work" :style="{ blockSize: weeklyHeight(d.work) }" title="Çalışma">
                      <span v-if="d.work > 0" class="wv-val">{{ d.work }}</span>
                    </div>
                    <div class="wv-seg stop" :style="{ blockSize: weeklyHeight(d.stop) }" title="Duruş">
                      <span v-if="d.stop > 0" class="wv-val">{{ d.stop }}</span>
                    </div>
                  </div>
                  <div class="wv-day">{{ d.label }}</div>
                </div>
              </template>
            </div>
          </div>
        </div>
        <div class="weekly-count-card">
          <h4>Haftalık Sayaç (adet)</h4>
          <div class="weekly-vertical mt-2">
            <div class="wvc-bars">
              <template v-if="loading">
                <div v-for="i in 7" :key="'wvc-skel-' + i" class="wvc-col">
                  <div class="wvc-stack">
                    <div class="wvc-fill sk-fill" :style="{ blockSize: (20 + i * 4) + '%' }"></div>
                  </div>
                  <div class="wvc-day">&nbsp;</div>
                </div>
              </template>
              <template v-else>
                <div v-for="d in weeklyCounts" :key="d.date" class="wvc-col">
                  <div class="wvc-stack" :title="d.count + ' adet'">
                    <div class="wvc-bar" :style="{ blockSize: weeklyCountHeight(d.count) }" title="Adet">
                      <span v-if="d.count > 0" class="wvc-label">{{ d.count }}</span>
                    </div>
                  </div>
                  <div class="wvc-day">{{ d.label }}</div>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>
    </header>

    <section class="cards-grid">
      <template v-if="loading">
        <article v-for="i in 6" :key="'skeleton-' + i" class="machine-card skeleton">
          <div class="card-top">
            <div class="names">
              <div class="sk-line w-60"></div>
              <div class="sk-line w-40 mt-1"></div>
            </div>
            <div class="status">
              <span class="dot"></span>
              <span class="sk-line w-20"></span>
            </div>
          </div>

          <div class="kpi-row">
            <div class="kpi-item">
              <div class="sk-line w-60"></div>
              <div class="sk-line w-40 mt-1"></div>
            </div>
            <div class="kpi-item">
              <div class="sk-line w-60"></div>
              <div class="sk-line w-40 mt-1"></div>
            </div>
            <div class="kpi-item">
              <div class="sk-line w-60"></div>
              <div class="sk-line w-40 mt-1"></div>
            </div>
            <div class="kpi-item">
              <div class="sk-line w-60"></div>
              <div class="sk-line w-40 mt-1"></div>
            </div>
          </div>

          <div class="mini-bars">
            <div class="multi-bar">
              <div class="multi-seg sk-fill" style="inline-size: 65%;"></div>
              <div class="multi-seg sk-fill" style="inline-size: 20%;"></div>
              <div class="multi-seg sk-fill" style="inline-size: 15%;"></div>
            </div>
          </div>
        </article>
      </template>
      <template v-else>
        <article v-for="m in machines" :key="m.id" class="machine-card">
          <div class="card-top">
            <div class="names">
              <h3 class="machine-name">{{ m.code }} - {{ m.name }}</h3>
              <p class="operator-name" v-if="m.operator">Operatör: {{ m.operator }}</p>
            </div>
            <div class="status" :class="m.status">
              <span class="dot"></span>
              <span class="label">{{ statusText(m.status) }}</span>
            </div>
          </div>

          <div class="meta-row" v-if="m.itemCode || m.itemName || m.counter !== undefined">
            <div class="meta-left">
              <span v-if="m.itemCode" class="pill code">{{ m.itemCode }}</span>
              <span v-if="m.itemName" class="meta-name">{{ m.itemName }}</span>
            </div>
            <div class="meta-right" v-if="m.counter !== undefined">Sayaç: <strong>{{ m.counter }}</strong></div>
          </div>

          <div class="kpi-row">
            <div class="kpi-item">
              <span>Uygulanabilirlik</span>
              <strong>{{ fmtPct(m.availability) }}</strong>
            </div>
            <div class="kpi-item">
              <span>Performans</span>
              <strong>{{ fmtPct(m.performance) }}</strong>
            </div>
            <div class="kpi-item">
              <span>Kalite</span>
              <strong>{{ fmtPct(m.quality) }}</strong>
            </div>
            <div class="kpi-item oee" :class="oeeClass(m.oee)">
              <span>OEE</span>
              <strong>{{ fmtPct(m.oee) }}</strong>
            </div>
          </div>

          <div class="mini-bars">
            <template v-if="m.segments && m.segments.length">
              <div class="multi-bar">
                <div v-for="(seg, idx) in m.segments" :key="m.id + '-seg-' + idx" class="multi-seg"
                  :style="getSegmentStyle(seg as any, m.segments as any)" :title="getSegmentTooltip(seg as any, m)" />
              </div>
            </template>
            <template v-else>
              <div class="multi-bar" :title="'Çalışma/Duruş oranı'">
                <div class="multi-seg work" :style="{ inlineSize: segs(m).work + '%' }" title="Çalışma"></div>
                <div class="multi-seg stop" :style="{ inlineSize: segs(m).stop + '%' }" title="Duruş"></div>
                <div class="multi-seg remain" v-if="segs(m).remain > 0" :style="{ inlineSize: segs(m).remain + '%' }"
                  title="Kalan"></div>
              </div>
            </template>
          </div>
        </article>
      </template>
    </section>

    <section class="charts-grid">
      <div class="chart-card">
        <h4>Duruş Sebepleri (Süre)</h4>
        <div v-if="loadError" class="alert error mt-2">
          <strong>Veri alınamadı:</strong>
          <span class="msg">{{ loadError }}</span>
          <button class="retry-btn" @click="manualRefresh">Tekrar Dene</button>
        </div>
        <div v-else-if="emptyData && !loading" class="alert empty mt-2">
          <span>Henüz veri yok.</span>
          <button class="retry-btn subtle" @click="manualRefresh">Yenile</button>
        </div>
        <div v-if="loading" class="reason-bars mt-2">
          <div v-for="i in 5" :key="'rs-' + i" class="reason-row">
            <div class="row-label">
              <div class="sk-line w-40"></div>
            </div>
            <div class="bar">
              <div class="fill sk-fill" :style="{ width: (100 - i * 12) + '%' }" />
            </div>
            <div class="value">
              <div class="sk-line w-20 ml-auto"></div>
            </div>
          </div>
        </div>
        <div v-else class="reason-bars mt-2">
          <div v-for="r in downtimeReasons" :key="r.name" class="reason-row">
            <div class="row-label">{{ r.name }}</div>
            <div class="bar mt-2">
              <div class="fill reason" :style="{ width: reasonWidth(r.minutes) }" />
            </div>
            <div class="value">{{ r.minutes }} dk</div>
          </div>
        </div>
      </div>
      <!-- Sağ boşluk: canlı ticker -->
      <div class="chart-card ticker-card" aria-label="Bilgilendirme akışı">
        <h4>Canlı Akış</h4>
        <div class="ticker-shell" @mouseenter="pauseTicker = true" @mouseleave="pauseTicker = false">
          <div class="ticker-track" :style="tickerStyle" ref="tickerTrackEl">
            <span v-for="(msg, i) in tickerMessagesDoubled" :key="'tk-' + i" class="ticker-item">{{ msg }}</span>
          </div>
        </div>
        <!-- <button class="ticker-pause" @click="pauseTicker = !pauseTicker"
          :title="pauseTicker ? 'Devam Et' : 'Duraklat'">
          {{ pauseTicker ? '▶' : 'Ⅱ' }}
        </button> -->
        <small class="ticker-hint" v-if="!pauseTicker">Üzerine gelince durur</small>
        <div class="ticker-controls">
          <button v-for="(sp, i) in speedOptions" :key="'sp-' + sp" class="speed-btn"
            :class="{ active: speedIndex === i }" @click="speedIndex = i" :title="sp + ' px/sn'">{{ sp }}</button>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import axios from 'axios';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
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
})
onBeforeUnmount(() => { if (tickerRaf) cancelAnimationFrame(tickerRaf) })
onBeforeUnmount(() => { window.removeEventListener('resize', measureTicker) })

type MachineStatus = 'running' | 'stopped' | 'offline';

interface MachineCard {
  id: number;
  name: string;
  code?: string;
  operator?: string;
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

const fmtPct = (v: number) => isFinite(v) ? `${Math.round(v * 100)}%` : '-';
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
    const newMachines = (data?.machines || []).map((m: any) => ({
      id: Number(m.id),
      name: String(m.name ?? ''),
      operator: m.operator || undefined,
      itemCode: m.itemCode ?? m.item_code ?? undefined,
      itemName: m.itemName ?? m.item_name ?? undefined,
      counter: (m.counter !== undefined && m.counter !== null) ? Number(m.counter) : undefined,
      status: (m.status ?? 'offline') as MachineStatus,
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

onMounted(async () => {
  document.addEventListener('visibilitychange', handleVisibility);
  await tick();
});
onBeforeUnmount(() => {
  document.removeEventListener('visibilitychange', handleVisibility);
  if (controller) controller.abort();
  if (timer) clearTimeout(timer);
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
}

.wv-bars {
  display: grid;
  grid-auto-flow: column;
  grid-auto-columns: minmax(32px, 1fr);
  gap: 10px;
  align-items: end;
  inline-size: 100%;
}

.wv-col {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
}

.wv-stack {
  position: relative;
  inline-size: 100%;
  block-size: 140px;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
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
  border: 1px solid var(--v-theme-outline-variant, #535353);
  border-radius: 8px;
  padding: 8px;
}

.summary-kpis .kpi strong {
  font-size: 18px;
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
  background: var(--v-theme-primary, #3b82f6);
  color: #fff;
  border: none;
  cursor: pointer;
  padding-block: 4px;
  padding-inline: 10px;
  border-radius: 6px;
  font-size: 13px;
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
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

.machine-name {
  margin: 0;
  font-size: 15px;
}

.operator-name {
  margin: 0;
  color: var(--v-theme-on-surface-variant, #bdbdbd);
  font-size: 15px;
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
  font-size: 14px;
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
  border-radius: 5px;
  block-size: 10px;
  inline-size: 50px;
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
  gap: 6px;
  border: 1px solid var(--v-theme-outline-variant, #2a2a2a);
  border-radius: 8px;
  background: var(--v-theme-surface-variant, #161616);
  padding: 8px;
}

.kpi-item strong {
  font-size: 22px;
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
  background: linear-gradient(90deg, #1a1b1b, #1a1b1b00);
}

.ticker-shell::after {
  inset-inline-end: 0;
  background: linear-gradient(270deg, #1a1b1b, #1a1b1b00);
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
.w-20 { inline-size: 20%; }
.w-40 { inline-size: 40%; }
.w-60 { inline-size: 60%; }
.mt-1 { margin-block-start: 4px; }
.ml-auto { margin-inline-start: auto; }
@keyframes sk {
  0% { opacity: 0.5; }
  50% { opacity: 1; }
  100% { opacity: 0.5; }
}

@keyframes ping-green {
  0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 60%); }
  70% { box-shadow: 0 0 0 10px rgba(34, 197, 94, 0%); }
  100% { box-shadow: 0 0 0 10px rgba(34, 197, 94, 0%); }
}
@keyframes blink-orange {
  0% { opacity: 0.6; }
  50% { opacity: 1; }
  100% { opacity: 0.6; }
}

</style>

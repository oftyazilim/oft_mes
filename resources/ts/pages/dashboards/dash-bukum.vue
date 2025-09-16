<template>
  <div class="dash-container">
    <header class="dash-header">
      <div class="header-left">
        <div class="title-block ms-4">
          <h1>Büküm Dashboard</h1>
          <p class="muted">Full HD ekran için optimize, mobilde akıcı.</p>
        </div>
        <div class="summary-card stretch">
          <div class="summary-title">Günlük Genel Değerlendirme</div>
          <div class="summary-kpis">
            <div class="kpi"><span>Uyg.</span><strong>{{ fmtPct(daySummary.availability) }}</strong></div>
            <div class="kpi"><span>Perf.</span><strong>{{ fmtPct(daySummary.performance) }}</strong></div>
            <div class="kpi"><span>Kalite</span><strong>{{ fmtPct(daySummary.quality) }}</strong></div>
            <div class="kpi oee" :class="oeeClass(daySummary.oee)"><span>OEE</span><strong>{{ fmtPct(daySummary.oee) }}</strong></div>
          </div>
        </div>
      </div>
      <div class="header-right">
        <div class="weekly-card">
        <h4>Haftalık Çalışma / Duruş (dk)</h4>
        <div class="weekly-vertical mt-2">
          <div class="wv-bars">
            <template v-if="loading">
              <div v-for="i in 7" :key="'wv-skel-'+i" class="wv-col">
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
                <div v-for="i in 7" :key="'wvc-skel-'+i" class="wvc-col">
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
        <div v-if="isSlow && loading" class="slow-hint">Veri yükleniyor…</div>
      </div>
    </header>

    <section class="cards-grid">
      <template v-if="loading">
        <article v-for="i in 6" :key="'skeleton-'+i" class="machine-card skeleton">
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
            <div class="kpi-item"><div class="sk-line w-60"></div><div class="sk-line w-40 mt-1"></div></div>
            <div class="kpi-item"><div class="sk-line w-60"></div><div class="sk-line w-40 mt-1"></div></div>
            <div class="kpi-item"><div class="sk-line w-60"></div><div class="sk-line w-40 mt-1"></div></div>
            <div class="kpi-item"><div class="sk-line w-60"></div><div class="sk-line w-40 mt-1"></div></div>
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
              <div v-for="(seg, idx) in m.segments" :key="m.id + '-seg-' + idx"
                   class="multi-seg"
                   :style="getSegmentStyle(seg as any, m.segments as any)"
                   :title="getSegmentTooltip(seg as any, m)" />
            </div>
          </template>
          <template v-else>
            <div class="multi-bar" :title="'Çalışma/Duruş oranı'">
              <div class="multi-seg work" :style="{ inlineSize: segs(m).work + '%' }" title="Çalışma"></div>
              <div class="multi-seg stop" :style="{ inlineSize: segs(m).stop + '%' }" title="Duruş"></div>
              <div class="multi-seg remain" v-if="segs(m).remain > 0" :style="{ inlineSize: segs(m).remain + '%' }" title="Kalan"></div>
            </div>
          </template>
        </div>
        </article>
      </template>
    </section>

    <section class="charts-grid">
      <div class="chart-card">
        <h4>Duruş Sebepleri (Süre)</h4>
        <div v-if="loading" class="reason-bars mt-2">
          <div v-for="i in 5" :key="'rs-'+i" class="reason-row">
            <div class="row-label"><div class="sk-line w-40"></div></div>
            <div class="bar"><div class="fill sk-fill" :style="{ width: (100 - i*12) + '%' }"/></div>
            <div class="value"><div class="sk-line w-20 ml-auto"></div></div>
          </div>
        </div>
        <div v-else class="reason-bars mt-2">
          <div v-for="r in downtimeReasons" :key="r.name" class="reason-row">
            <div class="row-label">{{ r.name }}</div>
            <div class="bar mt-2"><div class="fill reason" :style="{ width: reasonWidth(r.minutes) }"/></div>
            <div class="value">{{ r.minutes }} dk</div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import axios from 'axios';
import { onBeforeUnmount, onMounted, ref } from 'vue';

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
async function loadData(signal?: AbortSignal) {
  try {
    if (machines.value.length === 0) loading.value = true;
    if (slowTimer) { clearTimeout(slowTimer); slowTimer = null; }
    slowTimer = window.setTimeout(() => { isSlow.value = true; }, 2500);
    const { data } = await axios.get('/api/dash-bukum/overview', { signal, timeout: 4500 });
    machines.value = (data?.machines || []).map((m: any) => ({
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
    daySummary.value = data?.daySummary || { availability: 0, performance: 0, quality: 1, oee: 0 };
    downtimeReasons.value = data?.reasons || [];
  weekly.value = data?.weekly || [];
    weeklyCounts.value = (data?.weeklyCounts || data?.weekly_counts || []).map((d: any) => ({
      date: String(d.date),
      label: String(d.label ?? ''),
      count: Number(d.count ?? 0),
    }));
  } catch (e) {
    // 401/419 durumlarını global interceptor gösteriyor.
  } finally {
    loading.value = false;
    if (slowTimer) { clearTimeout(slowTimer); slowTimer = null; }
    if (isSlow.value) setTimeout(() => { isSlow.value = false; }, 400);
  }
}

const POLL_MS = 5000;
let timer: number | null = null;
let inFlight = false;
let controller: AbortController | null = null;

function schedule(nextMs = POLL_MS) {
  if (timer) {
    clearTimeout(timer);
  }
  timer = window.setTimeout(tick, nextMs);
}

async function tick() {
  if (document.hidden) {
    // Sekme gizliyken bekle, gereksiz istek atma
    schedule(POLL_MS);
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
.dash-container { display: flex; flex-direction: column; gap: 10px; padding-block: 0; padding-inline: 0; }
.dash-header { display: grid; grid-template-columns: minmax(250px, 25%) 1fr; gap: 16px; align-items: stretch; }
.header-left { display: flex; flex-direction: column; gap: 12px; }
.summary-card.stretch { flex: 1; display: flex; flex-direction: column; }
.summary-card.stretch .summary-kpis { margin-block-start: auto; }
.title-block h1 { margin: 0; font-size: 26px; }
.header-right { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; align-items: end; }
.dash-header h1 { margin: 0; font-size: 26px; }
.muted { color: #7d7d7d; font-size: 13px; }

.header-metrics { display: flex; gap: 16px; }
.weekly-card { border: 1px solid var(--v-theme-outline-variant, #272727); border-radius: 12px; background: rgb(var(--v-theme-surface)); padding-block: 12px; padding-inline: 12px; box-shadow: 0 10px 24px rgba(0, 0, 0, 24%), 0 2px 8px rgba(0, 0, 0, 10%); }
.weekly-vertical { display: flex; }
.wv-bars { display: grid; grid-auto-flow: column; grid-auto-columns: minmax(32px, 1fr); gap: 10px; align-items: end; inline-size: 100%; }
.wv-col { display: flex; flex-direction: column; align-items: center; gap: 6px; }
.wv-stack { position: relative; inline-size: 100%; block-size: 140px; display: flex; flex-direction: column; justify-content: flex-end; gap: 2px; border: 1px solid var(--v-theme-outline-variant, #2a2a2a); border-radius: 6px; background: var(--v-theme-surface-variant, #222); padding: 2px; }
.wv-val { position: absolute; inset-inline-start: 50%; transform: translate(-50%, 0); inset-block-start: 2px; font-size: 16px; font-weight: 600; color: #fff; text-shadow: 0 1px 2px rgba(0, 0, 0, 70%); pointer-events: none; line-height: 1; white-space: nowrap; }
.wv-seg { position: relative; display: flex; align-items: flex-start; justify-content: center; }
.wv-seg.work .wv-val { color: #e8fce9; }
.wv-seg.stop .wv-val { color: #2b2200; text-shadow: 0 1px 2px rgba(255, 255, 255, 40%); }
.wv-seg { inline-size: 100%; border-radius: 2px; }
.wv-seg.work { background: #189c49; }
.wv-seg.stop { background: #efb803; }
.wv-fill { inline-size: 100%; border-radius: 2px; }
.wv-day { color: #bdbdbd; font-size: 12px; text-align: center; }

.weekly-count-card { border: 1px solid var(--v-theme-outline-variant, #272727); border-radius: 12px; background: rgb(var(--v-theme-surface)); padding-block: 12px; padding-inline: 12px; box-shadow: 0 10px 24px rgba(0, 0, 0, 24%), 0 2px 8px rgba(0, 0, 0, 10%); }
.wvc-bars { display: grid; grid-auto-flow: column; grid-auto-columns: minmax(32px, 1fr); gap: 10px; align-items: end; inline-size: 100%; }
.wvc-col { display: flex; flex-direction: column; align-items: center; gap: 6px; }
.wvc-stack { inline-size: 100%; block-size: 140px; border: 1px solid var(--v-theme-outline-variant, #2a2a2a); border-radius: 6px; background: var(--v-theme-surface-variant, #222); display: flex; flex-direction: column; justify-content: flex-end; padding: 2px; }
.wvc-label { position: absolute; inset-inline-start: 50%; transform: translate(-50%, 0); inset-block-start: 2px; font-size: 16px; font-weight: 600; color: #fff; text-shadow: 0 1px 3px rgba(0, 0, 0, 80%); pointer-events: none; line-height: 1; white-space: nowrap; }
.wvc-bar { position: relative; display: flex; align-items: flex-start; justify-content: center; }
.wvc-bar { inline-size: 100%; background: linear-gradient(180deg, #60a5fa, #2563eb); border-radius: 2px; }
.wvc-fill { inline-size: 100%; border-radius: 2px; }
.wvc-day { color: #bdbdbd; font-size: 12px; text-align: center; }
.summary-card { border: 1px solid var(--v-theme-outline-variant, #272727); border-radius: 12px; background: rgb(var(--v-theme-surface)); min-inline-size: 250px; padding-block: 14px; padding-inline: 16px; box-shadow: 0 10px 24px rgba(0, 0, 0, 24%), 0 2px 8px rgba(0, 0, 0, 10%); }
.summary-title { font-weight: 600; margin-block-end: 8px; }
.summary-kpis { display: grid; grid-template-columns: repeat(4, minmax(80px, 1fr)); gap: 10px; }
.summary-kpis .kpi {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  background: var(--v-theme-surface-variant, #171717);
  border: 1px solid var(--v-theme-outline-variant, #2a2a2a);
  border-radius: 8px;
  padding: 8px;
}
.summary-kpis .kpi strong { font-size: 18px; }
.summary-kpis .kpi.oee { border-width: 2px; }

.cards-grid { display: grid; grid-template-columns: repeat(3, minmax(260px, 1fr)); gap: 16px; }
.machine-card {
  display: flex;
  flex-direction: column;
  gap: 10px;
  border: 1px solid var(--v-theme-outline-variant, #262626);
  border-radius: 12px;
  background: rgb(var(--v-theme-surface));
  padding-block: 12px;
  padding-inline: 12px;
  box-shadow: 0 12px 28px rgba(0, 0, 0, 26%), 0 3px 10px rgba(0, 0, 0, 12%);
}
.card-top { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.machine-name { margin: 0; font-size: 15px; }
.operator-name { margin: 0; color: var(--v-theme-on-surface-variant, #bdbdbd); font-size: 15px; }
.meta-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; border: 1px dashed var(--v-theme-outline-variant, #2a2a2a); border-radius: 8px; background: var(--v-theme-surface-variant, #141414); padding-block: 6px; padding-inline: 8px; }
.meta-left { display: flex; align-items: center; gap: 8px; min-inline-size: 0; }
.meta-name { color: var(--v-theme-on-surface, #e5e5e5); font-size: 14px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.pill { display: inline-flex; align-items: center; gap: 6px; border: 1px solid var(--v-theme-outline-variant, #2a2a2a); border-radius: 999px; padding-block: 2px; padding-inline: 8px; font-size: 16px; background: color-mix(in oklab, var(--v-theme-primary, #3b82f6) 12%, transparent); color: var(--v-theme-primary, #3b82f6); }
.pill.code { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
.status { display: flex; align-items: center; border-radius: 999px; gap: 8px; font-size: 14px; font-weight: 600; padding-block: 6px; padding-inline: 10px; }
.status .dot { position: relative; background: #888; border-radius: 5px; block-size: 10px; inline-size: 50px; }
.status.running {
  background: color-mix(in oklab, var(--v-theme-success, #22c55e) 18%, transparent);
  color: var(--v-theme-success, #22c55e);
  border: 1px solid color-mix(in oklab, var(--v-theme-success, #22c55e) 40%, transparent);
}
.status.running .dot { background: #22c55e; }
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
.status.stopped .dot { background: #fb923c; animation: blink-orange 1.2s ease-in-out infinite; }
.status.offline { background: var(--v-theme-surface-variant, #2b2b2b); color: var(--v-theme-on-surface-variant, #b5b5b5); border: 1px solid var(--v-theme-outline-variant, #4a4a4a); }
.status.offline .dot { background: #9e9e9e; }

.kpi-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
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
.kpi-item strong { font-size: 22px; }
.kpi-item.oee { border-width: 2px; }

.mini-bars { display: grid; grid-template-columns: 1fr; gap: 10px; }
.mini-bar { display: flex; align-items: center; gap: 10px; }
.mini-bar-label { color: #bdbdbd; font-size: 12px; inline-size: 68px; }
.bar { overflow: hidden; flex: 1; border: 1px solid var(--v-theme-outline-variant, #2a2a2a); border-radius: 999px; background: var(--v-theme-surface-variant, #222); block-size: 10px; }
.fill { border-radius: 999px; block-size: 100%; }
.fill.work { background:linear-gradient(90deg, #22c55e, #16a34a); }
.fill.stop { background:linear-gradient(90deg, #fb923c, #f97316); }
.fill.reason { background:linear-gradient(90deg, #60a5fa, #2563eb); }

/* Yeni: Çalışma + Duruş tek satır çok renkli bar */
.multi-bar { overflow: hidden; display: flex; gap: 1px; border: 1px solid var(--v-theme-outline-variant, #2a2a2a); border-radius: 999px; background: var(--v-theme-surface-variant, #222); block-size: 10px; }
.multi-seg { block-size: 100%; }
.multi-seg.work { background: linear-gradient(90deg, #22c55e, #16a34a); }
.multi-seg.stop { background: linear-gradient(90deg, #fb923c, #f97316); }
.multi-seg.remain { background: repeating-linear-gradient(45deg, #2a2a2a, #2a2a2a 6px, #1a1a1a 6px, #1a1a1a 12px); opacity: 0.5; }

.charts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.chart-card { background: rgb(var(--v-theme-surface)); border: 1px solid var(--v-theme-outline-variant, #262626); border-radius: 12px; padding: 12px; box-shadow: 0 10px 24px rgba(0, 0, 0, 24%), 0 2px 8px rgba(0, 0, 0, 10%); }
.stacked-bars { display: flex; flex-direction: column; gap: 8px; }
.stacked-row { display: flex; align-items: center; gap: 10px; }
.row-label { color: #bdbdbd; font-size: 16px; white-space: nowrap; flex: 0 0 auto; }
.row-bars { overflow: hidden; display: flex; flex: 1; border: 1px solid var(--v-theme-outline-variant, #2a2a2a); border-radius: 6px; background: var(--v-theme-surface-variant, #222); block-size: 14px; }
.row-bars .seg { block-size: 100%; }
.row-bars .seg.work { background:#189c49; }
.row-bars .seg.stop { background:#efb803; }

.reason-bars { display: grid; grid-template-columns: max-content 1fr auto; gap: 8px 10px; }
.reason-row { display: contents; }
.reason-row .row-label { white-space: nowrap; }
.reason-row .bar { overflow: hidden; border: 1px solid var(--v-theme-outline-variant, #2a2a2a); border-radius: 6px; background: var(--v-theme-surface-variant, #222); block-size: 12px; }
.reason-row .value { inline-size: 64px; text-align: end; font-size: 12px; color: #bdbdbd; }

/* OEE renkleri */
.oee-red { border-color: #ef4444 !important; }
.oee-yellow { border-color: #eab308 !important; }
.oee-orange { border-color: #f97316 !important; }
.oee-green { border-color: #22c55e !important; }

/* Responsive */
@media (max-width: 1280px) {
  .dash-header { grid-template-columns: 1fr; }
  .header-left { order: 1; }
  .header-right { order: 2; grid-template-columns: 1fr 1fr; }
  .summary-card.stretch { flex: initial; }
  .cards-grid { grid-template-columns: repeat(2, minmax(260px, 1fr)); }
  .charts-grid { grid-template-columns: 1fr; }
}

@media (max-width: 720px) {
  .dash-header { grid-template-columns: 1fr; }
  .cards-grid { grid-template-columns: 1fr; }
  .kpi-row { grid-template-columns: repeat(2, 1fr); }
}

/* Skeletons */
.skeleton .status .dot { opacity: 0.5; }
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
.slow-hint { align-self: flex-end; color: #bdbdbd; font-size: 12px; }
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

<template>
  <!-- İlk yükleme overlay -->
  <DxLoadPanel v-model:visible="firstLoadOverlayVisible" :show-indicator="true" :show-pane="true" :shading="true"
    message="Montaj Duruşları Yükleniyor" :position="{ of: '#grid' }" />

  <VCard class="mt-0 pa-4 pt-2">
    <VCardText class="mt-0 pa-4 ms-2">
      <div id="liste" style="margin-block-end: -10px;">

        <!-- Grafik: Duruş sebeplerine göre toplam süre (dk) - yatay bar (üstte) -->
        <div id="durus-chart" style="display: grid; margin-block-end: 8px;">
          <div
            style="display: flex; align-items: center; justify-content: flex-end; gap: 8px; margin-block-end: -46px; margin-inline-end: 60px;">
            <span style="color: var(--v-theme-on-surface); font-size: 12px;">En çok</span>
            <DxNumberBox :value="topN" :min="1" :max="100" :show-spin-buttons="true" :on-value-changed="onTopNChanged"
              style="inline-size: 80px;" />
            <!-- <span style="color: var(--v-theme-on-surface); font-size: 12px;">sebep</span> -->
          </div>
          <DxChart ref="chartRef" :data-source="chartTopNHours" palette="Soft"
            style="block-size: 420px; inline-size: 100%;" :rotated="true" :customize-point="customizePoint">
            <DxCommonSeriesSettings type="bar" argument-field="arg" value-field="valHours" />
            <DxSeries type="bar" argument-field="arg" value-field="valHours" name="Süre (saat)">
              <DxLabel :visible="true" :format="{ type: 'fixedPoint', precision: 1 }"
                :font="{ color: chartTextColor }" />
            </DxSeries>
            <DxArgumentAxis :categories="chartCategories">
              <DxLabel :font="{ color: chartTextColor }" />
            </DxArgumentAxis>
            <DxValueAxis>
              <DxLabel :font="{ color: chartTextColor }" />
              <DxTitle text="Süre (saat)" :font="{ color: chartTextColor }" />
            </DxValueAxis>
            <DxLegend :visible="false" />
            <DxTooltip :enabled="true" :customize-tooltip="customizeTooltip" :color="chartTooltipBg"
              :border="{ color: chartTooltipBorderColor, width: 1, opacity: 0.35 }"
              :font="{ color: chartTooltipTextColor }" />
            <DxTitle :text="chartTitle" :font="{ color: chartTextColor }" />
            <DxChartExport :enabled="true" file-name="montaj-durus-sureleri" :formats="['PNG', 'JPEG', 'PDF', 'SVG']"
              :printing-enabled="false" />
            <DxSize :height="420" />
          </DxChart>
        </div>
      </div>
    </VCardText>
  </VCard>

  <VCard class="mt-4 pa-0 pt-4">
    <VCardText class="mt-0 pa-0 ms-2 me-1">
      <VCol cols="12" class="mt-0 pa-1 pe-2">
        <div id="liste" style="margin-block-end: -10px;">
          <DxDataGrid id="grid" ref="dataGridRef" :key="gridKey" :data-source="gridData" key-expr="id"
            :show-borders="true" :focused-row-enabled="true" :row-alternation-enabled="true" :min-width="200"
            @exporting="onExporting" :allow-column-reordering="true" :column-auto-width="false"
            @content-ready="onContentReady" @focused-row-changed="onFocusedRowChanged" :allow-column-resizing="true"
            column-resizing-mode="widget" @cell-prepared="onCellPrepared" @selection-changed="onSelectionChanged"
            :auto-navigate-to-focused-row="true" @row-prepared="onRowPrepared" :on-option-changed="handleOptionChanged"
            @contextMenuPreparing="onContextMenuPreparing" v-model:focused-row-key="focusedRowKey"
            :selected-rows-keys="selectedRows">
            <DxColumn data-field="id" caption="ID" :visible="true" width="90" />
            <DxColumn data-field="istasyon" caption="İSTASYON" :visible="true" width="250" />
            <DxColumn data-field="durum_bas_tarihi" caption="BAŞLANGIÇ" data-type="date" :width="160" :visible="true"
              :format="{
                formatter: (d) =>
                  new Intl.DateTimeFormat('tr-TR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                  })
                    .format(new Date(d))
                    .replace(/\//g, '.'),
              }" />
            <DxColumn data-field="durum_bit_tarihi" caption="BİTİŞ" data-type="date" :width="160" :visible="true"
              :format="{
                formatter: (d) =>
                  d
                    ? new Intl.DateTimeFormat('tr-TR', {
                      year: 'numeric',
                      month: '2-digit',
                      day: '2-digit',
                      hour: '2-digit',
                      minute: '2-digit',
                    })
                      .format(new Date(d))
                      .replace(/\//g, '.')
                    : '-',
              }" />
            <DxColumn data-field="durum_suresi" caption="SÜRE (dk)" data-type="number" :width="160" :visible="true"
              :cell-template="'durationCell'" />
            <DxColumn data-field="durus_sebebi_kodu" caption="SEBEP KODU" :visible="true" width="120" />
            <DxColumn data-field="durus_sebebi" caption="SEBEP" :visible="true" width="180" />
            <DxColumn data-field="is_emri_no" caption="İŞ EMRİ NO" :visible="true" width="140" />
            <DxColumn data-field="urun_kodu" caption="ÜRÜN KODU" :visible="true" width="140" />
            <DxColumn data-field="urun_adi" caption="ÜRÜN ADI" :visible="true" min-width="200" />
            <DxColumn data-field="personel_id" caption="PERSONEL ID" :visible="true" width="120" />
            <DxColumn data-field="isemri_id" caption="İŞ EMRİ ID" :visible="false" width="120" />
            <DxColumn data-field="urun_id" caption="ÜRÜN ID" :visible="false" width="120" />
            <!-- <DxColumn data-field="sip_detay_id" :min-width="120" :width="140" :allow-sorting="false" /> -->

            <DxGridLoadPanel :enabled="firstLoadDone" :text="loadingMessage || 'Yükleniyor...'" :show-indicator="true"
              :show-pane="true" :shading="true" />
            <DxSelection mode="single" />
            <DxGrouping :auto-expand-all="expandAll" />
            <DxGroupPanel :visible="true" />
            <DxHeaderFilter :visible="true" />
            <DxFilterPanel :visible="true" />
            <DxFilterRow :visible="goster" />
            <DxSearchPanel :visible="true" :width="240" />
            <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always" />
            <DxSorting mode="multiple" />
            <!-- <DxRowDragging :allow-reordering="true" :on-reorder="onReorder" :show-drag-icons="true" /> -->
            <!-- <DxStateStoring :enabled="true" type="localStorage" :storage-key="tab" /> -->
            <DxExport :enabled="true" :allow-export-selected-data="false" />

            <DxColumnChooser height="540px" :enabled="true" mode="select" :position="position">
              <DxColumnChooserSearch :enabled="true" />
              <DxColumnChooserSelection :allow-select-all="true" :select-by-click="true" :recursive="true" />
            </DxColumnChooser>

            <DxToolbar>
              <DxItem location="before" name="groupPanel" />
              <DxItem location="before" template="fromDateTemplate" />
              <DxItem location="before" template="toDateTemplate" />
              <DxItem location="before" template="bugunTemplate" />
              <DxItem location="before" template="dunTemplate" />
              <DxItem location="before" template="minSureTemplate" />
              <DxItem location="before" template="filtreleTemplate" />
              <DxItem location="before" template="temizleTemplate" />
              <DxItem location="before" template="esikTemplate" />
              <DxItem location="before" template="clipTemplate" />
              <DxItem name="exportButton" />
              <DxItem name="columnChooserButton" />
              <DxItem name="searchPanel" />
            </DxToolbar>

            <DxSummary>
              <DxGroupItem :align-by-column="true" column="durus_sebebi" summary-type="count" display-format="{0} duruş"
                :alignment="'right'" />
              <DxGroupItem :align-by-column="true" column="durum_suresi" summary-type="sum" :alignment="'right'"
                :customize-text="formatSummaryText" />

              <DxTotalItem :align-by-column="true" column="durus_sebebi" summary-type="count" display-format="{0} duruş"
                :alignment="'right'" />
              <DxTotalItem :align-by-column="true" column="durum_suresi" summary-type="sum" :alignment="'right'"
                :customize-text="formatSummaryText" />
            </DxSummary>

            <template #fromDateTemplate>
              <div style="
                  display: flex;
                  align-items: center;
                  gap: 6px;
                  margin-inline-start: 10px;">
                <span style="color: var(--v-theme-on-surface); font-size: 12px;">Başlangıç</span>
                <DxDateBox :value="fromDate" type="date" display-format="yyyy-MM-dd"
                  :on-value-changed="onFromChanged" />
              </div>
            </template>

            <template #toDateTemplate>
              <div style="display: flex; align-items: center; gap: 6px;">
                <span style="color: var(--v-theme-on-surface); font-size: 12px;">Bitiş</span>
                <DxDateBox :value="toDate" type="date" display-format="yyyy-MM-dd" :on-value-changed="onToChanged" />
              </div>
            </template>

            <template #filtreleTemplate>
              <DxButton icon="filter" text="Listele" styling-mode="contained" type="default" @click="Filtrele" />
            </template>

            <template #temizleTemplate>
              <DxButton icon="close" text="Temizle" styling-mode="outlined" type="normal" @click="Temizle" />
            </template>

            <template #minSureTemplate>
              <div style="display: flex; align-items: center; gap: 6px; inline-size: 100px;">
                <span style="color: var(--v-theme-on-surface); font-size: 12px;">Min Süre (dk)</span>
                <DxNumberBox :value="minMinutes" :min="0" :max="1440" :show-spin-buttons="true"
                  :on-value-changed="onMinMinutesChanged" />
              </div>
            </template>

            <!-- Süre sütunu: bar + sayı etiketi -->
            <template #durationCell="{ data }">
              <div class="duration-cell">
                <div class="duration-cell__bar" :style="{
                  inlineSize:
                    Math.min(
                      100,
                      Math.round(
                        ((Number(data.value) || 0) / effectiveMaxDuration) *
                        100
                      )
                    ) + '%',
                  background: getBarGradient(
                    (data && data.data && data.data.durum) || null
                  ),
                }"></div>
                <div class="duration-cell__label">
                  {{
                    new Intl.NumberFormat("tr-TR", {
                      maximumFractionDigits: 0,
                    }).format(Number(data.value) || 0)
                  }}
                </div>
              </div>
            </template>

            <template #clipTemplate>
              <DxButton :icon="clipQ95 ? 'percent' : 'percent'" :text="clipQ95 ? 'Q95 Açık' : 'Q95 Kapalı'"
                styling-mode="outlined" type="default" @click="toggleClip" />
            </template>

            <template #bugunTemplate>
              <DxButton icon="event" text="Bugün" styling-mode="outlined" type="default" @click="Bugun" />
            </template>

            <template #dunTemplate>
              <DxButton icon="event" text="Dün" styling-mode="outlined" type="default" @click="Dun" />
            </template>

            <template #esikTemplate>
              <div style="
                  display: flex;
                  align-items: center;
                  gap: 6px;
                  inline-size: 120px;">
                <span style="color: var(--v-theme-on-surface); font-size: 12px;">Eşik (dk)</span>
                <DxNumberBox :value="longDowntimeMin" :min="1" :max="1440" :show-spin-buttons="true"
                  :on-value-changed="onEsikChanged" />
              </div>
            </template>
          </DxDataGrid>

          <!-- Sebep Değiştir Dialog (tek alan: Sebep - AppSelect) -->
          <VDialog v-model="reasonDialog.visible" max-width="520">
            <VCard>
              <VCardTitle> Duruş Sebebi Değiştir </VCardTitle>
              <VCardText>
                <div style="display: grid; gap: 12px;">
                  <div>
                    <!-- <div style="color: var(--v-theme-on-surface); font-size: 12px; margin-block-end: 6px;">Sebep</div> -->
                    <AppSelect v-model="selectedSebep" :items="reasonOptions" item-title="description"
                      item-value="break_reason_code" return-object label="Duruş Sebebi" single-line
                      placeholder="Seçiniz..." variant="outlined" :menu-props="{ zIndex: 4000, maxHeight: 400 }"
                      :custom-filter="appSelectFilter" />
                  </div>
                </div>
              </VCardText>
              <VCardActions>
                <VSpacer />
                <VBtn variant="text" @click="closeReasonDialog">Vazgeç</VBtn>
                <VBtn color="primary" variant="flat" :disabled="!selectedSebep" @click="saveReason">Kaydet</VBtn>
              </VCardActions>
            </VCard>
          </VDialog>
          <DxLoadPanel v-model:visible="loadingVisible" :show-indicator="true" :show-pane="true" :shading="true"
            :message="loadingMessage || 'Yükleniyor...'" :position="{ of: '#grid' }" />
        </div>

      </VCol>
    </VCardText>
  </VCard>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import { useTheme } from "vuetify";

// import { DxTooltip } from 'devextreme-vue/tooltip';
import { usePageTitleStore } from "@/stores/pageTitle";
import axios from "axios";
import { DxButton } from "devextreme-vue/button";
import DxChart, {
  DxArgumentAxis,
  DxExport as DxChartExport,
  DxCommonSeriesSettings,
  DxLabel,
  DxLegend,
  DxSeries,
  DxSize,
  DxTitle,
  DxTooltip,
  DxValueAxis,
} from "devextreme-vue/chart";
import {
  DxColumn,
  DxColumnChooser,
  DxColumnChooserSearch,
  DxColumnChooserSelection,
  DxDataGrid,
  DxExport,
  DxFilterPanel,
  DxFilterRow,
  DxGroupItem,
  DxGroupPanel,
  DxGrouping,
  DxHeaderFilter,
  DxItem,
  DxScrolling,
  DxSearchPanel,
  DxSelection,
  DxSorting,
  DxSummary,
  DxToolbar,
  DxTotalItem,
} from "devextreme-vue/data-grid";
import DxDateBox from "devextreme-vue/date-box";
import { DxLoadPanel } from "devextreme-vue/load-panel";
import DxNumberBox from "devextreme-vue/number-box";
// PivotGrid kaldırıldı
import notify from "devextreme/ui/notify";
// PivotGrid data source tipleri kaldırıldı
// Chart export ve print modülleri (yan etki ile aktif eder)
import "devextreme/viz/export";

const pageTitleStore = usePageTitleStore();
const pageName = "Duruşlar";
const pageAlias = "Montaj";

pageTitleStore.setTitle(`${pageName} (${pageAlias})`);
document.title = `OFT - ${pageName} | ${pageAlias}`;

// definePage({
//   meta: { action: ["read"], subject: ["planlama", "montaj"] },
// });

// Basit durumlar için yardımcılar şimdilik gereksiz; gerekirse ekleriz.
const gridKey = ref(0);
const gridData = ref<any[]>([]);
const loadingVisible = ref(false);
const firstLoadDone = ref(false);
const loadingMessage = ref("");
const dataGridRef = ref<DxDataGrid | null>(null);
const chartRef = ref<InstanceType<typeof DxChart> | null>(null);
const focusedRowKey = ref<number | null>(null);
const selectedRows = ref<number[]>([]);
const expandAll = ref(true);
const goster = ref(true);
const position = { of: "#grid" };
const longDowntimeMin = ref<number>(
  Number(localStorage.getItem("montaj.longDowntimeMin") || 30)
);
// API minimum süre filtresi (dakika) - default 1
const minMinutes = ref<number>(Number(localStorage.getItem("montaj.minMinutes") || 1) || 1);
const fromDate = ref<Date | null>(null);
const toDate = ref<Date | null>(null);
// Chart için gridData'dan toplanmış veri (sebebe göre toplam süre)
const chartData = computed(() => {
  const sums = new Map<string, number>();
  for (const r of gridData.value) {
    const keyRaw = (r && (r.durus_sebebi || r.durus_sebebi_kodu)) as string | undefined;
    const key = (keyRaw && String(keyRaw).trim()) || 'Diğer';
    const val = Number(r?.durum_suresi) || 0;
    sums.set(key, (sums.get(key) || 0) + val);
  }
  const arr = Array.from(sums.entries()).map(([arg, val]) => ({ arg, val }));
  // Büyükten küçüğe sırala, 'Diğer'i en sona koy
  const main = arr.filter((x) => x.arg !== 'Diğer').sort((a, b) => b.val - a.val);
  const others = arr.filter((x) => x.arg === 'Diğer');
  return [...main, ...others];
});

// Top N ayarı (varsayılan 10)
const topN = ref<number>(Number(localStorage.getItem("montaj.chartTopN") || 10) || 10);
const onTopNChanged = (e: any) => {
  const v = Math.max(1, Math.min(100, Number(e?.value || 10)));
  topN.value = v;
  localStorage.setItem("montaj.chartTopN", String(topN.value));
};

// Grafikte kullanılacak veri ve kategoriler (sıra korunur)
const chartTopN = computed(() => chartData.value.slice(0, topN.value));
// Saat cinsine dönüştürülmüş veri
const chartTopNHours = computed(() =>
  chartTopN.value.map((d) => ({ ...d, valHours: (Number(d.val) || 0) / 60 }))
);
const chartCategories = computed(() => chartTopN.value.map((d) => d.arg));
// Başlıkta saat ibaresi
const chartTitle = computed(() => `Duruş Süre Toplamları (Saat, İlk ${topN.value})`);

const firstLoadOverlayVisible = computed({
  get: () => !firstLoadDone.value,
  set: (v: boolean) => {
    firstLoadDone.value = !v;
  },
});
// Vuetify tema durumu (light/dark) tespiti
const theme = useTheme();
const isDarkTheme = computed<boolean>(() => {
  try {
    // Vuetify 3
    // @ts-ignore - runtime'da mevcut
    return !!theme.global.current.value.dark;
  } catch {
    return !!document.querySelector("body.v-theme--dark, .v-theme--dark");
  }
});
// Grafik metin/tooltip renkleri (tema duyarlı)
const chartTextColor = computed<string>(() =>
  isDarkTheme.value ? "#eceff1" : "#263238"
);
const chartTooltipBg = computed<string>(() =>
  isDarkTheme.value ? "#263238" : "#ffffff"
);
const chartTooltipBorderColor = computed<string>(() =>
  isDarkTheme.value ? "#90a4ae" : "#90a4ae"
);
const chartTooltipTextColor = computed<string>(() =>
  isDarkTheme.value ? "#eceff1" : "#263238"
);
// Listelenen verideki maksimum süre (dk)
const maxDuration = computed<number>(() => {
  const max = gridData.value.reduce((m, r) => {
    const v = Number(r?.durum_suresi) || 0;
    return v > m ? v : m;
  }, 0);
  // 0 bölünmesini önlemek için en az 1
  return max > 0 ? max : 1;
});

// Q95 kırpma togglesı ve etkin maksimum süre hesaplaması
const clipQ95 = ref<boolean>(localStorage.getItem("montaj.clipQ95") === "1");
const durations = computed<number[]>(() =>
  gridData.value.map((r) => Number(r?.durum_suresi) || 0)
);

function percentile(values: number[], p: number): number {
  if (!values || values.length === 0) return 1;
  const arr = [...values].sort((a, b) => a - b);
  const idx = (arr.length - 1) * p;
  const lo = Math.floor(idx);
  const hi = Math.ceil(idx);
  if (hi === lo) return Math.max(1, arr[lo]);
  const h = idx - lo;
  return Math.max(1, arr[lo] + (arr[hi] - arr[lo]) * h);
}

const effectiveMaxDuration = computed<number>(() => {
  if (clipQ95.value) {
    const q95 = percentile(durations.value, 0.95);
    return q95 > 0 ? q95 : 1;
  }
  return maxDuration.value;
});

const toggleClip = () => {
  clipQ95.value = !clipQ95.value;
  localStorage.setItem("montaj.clipQ95", clipQ95.value ? "1" : "0");
};

// Bar rengini duruma göre belirle
const getBarGradient = (status: string | null | undefined): string => {
  const dark = isDarkTheme.value;
  if (dark) {
    switch (status) {
      case "DURUYOR":
        return "linear-gradient(90deg, #ff8a65, #e53935)";
      case "MOLA":
        return "linear-gradient(90deg, #ba68c8, #8e24aa)";
      default:
        return "linear-gradient(90deg, #90caf9, #42a5f5)";
    }
  } else {
    // Light temada daha yumuşak/pastel tonlar
    switch (status) {
      case "DURUYOR":
        return "linear-gradient(90deg, #ffccbc, #ff8a65)";
      case "MOLA":
        return "linear-gradient(90deg, #e1bee7, #ce93d8)";
      default:
        return "linear-gradient(90deg, #bbdefb, #90caf9)";
    }
  }
};

// Grafik renkleri: tek kurumsal pastel kırmızı, 'Diğer' için gri
const corporateRedLight = "#ff9e9e"; // pastel kırmızı (light)
const corporateRedDark = "#ef5350";  // daha doygun kırmızı (dark)
const otherGray = "#b0bec5";

function isOther(arg?: string): boolean {
  return (arg || "").trim().toLowerCase() === "diğer";
}

function customizePoint(info: any) {
  const arg = String(info?.argument ?? "");
  if (isOther(arg)) return { color: otherGray };
  return { color: isDarkTheme.value ? corporateRedDark : corporateRedLight };
}

function customizeTooltip(info: any) {
  const arg = String(info?.argumentText ?? info?.argument ?? "");
  const valText = info?.valueText ?? String(info?.value ?? "0");
  return { text: `${arg}: ${valText} saat` };
}
function formatSummaryText(itemInfo: {
  value: string | number | Date;
  valueText: string;
}): string {
  // Only format if value is a number, otherwise return valueText as is
  if (typeof itemInfo.value === "number") {
    return new Intl.NumberFormat("tr-TR", { maximumFractionDigits: 0 }).format(
      itemInfo.value
    );
  }
  return itemInfo.valueText;
}
const getData = async () => {
  loadingVisible.value = true;
  try {
    const params: Record<string, string> = {};
    if (fromDate.value instanceof Date)
      params.from = fromDate.value.toISOString().slice(0, 10);
    if (toDate.value instanceof Date)
      params.to = toDate.value.toISOString().slice(0, 10);
    // minimum süre (dk) backend'e gönder
    params.min_minutes = String(minMinutes.value ?? 1);
    const { data } = await axios.get("/api/duruslar-montaj", { params });
    gridData.value = data.data || [];
    dataGridRef.value?.instance?.refresh();
    // Chart verisi computed olduğundan otomatik güncellenir
    console.log(
      "duruslar-montaj yüklendi, kayıt sayısı:",
      gridData.value.length
    );
  } catch (e) {
    console.error("duruslar-montaj yüklenemedi:", e);
    gridData.value = [];
    notify("Veri alınamadı", "error", 1500);
  } finally {
    loadingVisible.value = false;
    if (!firstLoadDone.value) firstLoadDone.value = true;
  }
};

// const Yenile = () => {
//   // Grid’i zorla yeniden oluşturmak için key değiştir, ardından veriyi çek
//   gridKey.value += 1
//   getData()
// }

const Filtrele = () => {
  getData();
};

const Temizle = () => {
  fromDate.value = null;
  toDate.value = null;
  getData();
};

const onFromChanged = (e: any) => {
  fromDate.value = e.value ?? null;
};

const Bugun = () => {
  const today = new Date();
  const yyyyMmDd = today.toISOString().slice(0, 10);
  fromDate.value = new Date(yyyyMmDd);
  toDate.value = new Date(yyyyMmDd);
  getData();
};

const Dun = () => {
  const d = new Date();
  d.setDate(d.getDate() - 1);
  const yyyyMmDd = d.toISOString().slice(0, 10);
  fromDate.value = new Date(yyyyMmDd);
  toDate.value = new Date(yyyyMmDd);
  getData();
};

const onEsikChanged = (e: any) => {
  const val = Number(e.value || 30);
  longDowntimeMin.value = isNaN(val) ? 30 : val;
  localStorage.setItem("montaj.longDowntimeMin", String(longDowntimeMin.value));
};
const onToChanged = (e: any) => {
  toDate.value = e.value ?? null;
};

const onMinMinutesChanged = (e: any) => {
  const v = Number(e?.value ?? 1);
  minMinutes.value = isNaN(v) ? 1 : Math.max(0, Math.min(1440, v));
  localStorage.setItem("montaj.minMinutes", String(minMinutes.value));
};

// Uzun duruşları kırmızı tonla vurgula
const onRowPrepared = (e: any) => {
  if (e.rowType !== "data") return;
  const r = e.data;
  if (
    r?.durum === "DURUYOR" &&
    Number(r?.durum_suresi) >= longDowntimeMin.value
  ) {
    e.rowElement?.classList?.add("long-downtime");
  }
};

const onCellPrepared = (e: any) => {
  if (e.rowType !== "data") return;
  const r = e.data;
  const isLong =
    r?.durum === "DURUYOR" && Number(r?.durum_suresi) >= longDowntimeMin.value;
  if (isLong && e.column?.dataField === "durum_suresi") {
    if (e.cellElement) {
      e.cellElement.classList.add("duration-long");
      e.cellElement.style.fontWeight = "700";
    }
  }
};

// Diğer grid event’leri için no-op stub’lar
const onExporting = () => { };
const onContentReady = () => { };
const onFocusedRowChanged = () => { };
const onSelectionChanged = () => { };
const handleOptionChanged = () => { };
// Sağ tık menüsü: ‘Sebebi Değiştir’
function onContextMenuPreparing(e: any) {
  if (e.target === 'content' && e.row && e.row.rowType === 'data') {
    e.items = e.items || [];
    e.items.push({
      text: 'Sebebi Değiştir',
      onItemClick: () => openReasonDialog(e.row.data)
    });
  }
}

// Dialog state ve form
const reasonDialog = ref<{ visible: boolean, row: any | null }>({ visible: false, row: null });
const reasonForm = ref<{ kod: string | null, ad: string | null }>({ kod: null, ad: null });
const reasonOptions = ref<any[]>([]);
const selectedSebep = ref<{ break_reason_code: string; description: string } | null>(null);

// AppSelect için case-insensitive filtre
const appSelectFilter = (item: any, queryText: string, itemText: string) => {
  const text = (itemText || '').toString().toLocaleLowerCase('tr-TR');
  const query = (queryText || '').toString().toLocaleLowerCase('tr-TR');
  return text.includes(query);
};

function openReasonDialog(row: any) {
  reasonDialog.value = { visible: true, row };
  reasonForm.value = {
    kod: row?.durus_sebebi_kodu ?? null,
    ad: row?.durus_sebebi ?? null,
  };
  // Sebep seçeneklerini yükle (uyumsoft.prdd_break_reason)
  axios.get('/api/durus-sebepleri-al', { params: { istasyon: row?.istasyon ?? '' } })
    .then(res => {
      reasonOptions.value = res.data?.durusSebepleri || [];
      // Mevcut değerler ile selectedSebep'i eşle
      if (reasonForm.value.kod) {
        selectedSebep.value = reasonOptions.value.find((o: any) => o?.break_reason_code === reasonForm.value.kod) || null;
      } else if (reasonForm.value.ad) {
        selectedSebep.value = reasonOptions.value.find((o: any) => o?.description === reasonForm.value.ad) || null;
      } else {
        selectedSebep.value = null;
      }
    })
    .catch(() => { reasonOptions.value = []; });
}

function closeReasonDialog() {
  reasonDialog.value.visible = false;
}

// selectedSebep değiştiğinde formu senkronize et
watch(selectedSebep, (val) => {
  if (val) {
    reasonForm.value.kod = val.break_reason_code || null;
    reasonForm.value.ad = val.description || null;
  } else {
    reasonForm.value.kod = null;
    reasonForm.value.ad = null;
  }
});

async function saveReason() {
  const row = reasonDialog.value.row;
  if (!row || !row.id) return;
  const body = {
    durus_sebebi_kodu: reasonForm.value.kod,
    durus_sebebi: reasonForm.value.ad,
  };
  try {
    await axios.put(`/api/duruslar-montaj/${row.id}/reason`, body);
    // Grid verisini yerinde güncelle
    const idx = gridData.value.findIndex(r => r.id === row.id);
    if (idx >= 0) {
      gridData.value[idx] = { ...gridData.value[idx], ...body };
    }
    dataGridRef.value?.instance?.refresh();
    notify('Duruş sebebi güncellendi', 'success', 1200);
    closeReasonDialog();
  } catch (e: any) {
    notify(e?.response?.data?.message || 'Güncelleme başarısız', 'error', 1500);
  }
}

onMounted(() => {
  pageTitleStore.setTitle(`${pageName} (${pageAlias})`);
  document.title = `OFT - ${pageName} | ${pageAlias}`;
  getData();
});

// Pivot kaldırıldığı için izleme gerekmez
</script>

<style>
.v-progress-linear {
  inline-size: 100% !important;
}

/* İlk yükleme overlay stilleri */
.montaj-loading-overlay {
  position: fixed;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  animation: montaj-fade-in 0.4s ease;
  backdrop-filter: blur(3px);
  background:
    radial-gradient(circle at 35% 30%,
      rgba(90 130 200 / 12%),
      rgba(10 15 25 / 92%));
  inset: 0;
}

.montaj-loading-content {
  border: 1px solid rgba(255 255 255 / 8%);
  border-radius: 18px;
  animation: montaj-scale-in 0.5s cubic-bezier(0.4, 0.14, 0.3, 1.2);
  background: rgba(20 28 40 / 70%);
  box-shadow:
    0 8px 32px -4px rgba(0 0 0 / 55%),
    0 0 0 1px rgba(255 255 255 / 6%) inset;
  max-inline-size: 520px;
  padding-block: 32px 40px;
  padding-inline: 36px;
  text-align: center;
}

.montaj-spinner {
  position: relative;
  border: 5px solid rgba(255 255 255 / 12%);
  border-radius: 50%;
  animation:
    montaj-spin 1.05s linear infinite,
    montaj-pulse 2.2s ease-in-out infinite;
  block-size: 68px;
  border-block-start-color: #3b82f6;
  box-shadow: 0 0 0 0 rgba(59 130 246 / 35%);
  inline-size: 68px;
  margin-block-end: 26px;
  margin-inline: auto;
}

.montaj-loading-title {
  background: linear-gradient(92deg, #fff, #d0e4ff 28%, #9fc5ff 67%, #fff);
  background-clip: text;
  color: transparent;
  font-size: 26px;
  font-weight: 650;
  letter-spacing: 0.5px;
  margin-block: 0 14px;
  margin-inline: 0;
  text-shadow: 0 2px 12px rgba(30 55 105 / 35%);
}

.montaj-loading-hint {
  color: #d6e1f2;
  font-size: 15px;
  font-weight: 400;
  margin-block: 0 8px;
  margin-inline: 0;
}

.montaj-loading-sub {
  color: #95a6bc;
  font-size: 13px;
  letter-spacing: 0.3px;
  margin-block: 0 6px;
  margin-inline: 0;
}

.montaj-loading-timeout {
  color: #f0c674;
  font-size: 12px;
  margin-block: 14px 0;
  margin-inline: 0;
}

.montaj-loading-timeout .warn {
  color: #ffc266;
  font-weight: 600;
}

@keyframes montaj-spin {
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
}

@keyframes montaj-pulse {

  0%,
  100% {
    box-shadow: 0 0 0 0 rgba(59 130 246 / 35%);
  }

  50% {
    box-shadow: 0 0 0 12px rgba(59 130 246 / 0%);
  }
}

@keyframes montaj-scale-in {
  0% {
    opacity: 0;
    transform: scale(0.92) translateY(8px);
  }

  100% {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

@keyframes montaj-fade-in {
  0% {
    opacity: 0;
  }

  100% {
    opacity: 1;
  }
}

#grid {
  display: flex;
  flex-direction: column;
  block-size: 90vh;
  margin-block: -20px 20px;
}

.long-downtime {
  background-color: rgba(244 67 54 / 12%) !important;

  /* kırmızımsı arka plan */
}

/* Karanlık tema için kontrastı artır: zemin tonunu yükselt, sol kenara vurgu ekle */
.v-theme--dark .long-downtime {
  background-color: rgba(244 67 54 / 24%) !important;
  border-inline-start: 3px solid rgba(244 67 54 / 72%);
}

#grid .informer {
  display: grid;
  grid-template-columns: 100%;
  inline-size: 120px;
  padding-inline-end: 20px;
  text-align: center;
}

#grid .count {
  font-size: 18px;
  font-weight: 500;
}

#grid .dx-toolbar-items-container {
  min-block-size: 44px;
}

/* Süre barı hücresi */
.duration-cell {
  position: relative;
  overflow: hidden;
  border-radius: 6px;
  background-color:
    color-mix(in oklab,
      var(--v-theme-surface),
      transparent 82%);
  block-size: 22px;
  inline-size: 100%;
}

.duration-cell__bar {
  border-radius: 6px;
  background: linear-gradient(90deg, #64b5f6, #1e88e5);
  block-size: 100%;
  transition: inline-size 180ms ease;
}

.duration-cell__label {
  position: absolute;
  display: flex;
  align-items: center;
  justify-content: flex-start;
  block-size: 100%;
  color: var(--v-theme-on-surface);
  font-size: 12px;
  font-weight: 700;
  inline-size: calc(100% - 16px);
  inset-block-start: 0;
  inset-inline-start: 8px;
  text-shadow: 0 1px 2px rgba(0 0 0 / 20%);
}

/* Dark temada bar kontrastını artır */
.v-theme--dark .duration-cell {
  background-color: rgba(255 255 255 / 6%);
}

.v-theme--dark .duration-cell__bar {
  background: linear-gradient(90deg, #90caf9, #42a5f5);
}

.custom-col {
  flex: 0 0 12.5%;

  /* 1.5 sütun genişliği */
  max-inline-size: 12.5%;
}

/* DevExtreme LoadPanel message visibility tweaks */
.custom-col .dx-loadpanel-content .dx-loadpanel-message {
  color: #fff !important;
  font-weight: 600;
}

.custom-col .dx-loadpanel-content {
  background-color: rgba(0 0 0 / 65%) !important;
}

.custom-textarea textarea {
  block-size: 80px !important;

  /* Yüksekliği azaltmak için */
}

.kalin input {
  font-weight: bold !important;
}

.kalin label {
  font-weight: normal !important;
}

/* .label {
  font-size: 12px;
} */

.demo-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  block-size: 450px;
  gap: 40px;
}

.button-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 15px;
}

.dx-popup-content {
  font-size: 12px;
}

.caption {
  font-weight: 500;
  padding-block-end: 8px;
}

.content {
  display: flex;
  justify-content: space-between;
}

/* İkonların boyutunu özelleştirmek isterseniz */
.dx-icon-info {
  font-size: 16px;
}

.popup-center {
  display: flex;
  align-items: center;

  /* Dikeyde ortalama */
  justify-content: center;

  /* Yatayda ortalama */
  block-size: 100%;

  /* Popup içeriğinin tamamını kaplasın */
}
</style>

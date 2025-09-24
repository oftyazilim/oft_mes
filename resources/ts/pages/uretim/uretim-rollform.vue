<template>
  <div class="page-rollform pa-0">
    <!-- İlk yükleme overlay -->
    <div v-if="initialLoading" class="rf-loading-overlay">
      <div class="rf-loader-box">
        <div class="rf-spinner" aria-hidden="true"></div>
        <h2>Veriler yükleniyor</h2>
        <p>İstasyon, aktif iş emri, sayaç ve KPI verileri hazırlanıyor. Lütfen bekleyin…</p>
        <small v-if="!stationId && !stationDetectError" class="hint-line">İstasyon tespiti yapılıyor...</small>
        <small v-else-if="stationDetectError" class="warn-line">{{ stationDetectError }} – yeniden deniyor...</small>
      </div>
    </div>
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
                <DxCircularGauge class="oee-gauge" :value="speedUnitsHour" :subvalues="[speedTargetUnitsHour]"
                  :tooltip="gaugeTooltip" :size="gaugeSize">
                  <DxGeometry :start-angle="225" :end-angle="-45" />
                  <DxScale :start-value="0" :end-value="speedMaxUnitsHour" :tick-interval="gaugeTickInterval"
                    :minor-tick-interval="gaugeMinorTickInterval">
                    <DxTick :length="8" color="#666" />
                    <DxMinorTick :length="4" color="#444" />
                    <DxLabel :use-range-colors="true" :font="gaugeLabelFont" />
                  </DxScale>
                  <DxRangeContainer background-color="#20252b" :offset="10" :width="10">
                    <DxRange :start-value="0" :end-value="0.40 * speedMaxUnitsHour" color="#ce6978" />
                    <DxRange :start-value="0.40 * speedMaxUnitsHour" :end-value="0.70 * speedMaxUnitsHour"
                      color="#caa93b" />
                    <DxRange :start-value="0.70 * speedMaxUnitsHour" :end-value="speedMaxUnitsHour" color="#1e8e3e" />
                  </DxRangeContainer>
                  <DxValueIndicator type="triangleNeedle" :spindle-size="18" :spindle-gap-size="9" :offset="10"
                    :width="8" />
                  <!-- <DxTitle text="OEE %" /> -->
                  <DxExport :enabled="false" />
                </DxCircularGauge>
                <div class="gauge-center-value text-center">
                  <div class="label" style="font-size: 16px; margin-block: -10px;">Hız (ad/saat)</div>
                  <div style="color: goldenrod;"> {{ fmt0(speedUnitsHour) }} </div>
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
                <label>Duruş Sebebi (F8 – Yeni Duruş)</label>
                <div style="display: flex; align-items: center; gap: 8px;">
                  <AppSelect v-model="selectedSebep" :items="durusSebepleri" item-title="description"
                    item-value="break_reason_code" return-object single-line placeholder="Seçiniz..." variant="outlined"
                    :disabled="!isDurusModu || durusKayitLoading"
                    :class="{ 'app-select--force-disabled': !isDurusModu || durusKayitLoading }"
                    @keydown.enter.prevent="durusSebebiKaydet" />
                  <VBtn color="success" variant="tonal" size="small"
                    :disabled="!isDurusModu || durusKayitLoading || !selectedSebep" @click="durusSebebiKaydet">
                    Kaydet
                    <VIcon end icon="tabler-check" />
                  </VBtn>
                </div>
                <AppTextarea v-model="durusAciklamasi" placeholder="Açıklama..." disabled />
              </div>

              <div class="status-actions justify-between">
                <VBtn variant="tonal" color="error" size="small">Açıklama Gir</VBtn>
                <VSwitch color="error" v-model="arizali" label="Arızalı" value="false" :height="10" />
              </div>

              <div class="status-actions justify-between">
                <VBtn variant="tonal" color="warning" size="small" width="32%" @click="yeniDurusF8">
                  F8 - Yeni Duruş
                  <VIcon end icon="tabler-player-skip-forward" />
                </VBtn>
                <VBtn variant="tonal" color="warning" size="small" width="32%" @click="cayMolasiKaydet">
                  F9 - Çay Molası
                  <VIcon end icon="tabler-mug" />
                </VBtn>
                <VBtn variant="tonal" color="warning" size="small" width="32%" @click="yemekMolasiKaydet">
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
                  <div class="metric-time ok">{{ calismaSure }}</div>
                </div>
                <div>
                  <div class="label">Duruş</div>
                  <div class="metric-time warn">{{ durusSure }}</div>
                </div>
                <div>
                  <div class="label">Boşta</div>
                  <div class="metric-time">{{ bostaSure }}</div>
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

                    <div width="60%">
                      <div class="label">İş Emri No</div>
                      <div class="bilgi">{{ worksInfo?.worder_no }}</div>
                    </div>
                    <div width="60%">
                      <div class="label">Ürün Kodu</div>
                      <div class="bilgi">{{ worksInfo?.item_code }}</div>
                    </div>
                    <div width="60%">
                      <div class="label">Kalan</div>
                      <div class="bilgi">{{ worksInfo?.item_name }}</div>
                    </div>
                  </div>
                  <div width="60%">
                    <div class="label">Ürün Boyu</div>
                    <div class="bilgi">{{ fmt0(worksInfo?.item_length) }}</div>
                  </div>
                  <VRow class="mt-1">
                    <VCol cols="6" class="mt-0 py-2 pe-1">
                      <VBtn id="hurdaGir" block variant="outlined" color="error" @click="openHurdaDialog">
                        F8 - Hurda Girişi
                      </VBtn>
                    </VCol>
                    <VCol cols="6" class="mt-0 py-2 ps-1">
                      <VBtn variant="outlined" block @click="openUretimGirisiDialog">İş Emrini Kapat</VBtn>
                    </VCol>
                  </VRow>
                  <VRow class="mt-1">
                    <VCol cols="6" class="mt-0 py-2 pe-1">
                      <VBtn variant="outlined" block @click="openTechnicalDrawing">Teknik Resim</VBtn>
                    </VCol>
                    <VCol cols="6" class="mt-0 py-2 ps-1">
                      <VBtn variant="outlined" block>Ürün Etiketi Bas</VBtn>
                    </VCol>
                  </VRow>
                </VCol>
              </VRow>
            </section>
          </VCard>
        </VCol>
      </VRow>
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

        <DxSearchPanel :visible="true" :width="240" />

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

    <!-- Hurda Giriş Dialog -->
    <VDialog v-model="hurdaDialog" max-width="400">
      <VCard>
        <VCardTitle>Hurda Girişi</VCardTitle>
        <VCardText>
          <VTextField ref="hurdaInputRef" v-model="hurdaInput" label="Hurda Miktarı" type="number" hide-details :min="1"
            step="1" autofocus @keydown.enter.prevent="onHurdaEnter" @keyup.enter.prevent="onHurdaEnter"
            @keydown.esc.prevent="cancelHurda" />
          <div class="text-caption mt-2">Enter = Kaydet, Esc = İptal</div>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" color="grey" @click="cancelHurda">İptal (Esc)</VBtn>
          <VBtn variant="elevated" color="error" @click="kaydetHurda" :loading="hurdaKayitLoading"
            :disabled="hurdaKayitLoading">
            <template v-if="hurdaKayitLoading">
              <VProgressCircular indeterminate size="18" color="white" class="mr-2" /> Kaydediliyor...
            </template>
            <template v-else>Kaydet (Enter)</template>
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

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

    <!-- Üretim Girişi (İş Emrini Kapat) Dialog -->
    <VDialog v-model="uretimiKapatDialog" max-width="520" @keydown.esc.prevent.stop="closeUretimGirisiDialog">
      <VCard>
        <VCardTitle class="text-h5" style="background-color: green; color: black;">Üretim Girişi</VCardTitle>
        <VCardText>
          <VRow class="mb-2">
            <VCol cols="12" sm="4">
              <div class="label">İş Emri Miktarı</div>
              <div class="bilgi">{{ fmt0(worksInfo?.order_qty) }}</div>
            </VCol>
            <VCol cols="12" sm="4">
              <div class="label">Üretilen (uyum)</div>
              <div class="bilgi">{{ fmt0(worksInfo?.net_qty) }}</div>
            </VCol>
            <VCol cols="12" sm="4">
              <div class="label">Sayaç</div>
              <div class="bilgi">{{ fmt0(worksInfo?.counter) }}</div>
            </VCol>
          </VRow>
          <VTextField ref="uretimInputRef" v-model.number="manuelUretimMiktari" type="number"
            label="Elle Üretim Miktarı" variant="outlined" density="comfortable" :min="0" :step="1" hide-details
            autofocus @keydown.enter.prevent="saveUretimGirisi" @keydown.esc.prevent="closeUretimGirisiDialog" />
          <div class="text-caption mt-2">Sayaç değeri yanlış olabilir. Gerçek sayıyı giriniz...</div>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="outlined" color="grey" @click="closeUretimGirisiDialog">Vazgeç</VBtn>
          <VBtn color="primary" variant="outlined" @click="saveUretimGirisi"
            :disabled="manuelUretimMiktari == null || uretimKayitLoading" :loading="uretimKayitLoading">
            <template v-if="uretimKayitLoading">
              <VProgressCircular indeterminate size="18" color="primary" class="mr-2" /> Kaydediliyor...
            </template>
            <template v-else>Kaydet (Enter)</template>
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Duruş Sebebi Seçim Popup (ProductionCard akışı) -->
    <DxPopup v-model:visible="popupDurusSecGosterVisible" :width="500" :height="200" :hide-on-outside-click="false"
      :show-close-button="false" :drag-enabled="false" :close-on-back-button="false" :defer-rendering="false"
      :focus-state-enabled="true" :shading="true" :shading-color="'rgba(0,0,0,0.5)'" :on-hiding="onPopupHiding"
      titleTemplate="title" :animation="{ show: { type: 'fade', duration: 120 }, hide: { type: 'fade', duration: 80 } }"
      :on-shown="onDurusPopupShown">
      <template #content>
        <VRow class="pa-4">
          <AppSelect ref="durusSelectRef" v-model="selectedSebep" :items="durusSebepleri" item-title="description"
            item-value="break_reason_code" return-object label="Duruş Sebebi" single-line placeholder="Seçiniz..."
            variant="outlined" :disabled="!isDurusModu || durusKayitLoading"
            :class="{ 'app-select--force-disabled': !isDurusModu || durusKayitLoading }"
            @keydown.enter.prevent="durusSebebiKaydet" />
        </VRow>
      </template>
      <template #title>
        <p class="popup-title">Duruş Sebebini Giriniz</p>
      </template>
      <DxToolbarItem widget="dxButton" toolbar="bottom" location="center" :options="kaydetOptions" />

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
  DxValueIndicator,
} from 'devextreme-vue/circular-gauge';
import DxDataGrid, { DxColumn, DxSearchPanel } from 'devextreme-vue/data-grid';
import { DxPopup, DxToolbarItem } from 'devextreme-vue/popup';
import notify from 'devextreme/ui/notify';
import { computed, nextTick, onActivated, onMounted, onUnmounted, ref, watch } from 'vue';

definePage({
  meta: { action: ['manage', 'read'], subject: ['mekanik', 'all'] }
})

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

// --- Hız birimi dönüşüm: m/dak -> adet/saat ---
// Not: item_length mm cinsinden geliyor; m/dak hızını adet/saat'e çevirmek için:
// adet/saat = (speed_m_per_min * 60) / (item_length_m) = speed(m/dak) * 60000 / item_length(mm)
function toUnitsPerHour(speedMPerMin: number | null | undefined, itemLenMm: number | null | undefined): number {
  const v = Number(speedMPerMin ?? 0)
  const Lmm = Number(itemLenMm ?? 0)
  if (!Number.isFinite(v) || !Number.isFinite(Lmm) || Lmm <= 0) return 0
  return (v * 60000) / Lmm
}
const speedUnitsHour = computed(() => worksInfo.value?.speed)
const speedMaxUnitsHour = computed(() => worksInfo.value?.speed_max)
const speedTargetUnitsHour = computed(() => worksInfo.value?.speed_target)
// const speedUnitsHour = computed(() => toUnitsPerHour(worksInfo.value?.speed, worksInfo.value?.item_length))
// const speedMaxUnitsHour = computed(() => toUnitsPerHour(worksInfo.value?.speed_max, worksInfo.value?.item_length))
// const speedTargetUnitsHour = computed(() => toUnitsPerHour(worksInfo.value?.speed_target, worksInfo.value?.item_length))

// Gauge görsel ayarları (mevcut tanım yoksa basit defaultlar)
const gaugeTooltip = { enabled: false } as any
const gaugeSize = { width: 260, height: 260 } as any
const gaugeLabelFont = { size: 12, color: '#cfd8dc' } as any
const gaugeTickInterval = computed(() => {
  const max = Number(speedMaxUnitsHour.value || 0)
  if (max <= 0) return 10
  // 5-7 arası ana tick; yuvarla
  const raw = Math.max(1, Math.round(max / 6))
  // 5'in katına yakınla
  return raw >= 5 ? Math.round(raw / 5) * 5 : raw
})
const gaugeMinorTickInterval = computed(() => {
  const main = Number(gaugeTickInterval.value || 5)
  return Math.max(1, Math.round(main / 2))
})

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
  await detectStation()
  if (!stationId.value) {
    console.warn('İstasyon tespiti başarısız; periyodikler başlatılmadı.')
    return
  }
  computeDurumSuresi()
  fetchKpiDirect()
  kpiTimer = setInterval(fetchKpiDirect, 10000)
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
    // Vardiya + çalışma/duruş/boşta sürelerini her saniye güncelle
    computeVardiyaSureleri()
  }, 1000)

  info = setInterval(fetchWorksInfo, 2000)

  // Sayfa yüklenince iş emirlerini getir
  fetchIsEmirleri()
  // Duruş sebeplerini de ilk yüklemede al
  durusSebepleriniAl()
  // Güvenlik: 15sn içinde readiness tamamlanmazsa overlay'i kapat
  setTimeout(() => { if (!firstLoadDone.value) firstLoadDone.value = true }, 15000)
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
const calismaSure = ref('00:00')
const durusSure = ref('00:00')
const bostaSure = ref('00:00')
// KPI ham değerlerden saniye cinsinden toplamlar
const upSec = ref(0)
const downSec = ref(0)
// Vardiya başlangıç zamanı (ms)
const shiftStartMs = ref<number | null>(null)
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
// HH:MM formatı
function toHHMM(totalSeconds: number): string {
  const sec = Math.max(0, Math.floor(totalSeconds || 0))
  const h = Math.floor(sec / 3600)
  const m = Math.floor((sec % 3600) / 60)
  const pad = (n: number) => String(n).padStart(2, '0')
  return `${pad(h)}:${pad(m)}`
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

// Vardiya, çalışma, duruş ve boşta sürelerini hesapla
function computeVardiyaSureleri() {
  // Vardiya: başlangıçtan şu ana
  if (shiftStartMs.value != null) {
    const vSec = Math.max(0, Math.floor((Date.now() - shiftStartMs.value) / 1000))
    vardiyaSuresi.value = toHHMM(vSec)
    const up = Math.max(0, Math.floor(upSec.value || 0))
    const down = Math.max(0, Math.floor(downSec.value || 0))
    calismaSure.value = toHHMM(up)
    durusSure.value = toHHMM(down)
    const idle = Math.max(0, vSec - up - down)
    bostaSure.value = toHHMM(idle)
  } else {
    // Varsayılanlar
    vardiyaSuresi.value = '00:00'
    calismaSure.value = '00:00'
    durusSure.value = '00:00'
    bostaSure.value = '00:00'
  }
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
  } else if (e.key === 'F8') {
    e.preventDefault()
    yeniDurusF8()
  } else if (e.key === 'F10') {
    // Yemek molası seç
    const meal = findSebepByDescription('yemek') || findSebepByDescription('öğle') || findSebepByDescription('ogle')
    if (meal) {
      selectedSebep.value = { break_reason_code: meal.break_reason_code, description: meal.description }
      e.preventDefault()
    }
  }
}

async function yeniDurusF8() {
  if (!isDurusModu.value) return
  if (!stationId.value) return
  try {
    await axios.post('/api/uretim-rollform/close-and-open-down', { station_id: stationId.value })
    notify({ message: 'Yeni duruş başlatıldı (F8)', type: 'success', displayTime: 1500 })
    void Promise.allSettled([fetchWorksInfo(), fetchKpiDirect()])
  } catch (e) {
    console.error('F8 yeni duruş hata', e)
    notify({ message: 'Yeni duruş başlatılamadı', type: 'error', displayTime: 2000 })
  }
}

function yemekMolasiF10() {
  if (!isDurusModu.value) return
  // Yemek molasını seç
  const meal = findSebepByDescription('yemek') || findSebepByDescription('öğle') || findSebepByDescription('ogle')
  if (meal) {
    selectedSebep.value = { break_reason_code: meal.break_reason_code, description: meal.description }
  }
}

async function cayMolasiKaydet() {
  if (!isDurusModu.value || durusKayitLoading.value) return
  const tea = findSebepByDescription('çay') || findSebepByDescription('cay')
  if (tea) {
    selectedSebep.value = { break_reason_code: tea.break_reason_code, description: tea.description }
    await durusSebebiKaydet()
  } else {
    notify({ message: 'Çay molası sebebi bulunamadı', type: 'warning', displayTime: 1500 })
  }
}

async function yemekMolasiKaydet() {
  if (!isDurusModu.value || durusKayitLoading.value) return
  const meal = findSebepByDescription('yemek') || findSebepByDescription('öğle') || findSebepByDescription('ogle')
  if (meal) {
    selectedSebep.value = { break_reason_code: meal.break_reason_code, description: meal.description }
    await durusSebebiKaydet()
  } else {
    notify({ message: 'Yemek molası sebebi bulunamadı', type: 'warning', displayTime: 1500 })
  }
}

// Hurda popup state ve işlemleri
const hurdaDialog = ref(false)
const hurdaInput = ref('')
const hurdaInputRef = ref<HTMLInputElement | null>(null)
const hurdaKayitLoading = ref(false)
let hurdaEnterTimer: ReturnType<typeof setTimeout> | null = null
function onHurdaEnter() {
  if (hurdaEnterTimer) return // debounce: hızlı çift Enter engelle
  hurdaEnterTimer = setTimeout(() => { hurdaEnterTimer = null }, 300)
  // Odak kaydırarak mobil/numpad commit tetikle
  try { (hurdaInputRef.value as any)?.blur?.() } catch { /* ignore */ }
  kaydetHurda()
}

function openHurdaDialog() {
  hurdaInput.value = ''
  hurdaDialog.value = true
  nextTick(() => hurdaInputRef.value?.focus())
}

async function kaydetHurda() {
  const qty = Number(hurdaInput.value)
  if (!Number.isFinite(qty) || qty <= 0) { cancelHurda(); return }
  // Optimistik yedekler (catch içinde geri alabilmek için dış scope)
  let prevWorks: any = worksInfo.value ? { ...worksInfo.value } : null
  let activeOrderId: number | null = worksInfo.value?.worder_id ?? null
  let patchedRowIndex = -1
  let prevRowHurda: number | null = null
  try {
    if (hurdaKayitLoading.value) return
    if (!stationId.value) throw new Error('İstasyon yok')
    hurdaKayitLoading.value = true

    // Performans ölçümü
    const t0 = (typeof performance !== 'undefined' && performance.now) ? performance.now() : Date.now()

    // Optimistik UI: aktif kart ve grid hurda değerlerini anında artır
    if (prevWorks) {
      const currentScrap = Number((prevWorks as any).scrap_qty || 0)
      worksInfo.value = { ...prevWorks, scrap_qty: currentScrap + qty } as any
    }
    if (activeOrderId) {
      patchedRowIndex = isEmirleri.value.findIndex(r => r.isEmriID === activeOrderId)
      if (patchedRowIndex >= 0) {
        prevRowHurda = Number(isEmirleri.value[patchedRowIndex].hurda || 0)
        const row = { ...isEmirleri.value[patchedRowIndex], hurda: prevRowHurda + qty }
        // Reaktivite için splice ile değiştir
        isEmirleri.value.splice(patchedRowIndex, 1, row)
      }
    }

    await axios.post('/api/uretim-rollform/hurda-gir', { station_id: stationId.value, qty })

    const t1 = (typeof performance !== 'undefined' && performance.now) ? performance.now() : Date.now()
    try { console.info(`[Hurda] POST tamam: ${(t1 - t0).toFixed(0)}ms (qty=${qty})`) } catch { /* ignore */ }

    notify({ message: 'Hurda kaydedildi', type: 'success', displayTime: 1200 })
    hurdaDialog.value = false
    // Arka planda hafif tazelemeler (paralel)
    void Promise.allSettled([
      fetchWorksInfo(),
      fetchIsEmirleri(),
      fetchKpiDirect(),
    ])
  } catch (e) {
    console.error('hurda kayıt hata', e)
    // Optimistik değişiklikleri geri al
    try {
      if (activeOrderId != null) {
        const idx = isEmirleri.value.findIndex(r => r.isEmriID === activeOrderId)
        if (idx >= 0 && prevRowHurda != null) {
          const row = { ...isEmirleri.value[idx], hurda: prevRowHurda }
          isEmirleri.value.splice(idx, 1, row)
        }
      }
    } catch { /* ignore */ }
    try {
      if (prevWorks !== null) {
        worksInfo.value = prevWorks as any
      }
    } catch { /* ignore */ }
    notify({ message: 'Hurda kaydedilemedi', type: 'error', displayTime: 2000 })
  } finally {
    hurdaKayitLoading.value = false
  }
}

function cancelHurda() { hurdaInput.value = ''; hurdaDialog.value = false }
function handleHurdaShortcut(e: KeyboardEvent) { if (e.key === 'F8') { openHurdaDialog(); e.preventDefault() } }

// Durum bilgileri
const durusSebebi = ref('uretim-disi')
// Yeni KPI alanları
const kpiLoaded = ref(false)
// İlk yükleme overlay state & readiness bayrakları
const firstLoadDone = ref(false)
const stationReady = ref(false)
const worksInfoReady = ref(false)
const kpiReady = ref(false)
const ordersReady = ref(false)
const reasonsReady = ref(false)
const allReady = computed(() => stationReady.value && worksInfoReady.value && kpiReady.value && ordersReady.value && reasonsReady.value)
watch(allReady, (v) => { if (v && !firstLoadDone.value) firstLoadDone.value = true })
const initialLoading = computed(() => !firstLoadDone.value)
const kpi = ref({ availability: 0, performance: 0, quality: 0, oee: 0 })
const shiftInfo = ref<{ id: string; start: string; end: string } | null>(null)

async function fetchKpiDirect() {
  try {
    if (!stationId.value) return
    // console.log('KPI fetch', { station_id: stationId.value })
    const { data } = await axios.get('/api/uretim-rollform/kpi', { params: { station_id: stationId.value } })
    if (data?.kpi) {
      const pct = (v: any) => Math.round(Number(v || 0) * 100)
      kpi.value = {
        availability: pct(data.kpi.availability),
        performance: pct(data.kpi.performance),
        quality: pct(data.kpi.quality),
        oee: pct(data.kpi.oee),
      }
      // Vardiya penceresi
      if (data.shift) {
        // Ekran için HH:MM gösterimi
        shiftInfo.value = {
          id: data.shift.id,
          start: String(data.shift.start || '').substring(11, 16),
          end: String(data.shift.end || '').substring(11, 16)
        }
        // Hesaplama için başlangıç zamanı (ms)
        const ms = Date.parse(data.shift.start)
        if (!Number.isNaN(ms)) shiftStartMs.value = ms
      }
      // Ham süreler (saniye): çalışma ve duruş
      if (data.raw) {
        upSec.value = Math.max(0, Math.floor(Number(data.raw.upSeconds || 0)))
        downSec.value = Math.max(0, Math.floor(Number(data.raw.downSeconds || 0)))
      }
      // İlk çekimde anında hesapla
      computeVardiyaSureleri()
      kpiLoaded.value = true
      kpiReady.value = true
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

// Üretim Girişi dialog state ve aksiyonları
const uretimiKapatDialog = ref(false)
const manuelUretimMiktari = ref<number | null>(null)
const uretimInputRef = ref<HTMLInputElement | null>(null)
const uretimKayitLoading = ref(false)
function openUretimGirisiDialog() {
  manuelUretimMiktari.value = null
  uretimiKapatDialog.value = true
  nextTick(() => uretimInputRef.value?.focus?.())
}
function closeUretimGirisiDialog() {
  uretimiKapatDialog.value = false
}
async function saveUretimGirisi() {
  if (uretimKayitLoading.value) return
  if (manuelUretimMiktari.value == null) return
  try {
    uretimKayitLoading.value = true
    // Şimdilik simüle: backend entegrasyonu geldiğinde buraya axios.post eklenecek
    await new Promise(res => setTimeout(res, 800))
    notify({ message: 'Üretim girişi kaydedildi (örnek).', type: 'success', displayTime: 1500 })
    uretimiKapatDialog.value = false
  } catch (e) {
    console.error('Üretim girişi hata', e)
    notify({ message: 'Üretim girişi kaydedilemedi', type: 'error', displayTime: 1800 })
  } finally {
    uretimKayitLoading.value = false
  }
}

async function confirmActivate() {
  if (!activateRow.value) return
  const length = Number(activateLength.value ?? 0)
  const stationIdValue = stationId.value
  if (!stationIdValue) return
  const payload = {
    wstation_id: stationIdValue,
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
// Otomatik kaydetme kaldırıldığı için zamanlayıcıya ihtiyaç yok

async function fetchWorksInfo() {
  try {
    if (!stationId.value) { worksInfo.value = null; return }
    const res = await axios.get('/api/kapasite-works-info', { params: { station_id: stationId.value } })
    const arr = Array.isArray(res.data) ? res.data : []
    worksInfo.value = arr.length > 0 ? arr[0] : null
    computeDurumSuresi()
  } catch (e) {
    console.error('WorksInfo alınamadı', e)
    worksInfo.value = null
  } finally { worksInfoReady.value = true }
}

function openTechnicalDrawing() {
  const code = activateRow.value?.stokKodu || worksInfo.value?.item_code || null
  if (!code) {
    alert('Önce bir iş emri/ürün seçiniz (stok kodu yok).')
    return
  }
  const url = `/teknik-resim?code=${encodeURIComponent(code)}`
  window.open(url, '_blank')
}

async function fetchIsEmirleri() {
  try {
    if (!stationId.value) { isEmirleri.value = []; return }
    const { data } = await axios.get('/api/uretim-rollform/is-emirleri', { params: { istasyon: stationId.value } })
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
  } finally { ordersReady.value = true }
}

// --- Duruş Sebepleri Akışı (ProductionCard'dan port) ---
const popupDurusSecGosterVisible = ref(false)
const durusKayitLoading = ref(false)
const allowPopupClose = ref(true)
// Select odak için ref
const durusSelectRef = ref<any>(null)
function focusDurusSelect(attempt = 0) {
  try {
    const root = durusSelectRef.value?.$el || durusSelectRef.value?.$refs?.root || null
    if (root) {
      // Vuetify v-select input alanı
      const input: HTMLElement | null = root.querySelector('input') || root.querySelector('[role="combobox"]')
      if (input) {
        input.focus()
        if (document.activeElement !== input && attempt < 3) {
          // Çok hızlı render sebebiyle odak kaydı; tekrar dene
          setTimeout(() => focusDurusSelect(attempt + 1), 40)
        }
        return
      }
    }
    if (attempt < 5) setTimeout(() => focusDurusSelect(attempt + 1), 50)
  } catch { /* ignore */ }
}
function onDurusPopupShown() {
  // Popup animasyonundan hemen sonra güvenilir şekilde odakla
  setTimeout(() => focusDurusSelect(), 10)
}
// Sayfa yenilemesi ile gelen ilk statu değerlendirmesini ayırt etmek için
const firstStatuEvaluation = ref(true)

const kaydetOptions = computed(() => ({
  width: 110,
  marginBottom: 30,
  type: 'success',
  text: durusKayitLoading.value ? 'Kaydediliyor...' : 'Kaydet',
  stylingMode: 'contained',
  elementAttr: { class: durusKayitLoading.value ? 'btn-loading' : '' },
  disabled: durusKayitLoading.value,
  onClick: () => { if (!durusKayitLoading.value) durusSebebiKaydet() },
}))

function durusSebepleriniAl() {
  if (!stationId.value) return
  axios.get('/api/durus-sebepleri-al', { params: { istasyon: stationId.value } })
    .then(r => { durusSebepleri.value = r.data?.durusSebepleri || [] })
    .catch(err => { console.error('Duruş sebepleri alınamadı', err) })
    .finally(() => { reasonsReady.value = true })
}

function durusSebebiGir() {
  // Varsayılan placeholder
  selectedSebep.value = { break_reason_code: '000', description: 'GİRİLMEDİ' }
  popupDurusSecGosterVisible.value = true
  // Render sonrası select'e focus
  nextTick(() => focusDurusSelect())
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
  if (!stationId.value) {
    notify({ message: 'İstasyon bulunamadı.', type: 'error', displayTime: 3000 })
    return
  }
  try {
    durusKayitLoading.value = true
    allowPopupClose.value = false
    const t0 = (typeof performance !== 'undefined' && performance.now) ? performance.now() : Date.now()
    await axios.post('/api/duruskaydet-mekanik', {
      istasyonID: stationId.value,
      selectedDurus: selectedSebep.value,
    })
    const t1 = (typeof performance !== 'undefined' && performance.now) ? performance.now() : Date.now()
    try { console.info(`[Duruş] POST tamam: ${(t1 - t0).toFixed(0)}ms (code=${selectedSebep.value.break_reason_code})`) } catch { /* ignore */ }
    notify({ message: 'Duruş kaydedildi.', type: 'success', displayTime: 1600 })
    // Persist edileni güncelle
    try { localStorage.setItem(reasonStorageKey(), JSON.stringify(selectedSebep.value)) } catch { /* ignore */ }
    // Hızlı algı için popup'ı hemen kapat
    popupDurusSecGosterVisible.value = false
    // Arka planda hafif tazelemeler (paralel)
    void Promise.allSettled([
      fetchWorksInfo(),
      fetchKpiDirect(),
    ])
  } catch (err) {
    console.error('Duruş kaydedilemedi', err)
    notify({ message: 'Duruş kaydedilemedi.', type: 'error', displayTime: 3000 })
  } finally {
    durusKayitLoading.value = false
    allowPopupClose.value = true
    // popupDurusSecGosterVisible flag'ini catch içinde açık bırakıyoruz ki kullanıcı tekrar deneyebilsin
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
    // statu 1 dışına çıktı -> popup varsa hemen kapat (kaydetmeye çalışmadan)
    if (popupDurusSecGosterVisible.value) {
      // Kaydetme UI'si donmasın diye önce loading reset
      durusKayitLoading.value = false
      allowPopupClose.value = true
      popupDurusSecGosterVisible.value = false
    }
    selectedSebep.value = null
  }
  lastStatuId.value = nv ?? null
  if (firstStatuEvaluation.value) firstStatuEvaluation.value = false
})

// Otomatik kaydetme kaldırıldı: Duruş sebebi yalnızca Enter veya Kaydet butonu ile kaydedilecek

// İstasyon tespiti
const stationId = ref<number | null>(null)
const stationDetectError = ref<string | null>(null)

async function detectStation() {
  try {
    stationDetectError.value = null
    const { data } = await axios.get('/api/uretim-rollform/detect-station')
    if (data && data.station_id) {
      stationId.value = Number(data.station_id)
      stationReady.value = true
      // Operatör atanması: istasyon bulunduysa ve kullanıcı oturum açıksa
      try {
        const opId = Number(userData?.value?.id || userData?.value?.user?.id)
        if (Number.isFinite(opId) && opId > 0) {
          await axios.post('/api/uretim-rollform/set-operator', { station_id: stationId.value, operator_id: opId })
        }
      } catch (e) { /* sessiz */ }
    } else {
      stationId.value = null
      const detected = data?.ip ? ` – tespit edilen IP: ${data.ip}` : ''
      stationDetectError.value = 'İstasyon bulunamadı (IP eşleşmedi)' + detected
    }
  } catch (e: any) {
    stationDetectError.value = 'İstasyon tespiti hata: ' + (e?.message || 'bilinmiyor')
  }
  // console.log('detectStation', { stationId: stationId.value, error: stationDetectError.value }) 
}

// fetchWorksInfo, fetchKpiDirect, fetchIsEmirleri ve hurda/duruş kaydetme fonksiyonlarında userData.value?.istasyon_id yerine stationId kullanacağız.
// (Yinelenen fonksiyon ve timer tanımları kaldırıldı)

// Mount sırasını güncelle: önce istasyon tespit, sonra periyodikler
// (ikinci onMounted/onUnmounted bloğu kaldırıldı; yukarıdaki tekil mount akışı kullanılır)
</script>

<style scoped>
/* stylelint-disable order/properties-order */
.page-rollform {
  display: flex;
  flex-direction: column;
  gap: 5px;
  padding-block: 10px;
  padding-inline: 10px;
}

.panel-title {
  display: flex;
  align-items: center;
  justify-content: center;

  /* color: #c8cbd6; */
  font-weight: bold;
  margin-block-end: 8px;
}

.durum-title {
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px 10px 0 0;
  border-style: 3px yellow solid;
  background-color: #4b7027;
  font-weight: bold;
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
  font-weight: bold;
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
  font-weight: bold;

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
  margin-block-start: 15px;
}

.digital-clock {
  font-family: "Segoe UI", Verdana, "Helvetica Neue", Arial, sans-serif;
  font-size: 22px;
  font-weight: bold;
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
  font-weight: bold;
  border: 1px solid;
  border-radius: 8px;
}

.kpi-oee {
  /* background-color: #2a3142; */
  font-family: "Segoe UI", Verdana, "Helvetica Neue", Arial, sans-serif;
  font-size: 44px;
  font-weight: bold;
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
  font-weight: bold;
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
  font-weight: bold;
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

.bilgi {
  /* background-color: #2a3142; */
  color: #5993b5;
  font-size: 24px;
  font-weight: bold;
  text-align-last: center;
  border-radius: 10px;
  border: rgb(131, 114, 23) solid 1px;
}

.bilgi-urun-adi {
  /* background-color: #2a3142; */
  color: #5993b5;
  font-size: 24px;
  font-weight: bold;
  text-align-last: left;
  border-radius: 10px;
  border: rgb(131, 114, 23) solid 1px;
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
  font-weight: bold;
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
  font-weight: bold;
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
  font-weight: bold;
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
  font-weight: bold;
  text-align-last: left;
  block-size: 30px;

  /* border-bottom: 2px solid hsl(0, 38%, 88%); */
}

.info-urun-adi {
  /* background-color: #2a3142; */
  font-size: 16px;
  font-weight: bold;
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
  font-weight: bold;
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
  margin-block: 10px 0;
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
  font-weight: bold;
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

/* İlk yükleme overlay (Rollform) */
.rf-loading-overlay {
  position: fixed;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: radial-gradient(circle at center, rgba(0 0 0 / 55%), rgba(0 0 0 / 85%));
  backdrop-filter: blur(2px);
  z-index: 6000;
  padding-block: 20px;
  padding-inline: 20px;
  text-align: center;
}

.rf-loader-box {
  max-inline-size: 560px;
  display: flex;
  flex-direction: column;
  gap: 18px;
  background: rgba(30 30 30 / 75%);
  border: 1px solid #3a3a3a;
  border-radius: 22px;
  padding-block: 46px 52px;
  padding-inline: 52px;
  box-shadow: 0 18px 48px rgba(0 0 0 / 55%), 0 4px 16px rgba(0 0 0 / 40%);
  animation: rf-scale-in 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

.rf-loader-box h2 {
  margin: 0;
  font-size: 26px;
  letter-spacing: 0.5px;
  font-weight: 600;
  background: linear-gradient(90deg, #60a5fa, #38bdf8);
  background-clip: text;
  color: transparent;
}

.rf-loader-box p {
  margin: 0;
  font-size: 15px;
  line-height: 1.5;
  color: #d1d5db;
}

.rf-spinner {
  inline-size: 78px;
  block-size: 78px;
  border-radius: 50%;
  border: 7px solid rgba(255 255 255 / 15%);
  border-inline-start-color: #3b82f6;
  border-block-end-color: #0ea5e9;
  animation: rf-spin 1s linear infinite, rf-glow 2.2s ease-in-out infinite;
  margin-block: 0 6px;
  margin-inline: auto;
  position: relative;
}

.rf-spinner::after {
  content: "";
  position: absolute;
  inset: 7px;
  border-radius: inherit;
  border: 4px solid rgba(255 255 255 / 10%);
  border-block-start-color: rgba(255 255 255 / 35%);
  animation: rf-spin 2.2s linear infinite reverse;
}

.hint-line {
  color: #9ca3af;
  font-size: 16px;
}

.warn-line {
  color: #fbbf24;
  font-size: 12px;
  font-weight: 600;
}

@keyframes rf-spin {
  to {
    transform: rotate(360deg);
  }
}

@keyframes rf-glow {
  0%,
  100% {
    box-shadow: 0 0 0 0 rgba(59 130 246 / 35%);
  }

  50% {
    box-shadow: 0 0 0 14px rgba(59 130 246 / 0%);
  }
}

@keyframes rf-scale-in {
  0% {
    transform: scale(0.9) translateY(8px);
    opacity: 0;
  }

  100% {
    transform: scale(1) translateY(0);
    opacity: 1;
  }
}
</style>

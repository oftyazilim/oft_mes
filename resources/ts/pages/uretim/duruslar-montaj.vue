<template>
  <!-- İlk yükleme overlay -->
  <div v-if="!firstLoadDone" class="montaj-loading-overlay">
    <div class="montaj-loading-content">
      <div class="montaj-spinner" aria-label="Yükleniyor" />
      <h2 class="montaj-loading-title">Montaj Duruşları Yükleniyor</h2>
      <p class="montaj-loading-hint">
        Kritik veriler alınıyor: <strong>{{ readinessProgress }}</strong>
      </p>
      <p class="montaj-loading-sub">
        İlk listeleme tamamlandığında ekran otomatik açılacak.
      </p>
      <p class="montaj-loading-timeout" v-if="safetyTimeoutHit">
        <span class="warn">Beklenen bazı veriler gecikti, yine de gösteriliyor...</span>
      </p>
    </div>
  </div>

  <VCard class="mt-0 pa-0 pt-2">
    <VCardText class="mt-0 pa-0 ms-2 me-1">
      <VCol cols="12" class="mt-0 pa-1 pe-2">
        <div id="liste" style="margin-block-end: -10px;">
          <!-- <DxContextMenu :data-source="menuItems" :width="200" target="#grid" @item-click="itemClick" /> -->

          <DxDataGrid id="grid" ref="dataGridRef" :key="gridKey" :data-source="gridData" key-expr="satir_id"
            :show-borders="true" :focused-row-enabled="true" :row-alternation-enabled="true" :min-width="200"
            @exporting="onExporting" :allow-column-reordering="true" :column-auto-width="false"
            @content-ready="onContentReady" @focused-row-changed="onFocusedRowChanged" :allow-column-resizing="true"
            column-resizing-mode="widget" @cell-prepared="onCellPrepared" @selection-changed="onSelectionChanged"
            :auto-navigate-to-focused-row="true" @row-prepared="onRowPrepared" :on-option-changed="handleOptionChanged"
            @contextMenuPreparing="onContextMenuPreparing" v-model:focused-row-key="focusedRowKey"
            :selected-rows-keys="selectedRows">

            <!-- <DxColumn type="selection" :fixed="true" fixedPosition="left" /> -->
 <DxColumn data-field="id" caption="ID" :visible="false" :min-width="90"/>
            <DxColumn data-field="hafta" caption="HAFTA" :fixed="true" :width="120" :visible="true" alignment="left"
                      :cell-template="weekCellTemplate"/>
            <DxColumn data-field="IS_ISTASYONU" caption="İST. ADI" :visible="true" :width="130"/>
            <DxColumn data-field="IS_ISTASYONU_KODU" caption="İSTASYON KODU" :visible="false" :width="150"/>
            <DxColumn data-field="IS_ISTASYONU_ADI" caption="İSTASYON ADIx" :visible="false" :width="150"/>
            <DxColumn data-field="OPERASYON" caption="OPRSYN" :width="120"/>
            <DxColumn data-field="siparis_belge_no" caption="SİPARİŞ NO" :width="90" :visible="true"
                      :allow-sorting="false"/>
            <DxColumn data-field="cari_ad" caption="MÜŞTERİ" :visible="true" :min-width="140" :allow-sorting="false"/>
            <DxColumn data-field="stok_kodu" caption="STOK KODU" :visible="true" :width="120" :allow-sorting="false"/>
            <DxColumn data-field="stok_adi" caption="STOK ADI" min-width="200" :allow-sorting="false"/>
            <DxColumn data-field="isemri_id" caption="İŞ EMRİ ID" :width="150" :visible="false"
                      :allow-sorting="false"/>
            <DxColumn data-field="isemri_no" caption="İŞ EMRİ NO" :width="120" :allow-sorting="false"/>
            <DxColumn data-field="teslim_tarihi" caption="TESLİM TARİHİ" data-type="date" :width="140"
                      :visible="operasyon" :format="{
                formatter: (date) => {
                  const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                  }).format(new Date(date));
                  return formattedDate.replace(/\//g, '.');
                },
              }" :cell-template="getIconType" :allow-sorting="false"/>
            <DxColumn data-field="planlanan_baslangic" caption="PLN BŞL" data-type="date" :width="130" :visible="true"
                      :format="{
                formatter: (date) => {
                  const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                  }).format(new Date(date));

                  return formattedDate.replace(/\//g, '.');
                },
              }"/>
            <DxColumn data-field="planlanan_bitis_tarihi" caption="PLN BTŞ" data-type="date" :width="110"
                      :visible="true" :format="{
                formatter: (date) => {
                  const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                  }).format(new Date(date));

                  return formattedDate.replace(/\//g, '.');
                },
              }" alignment="left"/>
            <DxColumn data-field="kalan_miktar" caption="KALAN MİKTAR" data-type="number" :width="90" :visible="true"
            :allow-sorting="false" />

            <DxColumn data-field="surec" caption="SÜREÇ" data-type="number" :width="150" :visible="true"
                      cell-template="surecCellTemplate" alignment="center" :allow-sorting="false"/>
            <DxColumn data-field="siparis_miktari" caption="SİPARİŞ MİKTARI" data-type="number" :width="60"
                      :visible="true" :allow-sorting="false"/>
            <DxColumn data-field="isemri_miktari" caption="İŞ EMRİ MİKTARI" data-type="number" :width="60"
                      :visible="true"/>
            <DxColumn data-field="uretilen_toplam_miktar" caption="TOPLAM URETİLEN" data-type="number" :width="60"
                      :visible="false" :allow-sorting="false"/>
            <DxColumn data-field="uretilen_net_miktar" caption="NET URETILEN" data-type="number" :width="60"
                      :visible="true"/>
            <DxColumn data-field="toplam_hurda_miktari" caption="HURDA MİKTARI" data-type="number" :width="60"
                      :visible="true" :allow-sorting="false"/>
            <DxColumn data-field="operasyon_hazirlik_suresi" caption="HAZIRLIK SÜRESİ" data-type="number" :width="60"
                      :visible="true" :format="{
                type: 'fixedPoint',
                precision: 1,
                thousandsSeparator: ',',
              }"/>
            <DxColumn data-field="operasyon_suresi" caption="OPERASYON SÜRESİ" data-type="number" :width="60"
                      :visible="true" :format="{
                type: 'fixedPoint',
                precision: 1,
                thousandsSeparator: ',',
              }" :allow-sorting="false"/>
            <!-- <DxColumn data-field="sip_detay_id" :min-width="120" :width="140" :allow-sorting="false" /> -->


            <DxGridLoadPanel :enabled="firstLoadDone" :text="loadingMessage || 'Yükleniyor...'" :show-indicator="true"
              :show-pane="true" :shading="true" />
            <DxSelection mode="multiple" select-all-mode="page" show-check-boxes-mode="onClick" />
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
              <!-- <DxItem location="before" locate-in-menu="auto" template="groupingTemplate" /> -->
              <DxItem location="before" locate-in-menu="auto" template="collapseTemplate" />
              <DxItem location="before" template="totalCountTemplate" />
              <DxItem location="before" template="totalRecordTemplate" />
              <DxItem location="before" template="gruplaTemplate" :visible="grupVisible && canManagePlanlama"
                @click="grup" />
              <DxItem location="before" template="baslangicTarihiTemplate" :visible="grupVisible && canManagePlanlama"
                @click="TarihGoster" />
              <DxItem location="before" locate-in-menu="auto" template="aksesuarliTemplate"
                :visible="grupVisible && canManagePlanlama" @click="AksesuarGoster" />
              <DxItem location="after" locate-in-menu="auto" template="yenileTemplate"
                menu-item-template="menuYenileTemplate" @click="Yenile" />
              <DxItem location="after" locate-in-menu="auto" template="filtreTemizleTemplate"
                menu-item-template="menuFiltreTemizleTemplate" @click="FiltreTemizle" />
              <DxItem location="after" locate-in-menu="auto" template="filtreGosterTemplate"
                menu-item-template="menuFiltreGosterTemplate" @click="toggleGoster" />
              <DxItem name="exportButton" />
              <DxItem name="columnChooserButton" />
              <DxItem name="searchPanel" />
            </DxToolbar>

            <DxSummary>
              <DxGroupItem :align-by-column="true" column="isemri_no" summary-type="count" display-format="{0} iş emri"
                :alignment="'right'" />
              <DxGroupItem :align-by-column="true" column="kalan_miktar" summary-type="sum" :alignment="'right'"
                :customize-text="formatSummaryText" />
              <DxGroupItem :align-by-column="true" column="siparis_miktari" summary-type="sum" :alignment="'right'"
                :customize-text="formatSummaryText" />
              <DxGroupItem :align-by-column="true" column="kalan_siparis_miktari" summary-type="sum"
                :alignment="'right'" :customize-text="formatSummaryText" />
              <DxGroupItem :align-by-column="true" column="isemri_miktari" summary-type="sum" :alignment="'right'"
                :customize-text="formatSummaryText" />
              <DxGroupItem :align-by-column="true" column="operasyon_hazirlik_suresi" summary-type="sum"
                :alignment="'right'" :customize-text="formatSummaryText" />
              <DxGroupItem :align-by-column="true" column="operasyon_suresi" summary-type="sum" :alignment="'right'"
                :customize-text="formatSummaryText" />

              <DxTotalItem :align-by-column="true" column="isemri_no" summary-type="count" display-format="{0} iş emri"
                :alignment="'right'" />
              <DxTotalItem :align-by-column="true" column="kalan_miktar" summary-type="sum" :alignment="'right'"
                :customize-text="formatSummaryText" />
              <DxTotalItem :align-by-column="true" column="siparis_miktari" summary-type="sum" :alignment="'right'"
                :customize-text="formatSummaryText" />
              <DxTotalItem :align-by-column="true" column="kalan_siparis_miktari" summary-type="sum"
                :alignment="'right'" :customize-text="formatSummaryText" />
              <DxTotalItem :align-by-column="true" column="isemri_miktari" summary-type="sum" :alignment="'right'"
                :customize-text="formatSummaryText" />
              <DxTotalItem :align-by-column="true" column="operasyon_hazirlik_suresi" summary-type="sum"
                :alignment="'right'" :customize-text="formatSummaryText" />
              <DxTotalItem :align-by-column="true" column="operasyon_suresi" summary-type="sum" :alignment="'right'"
                :customize-text="formatSummaryText" />
            </DxSummary>


            <template #aksesuarliTemplate>
              <DxButton icon="gift" styling-mode="text" hint="Aksesuar Ekle/Kaldır" />
            </template>

            <template #baslangicTarihiTemplate>
              <DxButton icon="clock" styling-mode="text" hint="Başlangıç Tarihini Değiştir" />
            </template>

            <template #gruplaTemplate>
              <DxButton icon="hierarchy" styling-mode="text" hint="Haftayı Grupla" />
            </template>

            <template #totalCountTemplate>
              <div class="informer">
                <div class="count">{{ totalGroupCount }}</div>
                <span>Toplam Grup</span>
              </div>
            </template>

            <template #totalRecordTemplate>
              <div class="informer">
                <div class="count">{{ totalRecord }}</div>
                <span>Toplam Kayıt</span>
              </div>
            </template>

            <template #filtreTemizleTemplate>
              <DxButton icon="notequal" styling-mode="text" hint="Filtre Temizle" id="filtretemizle" />
              <!-- <VIcon size="24" icon="tabler-x" /> -->
            </template>
            <template #menuFiltreTemizleTemplate>
              <div style="display: flex; align-items: center;">
                <i class="dx-icon dx-icon-undo"></i>
                <span style="margin-inline-start: 8px;">Filtre Temizle</span>
              </div>
            </template>

            <template #yenileTemplate>
              <DxButton icon="refresh" styling-mode="text" hint="Yenile" id="sayim" />
            </template>
            <template #menuYenileTemplate>
              <div style="display: flex; align-items: center;">
                <i class="dx-icon dx-icon-refresh"></i>
                <span style="margin-inline-start: 8px;">Yenile</span>
              </div>
            </template>

            <template #filtreGosterTemplate>
              <DxButton icon="filter" styling-mode="text" hint="Filtre Goster" />
            </template>
            <template #menuFiltreGosterTemplate>
              <div style="display: flex; align-items: center;">
                <i class="dx-icon dx-icon-filter"></i>
                <span style="margin-inline-start: 8px;">Filtre Göster</span>
              </div>
            </template>

            <template #collapseTemplate>
              <DxButton :icon="expandAll ? 'collapse' : 'expand'" hint="expandAll ? 'Daralt' : 'Genişlet'" width="40"
                height="40" styling-mode="text" @click="toggleExpandAll" />
            </template>

            <template #surecCellTemplate="{ data: cellData }">
              <VProgressLinear v-model="cellData.value" height="20" color="warning">
                <span>{{ Math.ceil(cellData.value) }}%</span>
              </VProgressLinear>
              <!-- <SurecCell :cell-data="cellData" :max-deger="100" /> -->
            </template>

            <template #notlarPTemplate="{ data }">
              <template v-if="data.value === '1' || data.value === 1">
                <i :class="['dx-icon', 'dx-icon-belloutline']" :style="{ fontSize: '20px', color: 'green' }"></i>
              </template>
            </template>
            <template #notlarOTemplate="{ data }">
              <template v-if="data.value === '1' || data.value === 1">
                <i :class="['dx-icon', 'dx-icon-belloutline']"
                  :style="{ fontSize: '20px', color: staticPrimaryColor }"></i>
              </template>
            </template>
            <template #eksiklerTemplate="{ data }">
              <template v-if="data.value === '1' || data.value === 1">
                <i :class="['dx-icon', 'dx-icon-warning']" :style="{ fontSize: '20px', color: 'red' }"></i>
              </template>
            </template>
            <template #aksesuarTemplate="{ data }">
              <template v-if="data.value === 'Aksesuarlı'">
                <i :class="['dx-icon', 'dx-icon-gift']" :style="{ fontSize: '20px', color: staticPrimaryColor }"></i>
              </template>
            </template>
            <template #aktifTemplate="{ data }">
              <template v-if="data.value === 1">
                <i :class="['dx-icon', 'dx-icon-runner']" :style="{ fontSize: '20px', color: staticPrimaryColor }"></i>
              </template>
            </template>
          </DxDataGrid>
          <DxLoadPanel v-model:visible="loadingVisible" :show-indicator="true" :show-pane="true" :shading="true"
            :message="loadingMessage || 'Yükleniyor...'" :position="position" />
        </div>
      </VCol>
    </VCardText>
  </VCard>

</template>


<script setup lang="ts">

import { useAbility } from "@casl/vue";
import { computed, nextTick, onMounted, ref, watch } from "vue";

// import { DxTooltip } from 'devextreme-vue/tooltip';
import { usePageTitleStore } from '@/stores/pageTitle';
import axios from "axios";
import { DxButton } from 'devextreme-vue/button';
import DxCalendar from 'devextreme-vue/calendar';
import DxSelectBox from 'devextreme-vue/cjs/select-box';
import type { DxContextMenuTypes } from 'devextreme-vue/context-menu';
import DxContextMenu from 'devextreme-vue/context-menu';
import type { DxDataGridTypes } from 'devextreme-vue/data-grid';
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
  DxTotalItem
} from 'devextreme-vue/data-grid';
import { DxLoadPanel } from 'devextreme-vue/load-panel';
import { DxNumberBox } from 'devextreme-vue/number-box';
import { DxPopup, DxToolbarItem } from 'devextreme-vue/popup';
import { DxSwitch } from 'devextreme-vue/switch';
import DxTextArea from 'devextreme-vue/text-area';
import DxTextBox from 'devextreme-vue/text-box';
import { DxTooltip } from 'devextreme-vue/tooltip';
import query from 'devextreme/data/query';
import { exportDataGrid } from 'devextreme/excel_exporter';
import notify from 'devextreme/ui/notify';
import { Workbook } from 'exceljs';
import { saveAs } from 'file-saver';
import SurecCell from './SurecCell.vue';

const pageTitleStore = usePageTitleStore()
const pageName = 'Duruşlar'
const pageAlias = 'Montaj'

pageTitleStore.setTitle(`${pageName} (${pageAlias})`)
document.title = `OFT - ${pageName} | ${pageAlias}`

definePage({
  meta: { action: ['read'], subject: ['planlama'] }
})

function formatliNumber(value: number): string {
  return new Intl.NumberFormat("tr-TR", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
    useGrouping: true,
  }).format(value);
}
function formatDate(dateString: string): string {
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return "-"; // Geçersiz tarih kontrolü
  return date.toLocaleDateString("tr-TR", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  });
}
function formatSummaryText(itemInfo: { value: string | number | Date; valueText: string }): string {
  // Only format if value is a number, otherwise return valueText as is
  if (typeof itemInfo.value === 'number') {
    return new Intl.NumberFormat('tr-TR', { maximumFractionDigits: 0 }).format(itemInfo.value);
  }
  return itemInfo.valueText;
}
function truncateText(text: string, maxLength: number): string {
  return text.length > maxLength ? text.substring(0, maxLength) : text;
}



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
  background: radial-gradient(circle at 35% 30%, rgba(90 130 200 / 12%), rgba(10 15 25 / 92%));
  inset: 0;
}

.montaj-loading-content {
  border: 1px solid rgba(255 255 255 / 8%);
  border-radius: 18px;
  animation: montaj-scale-in 0.5s cubic-bezier(0.4, 0.14, 0.3, 1.2);
  background: rgba(20 28 40 / 70%);
  box-shadow: 0 8px 32px -4px rgba(0 0 0 / 55%), 0 0 0 1px rgba(255 255 255 / 6%) inset;
  max-inline-size: 520px;
  padding-block: 32px 40px;
  padding-inline: 36px;
  text-align: center;
}

.montaj-spinner {
  position: relative;
  border: 5px solid rgba(255 255 255 / 12%);
  border-radius: 50%;
  animation: montaj-spin 1.05s linear infinite, montaj-pulse 2.2s ease-in-out infinite;
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

.custom-col {
  flex: 0 0 12.5%;

  /* DevExtreme LoadPanel message visibility tweaks */
  .dx-loadpanel-content .dx-loadpanel-message {
    color: #fff !important;
    font-weight: 600;
  }

  .dx-loadpanel-content {
    background-color: rgba(0, 0, 0, 65%) !important;
  }

  /* 1.5 sütun genişliği */
  max-inline-size: 12.5%;
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


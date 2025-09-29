<template>
  <VCard class="mt-0 pa-0">
    <VCardText class="mt-0 pa-2 ">
      <VCol cols="12" class="mt-2 pa-1 pe-2 pt-0">
        <div id="liste" style="margin-block: -10px 10px;">
          <DxContextMenu :data-source="menuItems" :width="200" target="#gridM" @item-click="itemClick" />

          <DxDataGrid id="gridM" ref="dataGridRefM" :key="gridKeyM" class="grid" :data-source="gridDataM"
            key-expr="item_id" :show-borders="true" focused-row-enabled="true" row-alternation-enabled="true"
            :min-width="200" allow-column-reordering="true" column-auto-width="true" allow-column-resizing="true"
            @contextMenuPreparing="onContextMenuPreparing" column-resizing-mode="widget"
            :auto-navigate-to-focused-row="true" width="100%" @cell-prepared="onCellPreparedM" @exporting="onExportingM"
            @cell-click="onCellClick">
            <DxColumn data-field="tipi" caption="TİPİ" data-type="string" :visible="true" :width="65"
              :cell-template="tipCellTemplate" />
            <DxColumn data-field="satinalmapersoneli" caption="SATICI" :visible="true" :width="80" />
            <DxColumn data-field="mrk_adi" caption="İŞ MERKEZİ" :visible="true" :width="150" />
            <DxColumn data-field="item_id" caption="STOK ID" data-type="number" :visible="true" :width="80" />
            <DxColumn data-field="stok_kodu" caption="STOK KODU" data-type="string" :visible="true" :width="130" />
            <DxColumn data-field="stok_adi" caption="STOK ADI" data-type="string" :visible="true" :width="400" />
            <DxColumn data-field="isemri_miktari" caption="İŞ EMRİ MİKTARI" data-type="number" :visible="true"
              :width="100" :format="{ type: 'fixedPoint', precision: 0, thousandsSeparator: ',', }" />
            <DxColumn data-field="kalan" caption="KALAN" data-type="number" :visible="true" :width="100"
              :format="{ type: 'fixedPoint', precision: 0, thousandsSeparator: ',', }" />
            <DxColumn data-field="ihtiyac" caption="İHTİYAÇ" data-type="number" :visible="true" :width="100"
              :format="{ type: 'fixedPoint', precision: 0, thousandsSeparator: ',', }" />
            <DxColumn data-field="stok" caption="STOK" data-type="number" :visible="bothSelected" :width="100"
              :format="{ type: 'fixedPoint', precision: 0, thousandsSeparator: ',', }" />
            <DxColumn data-field="ana_depo" caption="ANA DEPO" data-type="number" :visible="bothSelected" :width="100"
              :format="{ type: 'fixedPoint', precision: 0, thousandsSeparator: ',', }" />
            <DxColumn data-field="depo_ihtiyaci" caption="DEPO İHTİYACI" data-type="number" :visible="bothSelected"
              :width="100" :format="{ type: 'fixedPoint', precision: 0, thousandsSeparator: ',', }" />
            <DxColumn data-field="diger_depo" :caption="digerDepoCaption" data-type="number" :visible="true"
              :width="100" :calculate-cell-value="calculateDigerDepoCellValue"
              :format="{ type: 'fixedPoint', precision: 0, thousandsSeparator: ',', }" />
            <DxColumn data-field="bakiye" caption="BAKİYE" data-type="number" :visible="true" :width="100"
              :calculate-cell-value="calculateBakiyeCellValue" :format="{
                type: 'fixedPoint',
                precision: 0,
                thousandsSeparator: ',',
              }" sort-order="asc" />
            <DxColumn data-field="satinalma" caption="SATINALMA" data-type="number" :visible="true" :width="100"
              :format="{
                type: 'fixedPoint',
                precision: 0,
                thousandsSeparator: ',',
              }" />
            <DxColumn data-field="talepler" caption="TALEPLER" data-type="number" :visible="true" :width="100" :format="{
              type: 'fixedPoint',
              precision: 0,
              thousandsSeparator: ',',
            }" />
            <DxColumn data-field="dongu" caption="KAÇ İŞEMRİNDE" data-type="number" :visible="true" :width="100"
              :format="{
                type: 'fixedPoint',
                precision: 0,
                thousandsSeparator: ',',
              }" />

            <DxLoadPanel :key="loadingVisible" v-model:visible="loadingVisible" :show-indicator="true" :show-pane="true"
              :shading="true" />
            <DxHeaderFilter :visible="true" />
            <DxFilterPanel :visible="true" />
            <DxFilterRow :visible="gosterM" />
            <DxSearchPanel :visible="true" :width="240" />
            <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always" />
            <DxSorting mode="multiple" />
            <DxExport :enabled="true" :allow-export-selected-data="false" />

            <DxToolbar>
              <DxItem location="before" locate-in-menu="auto" template="filtreTarih1" />
              <DxItem location="before" locate-in-menu="auto" template="filtreTarih2" />
              <DxItem location="before" locate-in-menu="auto" template="filtreMerkez" />
              <DxItem location="before" locate-in-menu="auto" template="filtreIstasyon" />
              <DxItem location="before" locate-in-menu="auto" template="filtreSiparis" />
              <DxItem location="before" locate-in-menu="auto" template="filtreCari" />
              <DxItem location="before" locate-in-menu="auto" template="yenileTemplate"
                menu-item-template="menuYenileTemplate" @click="YenileMalzemeler" />
              <DxItem location="after" locate-in-menu="auto" template="filtreTemizleTemplate"
                menu-item-template="menuFiltreTemizleTemplate" @click="FiltreTemizleM" />
              <DxItem location="after" locate-in-menu="auto" template="filtreGosterTemplate"
                menu-item-template="menuFiltreGosterTemplate" @click="toggleGosterM" />
              <DxItem name="exportButton" />
              <DxItem name="searchPanel" />
            </DxToolbar>

            <DxSummary>
              <DxGroupItem :align-by-column="true" column="stok_adi" summary-type="count" display-format="{0} adet"
                :alignment="right" />
              <DxTotalItem :align-by-column="true" column="stok_adi" summary-type="count" display-format="{0} adet"
                :alignment="right" />
            </DxSummary>

            <template #filtreTarih1>
              <div style="margin-block-start: -10px;">
                <DxDateBox label="Başlangıç Tarihi" width="150" label-mode="floating" v-model:value="filterValue"
                  type="date" />
              </div>
            </template>

            <template #filtreTarih2>
              <div style="margin-block-start: -10px; margin-inline-start: 5px;">
                <DxDateBox label="Bitiş Tarihi" width="150" label-mode="floating" v-model:value="filterValue1"
                  type="date" />
              </div>
            </template>

            <template #filtreMerkez>
              <div style="inline-size: 200px; margin-block-start: -10px; margin-inline-start: 5px;">
                <DxSelectBox :data-source="merkezler" v-model:value="merkez" :show-clear-button="true"
                  label="İş Merkezi" label-mode="floating" display-expr="mrk_adi" value-expr="is_merkezi_id"
                  @value-changed="getIstasyonlar()" style="inline-size: 100%;" search-mode="contains"
                  search-expr="mrk_adi" :search-timeout="200" :search-enabled="true" />
              </div>
            </template>

            <template #filtreIstasyon>
              <div style="inline-size: 200px; margin-block-start: -10px; margin-inline-start: 5px;">
                <DxSelectBox :data-source="istasyonlar" v-model:value="istasyon" label="İstasyon" label-mode="floating"
                  display-expr="ist_adi" value-expr="istasyon_id" style="inline-size: 100%;" search-mode="contains"
                  search-expr="ist_adi" :search-timeout="200" :search-enabled="true" />
              </div>
            </template>

            <template #filtreSiparis>
              <div style="inline-size: 160px; margin-block-start: -10px; margin-inline-start: 5px;">
                <DxSelectBox :data-source="siparisler" v-model:value="siparis" label="Sipariş" label-mode="floating"
                  display-expr="siparis_belge_no" value-expr="siparis_belge_no" style="inline-size: 100%;"
                  :show-clear-button="true" search-mode="contains" search-expr="siparis_belge_no" :search-timeout="200"
                  :search-enabled="true" />
              </div>
            </template>

            <template #filtreCari>
              <div style="inline-size: 200px; margin-block-start: -10px; margin-inline-start: 5px;">
                <DxSelectBox :data-source="cariler" v-model:value="cari" label="Cari" label-mode="floating"
                  display-expr="cari_ad" value-expr="cari_ad" style="inline-size: 100%;" :show-clear-button="true"
                  search-mode="contains" search-expr="cari_ad" :search-timeout="200" :search-enabled="true" />
              </div>
            </template>

            <template #filtreTemizleTemplate>
              <DxButton id="filtretemizle" icon="notequal" styling-mode="text" hint="Filtre Temizle" />
              <!-- <VIcon size="24" icon="tabler-x" /> -->
            </template>
            <template #menuFiltreTemizleTemplate>
              <div style="display: flex; align-items: center; margin-inline-start: 13px;">
                <i class="dx-icon dx-icon-notequal"></i>
                <span style="margin-inline-start: 8px;">Filtre Temizle</span>
              </div>
            </template>

            <template #yenileTemplate>
              <DxButton id="sayim" icon="refresh" styling-mode="text" hint="Yenile" />
            </template>
            <template #menuYenileTemplate>
              <div style="display: flex; align-items: center; margin-inline-start: 13px;">
                <i class="dx-icon dx-icon-refresh"></i>
                <span style="margin-inline-start: 8px;">Yenile</span>
              </div>
            </template>

            <template #filtreGosterTemplate>
              <DxButton icon="filter" styling-mode="text" hint="Filtre Goster" />
            </template>
            <template #menuFiltreGosterTemplate>
              <div style="display: flex; align-items: center; margin-inline-start: 13px;">
                <i class="dx-icon dx-icon-filter"></i>
                <span style="margin-inline-start: 8px;">Filtre Göster</span>
              </div>
            </template>

          </DxDataGrid>
        </div>
      </VCol>
    </VCardText>
  </VCard>

  <DxPopup v-model:visible="popupDepolarGosterVisible" :hide-on-outside-click="true" title='Stok Detayları'
    :show-close-button="false" :show-title="true" :width="600" :height="500">
    <VCol class="text-center">
      <h2>{{ eksikStokKodu }}</h2>
      <h2>{{ eksikStokAdi }}</h2>
    </VCol>
    <br>
    <VRow>
      <VCol>
        <DxDataGrid id="gridBakiyeler" ref="dataGridRefB" :data-source="gridBakiyeler" :show-borders="true"
          :column-auto-width="true" :allow-column-resizing="true" column-resizing-mode="widget"
          @cell-prepared="onCellPreparedB" :row-alternation-enabled="true">

          <DxColumn data-field="whouse_id" caption="DEPO ID" :width="80" :visible="false" />
          <DxColumn data-field="whouse_code" caption="DEPO KODU" :width="100" />
          <DxColumn data-field="description" caption="DEPO ADI" />
          <DxColumn data-field="qty_prm" caption="STOK" :width="100" data-type="number" :format="{
            type: 'fixedPoint',
            precision: 2,
            thousandsSeparator: ',',
          }" />


          <DxGroupPanel :visible="false" />
          <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always" />

        </DxDataGrid>
      </VCol>
    </VRow>
  </DxPopup>

  <DxPopup v-model:visible="popupTaleplerGosterVisible" :hide-on-outside-click="true" title='Taleplerin Detayı'
    :show-close-button="false" :show-title="true" :width="800" :height="500">
    <VCol class="text-center">
      <h2>{{ eksikStokKodu }}</h2>
      <h2>{{ eksikStokAdi }}</h2>
    </VCol>
    <br>
    <VRow>
      <VCol>
        <DxDataGrid id="gridTalepler" ref="dataGridRefT" :data-source="gridTalepler" :show-borders="true"
          :column-auto-width="true" :allow-column-resizing="true" column-resizing-mode="widget"
          :row-alternation-enabled="true" width="100%">

          <DxColumn data-field="talep_no" caption="TALEP NO" :width="100" />
          <DxColumn data-field="talep_tarihi" caption="TALEP TARİHİ" data-type="date" :width="110" :visible="true"
            :format="{
              formatter: (date) => {
                const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                  year: 'numeric',
                  month: '2-digit',
                  day: '2-digit',
                }).format(new Date(date));

                return formattedDate.replace(/\//g, '.');
              },
            }" alignment="left" />
          <DxColumn data-field="onay_tarihi" caption="ONAY TARİHİ" data-type="date" :width="120" :visible="true"
            :format="{
              formatter: (date) => {
                const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                  year: 'numeric',
                  month: '2-digit',
                  day: '2-digit',
                }).format(new Date(date));

                return formattedDate.replace(/\//g, '.');
              },
            }" alignment="left" />
          <DxColumn data-field="kaynak" caption="KAYNAK" :width="150" />
          <DxColumn data-field="tipi" caption="TİPİ" :width="100" />
          <DxColumn data-field="miktar" caption="MİKTAR" :width="110" data-type="number" :format="{
            type: 'fixedPoint',
            precision: 0,
            thousandsSeparator: ',',
          }" />

          <DxGroupPanel :visible="false" />
          <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always" />

        </DxDataGrid>
      </VCol>
    </VRow>
  </DxPopup>

  <DxPopup v-model:visible="popupSatinalmaGosterVisible" :hide-on-outside-click="true" title='Satınalma Sipariş Detayı'
    :show-close-button="false" :show-title="true" :width="1300" :height="500">
    <VCol class="text-center">
      <h2>{{ eksikStokKodu }}</h2>
      <h2>{{ eksikStokAdi }}</h2>
    </VCol>
    <br>
    <VRow>
      <VCol>
        <DxDataGrid id="gridSatinalma" ref="dataGridRefS" :data-source="gridSatinalma" :show-borders="true"
          :column-auto-width="true" :allow-column-resizing="true" column-resizing-mode="widget"
          :row-alternation-enabled="true" width="100%">

          <DxColumn data-field="BELGE_NO" caption="BELGE NO" :width="100" />
          <DxColumn data-field="BELGE_TARIHI" caption="BELGE TARİHİ" data-type="date" :width="110" :visible="true"
            :format="{
              formatter: (date) => {
                const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                  year: 'numeric',
                  month: '2-digit',
                  day: '2-digit',
                }).format(new Date(date));

                return formattedDate.replace(/\//g, '.');
              },
            }" alignment="left" />
          <DxColumn data-field="satici_adi" caption="SATICI" :width="100" />
          <DxColumn data-field="sip_olusturan" caption="OLUŞTURAN" :width="100" />
          <DxColumn data-field="CARI_AD" caption="CARİ ADI" :width="280" />
          <DxColumn data-field="sevk_tarihi" caption="SEVK TARİHİ" data-type="date" :width="100" :visible="true"
            :format="{
              formatter: (date) => {
                const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                  year: 'numeric',
                  month: '2-digit',
                  day: '2-digit',
                }).format(new Date(date));

                return formattedDate.replace(/\//g, '.');
              },
            }" alignment="left" />
          <DxColumn data-field="teslim_tarihi" caption="TESLİM TARİHİ" data-type="date" :width="100" :visible="true"
            :format="{
              formatter: (date) => {
                const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                  year: 'numeric',
                  month: '2-digit',
                  day: '2-digit',
                }).format(new Date(date));

                return formattedDate.replace(/\//g, '.');
              },
            }" alignment="left" />
          <DxColumn data-field="teslim_tarihi_not" caption="T.T. NOT" :width="80" />
          <DxColumn data-field="MIKTAR" caption="SPR. MİKTAR" :width="110" data-type="number" :format="{
            type: 'fixedPoint',
            precision: 0,
            thousandsSeparator: ',',
          }" />
          <DxColumn data-field="BIRIM_KOD" caption="BİRİM" :width="60" />
          <DxColumn data-field="KALAN_MIKTAR" caption="KALAN MİKTAR" :width="100" data-type="number" :format="{
            type: 'fixedPoint',
            precision: 0,
            thousandsSeparator: ',',
          }" />

          <DxGroupPanel :visible="false" />
          <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always" />

        </DxDataGrid>
      </VCol>
    </VRow>
  </DxPopup>

  <DxPopup v-model:visible="popupAcilmisIsEmirleriGosterVisible" :hide-on-outside-click="true"
    title='Açılmış İş Emirleri' :show-close-button="false" :show-title="true" :width="1000" :height="600">
    <VCol class="text-center">
      <h2>{{ eksikStokKodu }}</h2>
      <h2>{{ eksikStokAdi }}</h2>
    </VCol>
    <br>
    <VRow>
      <VCol>
        <DxDataGrid id="gridAcilmisEmirler" ref="dataGridRefA" :data-source="gridDataAcilmislar" :show-borders="true"
          :column-auto-width="true" :allow-column-resizing="true" column-resizing-mode="widget"
          :row-alternation-enabled="true" width="100%" :height="400">

          <DxColumn data-field="siparis_belge_no" caption="SİPARİŞ NO" :width="120" :visible="true" />
          <DxColumn data-field="cari_ad" caption="CARİ ADI" :width="270" :visible="true" />
          <DxColumn data-field="isemri_id" caption="İŞ EMRİ ID" :width="100" :visible="false" />
          <DxColumn data-field="isemri_no" caption="İŞ EMRİ NO" :width="120" />
          <DxColumn data-field="stok_id" caption="STOK ID" :width="100" :visible="false" />
          <DxColumn data-field="stok_kodu" caption="STOK KODU" :width="120" :visible="false" />
          <DxColumn data-field="stok_adi" caption="STOK ADI" :visible="false" />
          <DxColumn data-field="planlanan_bitis_tarihi" caption="PLN BTŞ" data-type="date" :width="110" :visible="true"
            :format="{
              formatter: (date) => {
                const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                  year: 'numeric',
                  month: '2-digit',
                  day: '2-digit',
                }).format(new Date(date));

                return formattedDate.replace(/\//g, '.');
              },
            }" alignment="left" />
          <DxColumn data-field="isemri_miktari" caption="İŞ EMRİ MKTRI" :width="120" data-type="number" :format="{
            type: 'fixedPoint',
            precision: 0,
            thousandsSeparator: ',',
          }" />
          <DxColumn data-field="kalan_miktar" caption="KALAN MİKTAR" :width="120" data-type="number" :format="{
            type: 'fixedPoint',
            precision: 0,
            thousandsSeparator: ',',
          }" />

          <DxSummary>
            <DxTotalItem :align-by-column="true" column="isemri_miktari" summary-type="sum" display-format=" Tpl: {0}"
              :alignment="right" :customize-text="formatSummaryText" />
            <DxTotalItem :align-by-column="true" column="kalan_miktar" summary-type="sum" display-format=" Tpl: {0}"
              :alignment="right" :customize-text="formatSummaryText" />
          </DxSummary>

          <DxGroupPanel :visible="false" />
          <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always" />

        </DxDataGrid>
      </VCol>
    </VRow>
  </DxPopup>

  <DxPopup v-model:visible="popupIsEmirleriGosterVisible" :hide-on-outside-click="true" title='Kullanan İş Emirleri'
    :show-close-button="false" :show-title="true" :width="1300" :height="500">
    <VCol class="text-center">
      <h2>{{ eksikStokKodu }}</h2>
      <h2>{{ eksikStokAdi }}</h2>
    </VCol>
    <br>
    <VRow>
      <VCol>
        <DxDataGrid id="gridEmirler" ref="dataGridRefE" :data-source="filtrelenmisEmirler()" :show-borders="true"
          :column-auto-width="false" :allow-column-resizing="true" column-resizing-mode="widget"
          :row-alternation-enabled="true" width="100%">

          <DxColumn data-field="siparis_belge_no" caption="SİPARİŞ NO" :width="120" :visible="true" />
          <DxColumn data-field="cari_ad" caption="CARİ ADI" :width="150" :visible="true" />
          <DxColumn data-field="isemri_id" caption="İŞ EMRİ ID" :width="100" :visible="false" />
          <DxColumn data-field="isemri_no" caption="İŞ EMRİ NO" :width="120" />
          <DxColumn data-field="stok_id" caption="STOK ID" :visible="false"  :width="50"/>
          <DxColumn data-field="stok_kodu" caption="STOK KODU"  :width="150"/>
          <DxColumn data-field="stok_adi" caption="STOK ADI"  :width="auto" />
          <DxColumn data-field="isemri_miktari" caption="İŞ EMRİ MİKTARI" :width="120" data-type="number" :format="{
            type: 'fixedPoint',
            precision: 2,
            thousandsSeparator: ',',
          }" />
          <DxColumn data-field="kalan" caption="KALAN MİKTAR" :width="120" data-type="number" :format="{
            type: 'fixedPoint',
            precision: 2,
            thousandsSeparator: ',',
          }" />

            <DxSummary>
              <DxTotalItem :align-by-column="true" column="isemri_miktari" summary-type="sum" display-format="{0} ad"
                :alignment="right" />
              <DxTotalItem :align-by-column="true" column="kalan" summary-type="sum" display-format="{0} ad"
                :alignment="right" />
            </DxSummary>

          <DxGroupPanel :visible="false" />
          <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always" />

        </DxDataGrid>
      </VCol>
    </VRow>
  </DxPopup>

</template>


<script setup lang="ts">
import { usePageTitleStore } from '@/stores/pageTitle'
import axios from 'axios'
import { DxButton } from 'devextreme-vue/button'
import DxContextMenu, { DxContextMenuTypes } from 'devextreme-vue/context-menu'
import type { DxDataGridTypes } from 'devextreme-vue/data-grid'
import {
  DxColumn,
  DxDataGrid,
  DxExport,
  DxFilterPanel,
  DxFilterRow,
  DxGroupItem,
  DxHeaderFilter,
  DxScrolling,
  DxSearchPanel,
  DxSorting,
  DxSummary,
  DxToolbar,
  DxTotalItem,
} from 'devextreme-vue/data-grid'
import DxDateBox from "devextreme-vue/date-box"
import { DxLoadPanel } from 'devextreme-vue/load-panel'
import { DxPopup } from 'devextreme-vue/popup'
import DxSelectBox from 'devextreme-vue/select-box'
import { DxItem } from 'devextreme-vue/tabs'
import { exportDataGrid } from 'devextreme/excel_exporter'
import { Workbook } from 'exceljs'
import { saveAs } from 'file-saver-es'
import { computed, onMounted, ref } from 'vue'

definePage({
  meta: { action: ['read'], subject: ['planlama', 'montaj', 'satinalma', 'satis'] }
})

const formatDate = (date: any) => {
  if (!date)
    return null
  const d = new Date(date)
  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')

  return `${year}-${month}-${day}`
}

const pageTitleStore = usePageTitleStore()
const pageName = 'İhtiyaç Listesi'
const pageAlias = ''
const popupDepolarGosterVisible = ref(false)
const popupIsEmirleriGosterVisible = ref(false)
const popupAcilmisIsEmirleriGosterVisible = ref(false)
const popupSatinalmaGosterVisible = ref(false)
const popupTaleplerGosterVisible = ref(false)

const loadingVisible = ref<boolean>(false)
const gridDataAcilmislar = ref<any[]>([])
const gridDataM = ref<any[]>([])
const gridDataDagilim = ref<any[]>([])
const gridSatinalma = ref<any[]>([])
const gridTalepler = ref<any[]>([])
const gridKeyM = ref(Date.now())

const userData = useCookie<any>('userData')
const dataGridRefM = ref<DxDataGrid | null>(null)
const dataGridRefB = ref<DxDataGrid | null>(null)
const dataGridRefE = ref<DxDataGrid | null>(null)
const dataGridRefS = ref<DxDataGrid | null>(null)
const dataGridRefT = ref<DxDataGrid | null>(null)
const initialFilter = ref<any>(null)
const gosterM = ref(true)
const depolar = ref(false)
const totalRecord = ref(0)
const stokId = ref(0)
const expandAll = ref(false)
const eksikStokKodu = ref(0)
const eksikStokAdi = ref('')
const istasyonlar = ref<any[]>([])
const istasyon = ref(0)
const merkezler = ref<any[]>([])
const merkez = ref(0)
const siparis = ref('')
const siparisler = ref<any[]>([])
const cari = ref('')
const cariler = ref<any[]>([])
const now = new Date()
const firstDayOfWeek = new Date(now)
firstDayOfWeek.setDate(now.getDate() - now.getDay() + 1) // Pazartesi
const lastDayOfWeek = new Date(now)
lastDayOfWeek.setDate(now.getDate() - now.getDay() + 7) // Pazar
const initialDates = [firstDayOfWeek, lastDayOfWeek]
const selectedDateRange = ref(initialDates)
const filterValue = ref<any>(firstDayOfWeek) // Formatlanmış başlangıç tarihi
const filterValue1 = ref<any>(lastDayOfWeek) // Formatlanmış bitiş tarihi
const gridBakiyeler = ref<any[]>([])
const selectedRow = ref<any | null>(null);

// İş Merkezi ve İstasyon seçim durumuna göre koşullu görünürlük/başlık
const bothSelected = computed(() => !!merkez.value && !!istasyon.value)
const digerDepoCaption = computed(() => (bothSelected.value ? 'DİĞER DEPO' : 'STOK'))

// BAKİYE sütunu hücre değeri, seçim durumuna göre farklı hesaplanır
// - Her ikisi de seçiliyse: backend'den gelen "bakiye" alanını kullan
// - Seçili değilse: (diger_depo as STOK) - ihtiyac
const calculateBakiyeCellValue = (rowData: any) => {
  if (bothSelected.value)
    return Number(rowData?.bakiye ?? 0)

  const stokFromDigerDepo = Number(rowData?.diger_depo ?? 0)
  const ihtiyacVal = Number(rowData?.ihtiyac ?? 0)

  return stokFromDigerDepo - ihtiyacVal
}

// DİĞER DEPO sütunu hücre değeri, seçim varken: (ana_depo + diger_depo)  [= (toplam - stok) ile eşdeğer]; seçim yokken: backend değeri
const calculateDigerDepoCellValue = (rowData: any) => {
  if (!bothSelected.value)
    return Number(rowData?.diger_depo ?? 0)

  const digerDepoVal = Number(rowData?.diger_depo ?? 0)
  const anaDepoVal = Number(rowData?.ana_depo ?? 0)
  return digerDepoVal + anaDepoVal
}

pageTitleStore.setTitle(`${pageName} (${pageAlias})`)
document.title = `OFT - ${pageName} | ${pageAlias}`

const EksikleriKontrolEt = async () => {
  await getEksikler()
}

// Eksik olan fonksiyon için güvenli stub (kullanım yoksa no-op)
const getEksikler = async (): Promise<void> => { return }


function formatSummaryText(e: any) {
  return new Intl.NumberFormat('tr-TR', { maximumFractionDigits: 1 }).format(e.value);
}

const YenileMalzemeler = async () => {
  // if (merkez.value === 0 || merkez.value === null) {
  //   notify('Hiç olmazsa İş Merkezini seçiverseydiniz :)', 'warning', 2000)
  //   return;
  // }
  gridDataM.value = [];
  await getMalzemeler()
}

const getMalzemeler = async () => {
  selectedDateRange.value = [filterValue.value, filterValue1.value]

  filterValue.value = formatDate(selectedDateRange.value[0])
  filterValue1.value = formatDate(selectedDateRange.value[1])
  loadingVisible.value = true
  try {
    const response = await axios.get('/api/istasyon-ihtiyaclar', {
      params: {
        filterValue: filterValue.value,
        filterValue1: filterValue1.value,
        merkezID: merkez.value,
        istasyonID: istasyon.value,
        siparis: siparis.value,
        cari: cari.value,
        coID: userData.value.co_id,
      },
    })
    gridDataM.value = response.data.emirler
    gridDataDagilim.value = response.data.dagilim

  } catch (error) {
    console.error('Veri çekilirken hata oluştu: ', error)
  } finally {
    loadingVisible.value = false
  }
}

const getMerkezler = async () => {
  loadingVisible.value = true
  try {
    const response = await axios.get('/api/merkezal', {
      params: {
        coID: userData.value.co_id,
      },
    })
    merkezler.value = response.data.merkezler
    siparisler.value = response.data.siparisler
    cariler.value = response.data.cariler
  } catch (error) {
    console.error('Veri çekilirken hata oluştu: ', error)
  } finally {
    loadingVisible.value = false
  }
}

const getIstasyonlar = async () => {
  istasyon.value = 0;
  gridDataM.value = [];
  loadingVisible.value = true
  try {
    const response = await axios.get('/api/istasyonal', {
      params: {
        ismerkezi: merkez.value,
      },
    })
    istasyonlar.value = response.data.istasyonlar
  } catch (error) {
    console.error('Veri çekilirken hata oluştu: ', error)
  } finally {
    loadingVisible.value = false
  }
}

const getAcilmisEmirler = async (itemID: number) => {
  loadingVisible.value = true
  try {
    const response = await axios.get('/api/isEmriAcilmislar', {
      params: {
        item_id: itemID,
      },
    })
    gridDataAcilmislar.value = response.data.data
  } catch (error) {
    console.error('Veri çekilirken hata oluştu: ', error)
  } finally {
    loadingVisible.value = false
  }
}

onMounted(async () => {
  loadGridState()
  getMerkezler()
  const currentWeek = getCurrentWeek()

})

const loadGridState = () => {
  const savedState = localStorage.getItem(`${userData.value.id}_malzemeler`)
  if (savedState)
    dataGridRefM.value?.instance?.state(JSON.parse(savedState))

  // console.log("Grid durumu yüklendi:", savedState);
}

const saveGridState = () => {
  const state = dataGridRefM.value?.instance?.state()

  localStorage.setItem(`${userData.value.id}_malzemeler`, JSON.stringify(state))

  // console.log("Grid durumu kaydedildi:", state);
}

const onStateResetClick = () => {
  localStorage.removeItem(`${userData.value.id}_malzemeler`)
  dataGridRefM.value!.instance!.state(null)
}

const getCurrentWeek = (): string => {
  const today = new Date()
  const year = today.getFullYear() % 100

  // Haftanın ilk günü pazartesi kabul edilsin ve ISO 8601 standartlarına uygun hafta numarası hesaplansın
  const firstThursday = new Date(today.getFullYear(), 0, 4) // Yılın ilk perşembesi
  const firstMonday = new Date(firstThursday)

  firstMonday.setDate(firstThursday.getDate() - (firstThursday.getDay() + 6) % 7) // İlk pazartesiyi bul

  // Gün farkını hesapla ve hafta numarasını bul
  const diff = Math.floor((today.getTime() - firstMonday.getTime()) / 86400000)
  const week = Math.floor(diff / 7) + 1

  return `${year}-${week.toString().padStart(2, '0')}`
}

const toggleGosterM = () => {
  gosterM.value = !gosterM.value
}

const FiltreTemizleM = () => {
  dataGridRefM.value?.instance?.clearFilter()
}

const toggleExpandAll = () => {
  expandAll.value = !expandAll.value
}

const onCellPreparedM = (e: any) => {
  if (e.rowType === 'data' && ((
    e.column.dataField === 'talepler'
    || e.column.dataField === 'satinalma'
    || e.column.dataField === 'stok'
    || e.column.dataField === 'ana_depo'
    || e.column.dataField === 'diger_depo'
  ) && e.value > 0))
    e.cellElement.style.fontWeight = 'bold'

  if (e.rowType === 'data' && e.column.dataField === 'stok' && Number(e.data.stok) < Number(e.data.ihtiyac))
    e.cellElement.style.backgroundColor = 'orange'

  if (e.rowType === 'data' && e.column.dataField === 'ana_depo' && (Number(e.data.ana_depo) + Number(e.data.stok)) < Number(e.data.ihtiyac)) {
    e.cellElement.style.backgroundColor = '#ff6e00'
    e.cellElement.style.color = 'white'
  }
  if (e.rowType === 'data' && e.column.dataField === 'diger_depo' && (Number(e.data.diger_depo) + Number(e.data.ana_depo) + Number(e.data.stok)) < Number(e.data.ihtiyac)) {
    e.cellElement.style.backgroundColor = '#ff5400'
    e.cellElement.style.color = 'white'
  }

  if (e.rowType === 'data' && e.column.dataField === 'bakiye' && e.value < 0) {
    e.cellElement.style.fontWeight = 'bold'
    e.cellElement.style.backgroundColor = 'red'
    e.cellElement.style.color = 'white'
  }
}

const onDateRangeChanged = async (newValue: any) => {
  if (!newValue || !newValue.value || newValue.value.length !== 2) {
    console.warn('Geçersiz tarih aralığı')
    selectedDateRange.value = initialDates
    filterValue.value = formatDate(selectedDateRange.value[0])
    filterValue1.value = formatDate(selectedDateRange.value[1])

    return
  }
  const [startDate, endDate] = newValue.value

  selectedDateRange.value = [startDate, endDate]
  filterValue.value = formatDate(startDate)
  filterValue1.value = formatDate(endDate)

  // await getMalzemeler();
}

const onContextMenuPreparing = (e: any) => {
  if (e.rowIndex === -1)
    return
  const data = e.row!.data!
  selectedRow.value = data
}

const onExportingM = (e: DxDataGridTypes.ExportingEvent) => {
  const workbook = new Workbook()
  const worksheet = workbook.addWorksheet('MalzemeListesi')

  exportDataGrid({
    component: e.component,
    worksheet,
    autoFilterEnabled: true,
  }).then(() => {
    workbook.xlsx.writeBuffer().then(buffer => {
      saveAs(
        new Blob([buffer], { type: 'application/octet-stream' }),
        'MalzemeListesi.xlsx',
      )
    })
  })

  e.cancel = true
}

const convertToISODate = (dateString: string | null | undefined) => {
  if (!dateString || typeof dateString !== 'string')
    return null

  const [day, month, year] = dateString.split('.')

  return `${year}-${month}-${day}`
}

const getIconType = (cellElement: HTMLElement, cellInfo: any): void => {
  // Tarihleri kontrol et ve dönüştür
  const teslimTarihiISO = convertToISODate(cellInfo.data.teslim_tarihi)
  const planlananBitisISO = convertToISODate(cellInfo.data.planlanan_bitis_tarihi)

  // Eğer tarih formatı geçersizse varsayılan bir değer ata
  const date1 = planlananBitisISO ? new Date(planlananBitisISO) : new Date()
  const date2 = teslimTarihiISO ? new Date(teslimTarihiISO) : new Date()

  date1.setHours(0, 0, 0, 0)
  date2.setHours(0, 0, 0, 0)

  const diffInDays = Math.floor(
    (date2.getTime() - date1.getTime()) / (1000 * 60 * 60 * 24),
  )

  // Ana metni ekle (hafta değeri)
  cellElement.innerText = cellInfo.text

  const createIcon = (color: string, dolu: boolean) => {
    const icon = document.createElement('span')

    icon.className = dolu ? 'dx-icon dx-icon-isnotblank' : 'dx-icon dx-icon-isblank'
    icon.style.marginRight = '8px' // İkon ile metin arasına boşluk ekle
    icon.style.color = color // İkon rengi
    icon.style.fontSize = '16' // Ana span'ı etkisizleştir
    icon.style.display = 'inline-flex'

    return icon
  }

  if (diffInDays < 0) {
    const redIcon = createIcon('red', true)
    if (cellElement.firstChild)
      cellElement.insertBefore(redIcon, cellElement.firstChild)
    else
      cellElement.appendChild(redIcon) // İlk çocuk yoksa sona ekle
  } else if (diffInDays === 0) {
    const blueIcon = createIcon('blue', true)
    if (cellElement.firstChild)
      cellElement.insertBefore(blueIcon, cellElement.firstChild)
    else
      cellElement.appendChild(blueIcon) // İlk çocuk yoksa sona ekle
  } else if (diffInDays <= 3) {
    const orangeIcon = createIcon('orange', true)
    if (cellElement.firstChild)
      cellElement.insertBefore(orangeIcon, cellElement.firstChild)
    else
      cellElement.appendChild(orangeIcon) // İlk çocuk yoksa sona ekle
  } else {
    const whiteIcon = createIcon('white', false)
    if (cellElement.firstChild)
      cellElement.insertBefore(whiteIcon, cellElement.firstChild)
    else
      cellElement.appendChild(whiteIcon) // İlk çocuk yoksa sona ekle
  }
}

const tipCellTemplate = (cellElement: HTMLElement, cellInfo: any): void => {
  cellElement.innerText = cellInfo.text

  const tip = cellInfo.data.tipi

  const createIcon = (color: string, dolu: boolean) => {
    const icon = document.createElement('span')

    icon.className = dolu ? 'dx-icon dx-icon-isnotblank' : 'dx-icon dx-icon-isblank'
    icon.style.marginRight = '8px' // İkon ile metin arasına boşluk ekle
    icon.style.color = color // İkon rengi
    icon.style.fontSize = '16' // Ana span'ı etkisizleştir
    icon.style.display = 'inline-flex'

    return icon
  }

  let renk: string = 'gray'

  switch (tip) {
    case 'Hammadde':
      renk = 'gold'
      break;
    case 'YarıMamul':
      renk = 'limegreen'
      break;
    case 'Mamul':
      renk = 'green'
      break;
    case 'Ticari':
      renk = 'gray'
      break;
  }
  const plnIcon = createIcon(renk, true)

  cellElement.insertBefore(plnIcon, cellElement.firstChild)
}

const menuItems = [
  { text: 'Yenile' },
  { text: 'Hangi İş Emirlerinde' },
  { text: 'Açılmış İş Emirleri' },
  { text: 'Stok Detayları' },
  { text: 'Satınalma Sipariş Detayları' },
  { text: 'Satınalma Talep Detayları' },
  { text: 'Düzen Yükle' },
  { text: 'Düzen Kaydet' },
  { text: 'Düzen Sıfırla' },
]

function itemClick({ itemData }: DxContextMenuTypes.ItemClickEvent) {
  if (!itemData?.items) {
    switch (itemData?.text) {
      case 'Yenile':
        YenileMalzemeler()
        break;
      case 'Hangi İş Emirlerinde':
        stokId.value = selectedRow.value.item_id
        eksikStokKodu.value = selectedRow.value.stok_kodu
        eksikStokAdi.value = selectedRow.value.stok_adi
        popupIsEmirleriGosterVisible.value = true
        break;
      case 'Açılmış İş Emirleri':
        stokId.value = selectedRow.value.item_id
        eksikStokKodu.value = selectedRow.value.stok_kodu
        eksikStokAdi.value = selectedRow.value.stok_adi
        gridDataAcilmislar.value = []
        getAcilmisEmirler(selectedRow.value.item_id)
        popupAcilmisIsEmirleriGosterVisible.value = true
        break;
      case 'Stok Detayları':
        gridBakiyeler.value = []
        eksikStokKodu.value = selectedRow.value.stok_kodu
        eksikStokAdi.value = selectedRow.value.stok_adi
        getBakiyeler(selectedRow.value.item_id)
        popupDepolarGosterVisible.value = true
        break;
      case 'Satınalma Sipariş Detayları':
        gridSatinalma.value = []
        eksikStokKodu.value = selectedRow.value.stok_kodu
        eksikStokAdi.value = selectedRow.value.stok_adi
        getSatinalma(selectedRow.value.item_id)
        popupSatinalmaGosterVisible.value = true
        break;
      case 'Satınalma Talep Detayları':
        gridTalepler.value = []
        eksikStokKodu.value = selectedRow.value.stok_kodu
        eksikStokAdi.value = selectedRow.value.stok_adi
        getTalepler(selectedRow.value.item_id)
        popupTaleplerGosterVisible.value = true
        break;
      case 'Düzen Yükle':
        loadGridState()
        break;
      case 'Düzen Kaydet':
        saveGridState()
        break;
      case 'Düzen Sıfırla':
        onStateResetClick()
        break;
      default:
        break;
    }
  }
}

const formatNumber = (number: number) => {
  return new Intl.NumberFormat('tr-TR', { maximumFractionDigits: 0 }).format(
    number,
  )
}

const filtrelenmisEmirler = () => {
  return gridDataDagilim.value.filter(item => item.item_id === stokId.value);
};

const onCellClick = (e: any) => {
  if (e.rowType === 'data' && e.column.dataField === 'diger_depo') {
    gridBakiyeler.value = []
    eksikStokKodu.value = e.data.stok_kodu
    eksikStokAdi.value = e.data.stok_adi
    getBakiyeler(e.data.item_id)
    popupDepolarGosterVisible.value = true
  }
  if (e.rowType === 'data' && e.column.dataField === 'dongu') {
    stokId.value = e.data.item_id
    eksikStokKodu.value = e.data.stok_kodu
    eksikStokAdi.value = e.data.stok_adi
    popupIsEmirleriGosterVisible.value = true
  }
}

const getSatinalma = async (itemID: number) => {
  try {
    const response = await axios.get('/api/satinalmasorgu', {
      params: {
        item_id: itemID,
      },
    })

    gridSatinalma.value = response.data.satinalma
  } catch (error) {
    console.error('Veri çekilirken hata oluştu: ', error)
  }
}

const getTalepler = async (itemID: number) => {
  try {
    const response = await axios.get('/api/taleplersorgu', {
      params: {
        item_id: itemID,
      },
    })

    gridTalepler.value = response.data.talepler
  } catch (error) {
    console.error('Veri çekilirken hata oluştu: ', error)
  }
}

const getBakiyeler = async (itemID: number) => {
  try {
    const response = await axios.get('/api/digerdepobakiyeleri', {
      params: {
        item_id: itemID,
      },
    })

    gridBakiyeler.value = response.data.bakiyeler
  } catch (error) {
    console.error('Veri çekilirken hata oluştu: ', error)
  }
}


</script>


<style>
.grid {
  display: flex;
  flex-direction: column;
  block-size: 85vh;
}

.grid .informer {
  display: grid;
  grid-template-columns: 100%;
  inline-size: 120px;
  padding-inline-end: 20px;
  text-align: center;
}

.grid .count {
  font-size: 18px;
  font-weight: 500;
}

.grid .dx-toolbar-items-container {
  min-block-size: 44px;
}

.tabpanel-demo .dx-tabpanel {
  background-color: var(--dx-component-color-bg);
}

.dx-theme-material .widget-container {
  background-color: rgba(191, 191, 191, 15%);
}

.dx-tabpanel-tabs-position-left .dx-tabpanel-container,
.dx-tabpanel-tabs-position-right .dx-tabpanel-container {
  inline-size: 0;
}

.dx-viewport:not(.dx-theme-generic) .dx-tabpanel {
  overflow: clip;
  border-radius: 8px;
}

.dx-tabs-vertical {
  min-inline-size: 120px;
}

.options {
  display: inline-flex;
  box-sizing: border-box;
  flex-direction: column;
  flex-shrink: 0;
  padding: 20px;
  background-color: rgba(191, 191, 191, 15%);
  inline-size: 200px;
}

.caption {
  font-size: 18px;
  font-weight: 500;
}

.option {
  margin-block-start: 20px;
}
</style>

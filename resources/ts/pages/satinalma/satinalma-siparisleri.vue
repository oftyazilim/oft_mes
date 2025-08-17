<template>
  <VCard class="mt-0 pa-0">
    <VCardText class="mt-0 pa-2 ">
      <VCol cols="12" class="mt-2 pa-1 pe-2 pt-0">
        <div id="liste" style="margin-block-end: 10px; margin-top: -10px">
          <DxContextMenu :data-source="menuItems" :width="200" target="#grid" @item-click="itemClick" />

          <DxDataGrid id="grid" ref="dataGridRef" :key="gridKey" class="grid" :data-source="gridData"
            key-expr="order_d_id" :show-borders="true" focused-row-enabled="true" row-alternation-enabled="true"
            :min-width="200" allow-column-reordering="true" column-auto-width="true" allow-column-resizing="true"
            @contextMenuPreparing="onContextMenuPreparing" column-resizing-mode="widget"
            @selection-changed="onSelectionChanged" :auto-navigate-to-focused-row="true" width="100%"
            @exporting="onExporting" :selected-rows-keys="selectedRows">

            <DxColumn data-field="satici_adi" caption="SATICI" :visible="true" :width="120" />
            <DxColumn data-field="BELGE_NO" caption="BELGE NO" :visible="true" :width="100" />
            <DxColumn data-field="BELGE_TARIHI" caption="BELGE TARİHİ" data-type="date" :width="150" :visible="true"
              :format="{
                formatter: (date) => {
                  const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                  }).format(new Date(date));

                  return formattedDate.replace(/\//g, '.');
                },
              }" alignment="center" />
            <DxColumn data-field="FIRMA" caption="FİRMA" :visible="true" :width="120" />
            <DxColumn data-field="ONAY_DURUMU" caption="ONAY DURUMU" :visible="true" :width="100" />
            <DxColumn data-field="ACIK_KAPALI" caption="AÇIK-KAPALI" :visible="true" :width="80" />
            <DxColumn data-field="KANAL_TIPI" caption="KANAL TİPİ" :visible="true" :width="80" />
            <DxColumn data-field="CARI_TIPI" caption="CARİ TİPİ" :visible="true" :width="80" />
            <DxColumn data-field="teslim_tarihi" caption="TESLİM TARİHİ" data-type="date" :width="150" :visible="true"
              :format="{
                formatter: (date) => {
                  const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                  }).format(new Date(date));

                  return formattedDate.replace(/\//g, '.');
                },
              }" alignment="center" />
            <DxColumn data-field="teslim_tarihi_not" caption="NOT" :visible="true" :width="80" />
            <DxColumn data-field="sevk_tarihi" caption="SEVK TARİHİ" data-type="date" :width="150" :visible="true"
              :format="{
                formatter: (date) => {
                  const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                  }).format(new Date(date));

                  return formattedDate.replace(/\//g, '.');
                },
              }" alignment="center" />
            <DxColumn data-field="BACKLOG_DURUM" caption="BACKLOG DURUM" :visible="true" :width="120" />
            <DxColumn data-field="TESLIM_SEKLI" caption="TESLİM ŞEKLİ" :visible="true" :width="100" />
            <DxColumn data-field="TESLIM_SEKLI_ACIKLAMA" caption="TESLİM ŞEKLİ NOT" :visible="true" :width="100" />
            <DxColumn data-field="Odeme_plan_kodu" caption="ÖDEME PLAN KODU" :visible="true" :width="80" />
            <DxColumn data-field="ODEME_VADESI" caption="ÖDEME VADESİ" :visible="true" :width="200" />
            <DxColumn data-field="CARI_GRUP" caption="CARİ GRUP" :visible="true" :width="80" />
            <DxColumn data-field="CARI_KOD" caption="CARİ KOD" :visible="true" :width="120" />
            <DxColumn data-field="CARI_AD" caption="CARİ AD" :visible="true" :width="280" />
            <DxColumn data-field="CARI_ULKE" caption="CARİ ÜLKE" :visible="true" :width="80" />
            <DxColumn data-field="CARI_IL" caption="CARİ İL" :visible="true" :width="100" />
            <DxColumn data-field="CARI_ILCE" caption="CARİ İLÇE" :visible="true" :width="100" />
            <DxColumn data-field="SEVK_ULKE" caption="SEVK ÜLKE" :visible="true" :width="100" />
            <DxColumn data-field="SEVK_IL" caption="SEVK İL" :visible="true" :width="100" />
            <DxColumn data-field="SEVK_ILCE" caption="SEVK İLÇE" :visible="true" :width="100" />
            <DxColumn data-field="SIPARIS_YILI" caption="SİPARİŞ YILI" :visible="true" :width="80" />
            <DxColumn data-field="MUSTERI_NO" caption="MÜŞTERİ NO" :visible="true" :width="100" />
            <DxColumn data-field="SATIR_TIP" caption="SATIR TİP" :visible="true" :width="80" />
            <DxColumn data-field="STOK_HIZMET_ID" caption="STOK ID" :visible="true" :width="90" />
            <DxColumn data-field="STOK_HIZMET_KOD" caption="STOK KODU" :visible="true" :width="100" />
            <DxColumn data-field="STOK_HIZMET_AD" caption="STOK ADI" :visible="true" :width="280" />
            <DxColumn data-field="kategori2" caption="KATEGORİ 2" :visible="true" :width="120" />
            <DxColumn data-field="kategori3" caption="KATEGORİ 3" :visible="true" :width="120" />
            <DxColumn data-field="URUN_GRUBU_OZET" caption="ÜRÜN GRUBU ÖZET" :visible="true" :width="150" />
            <DxColumn data-field="URUN_GRUBU" caption="ÜRÜN GRUBU" :visible="true" :width="150" />
            <DxColumn data-field="BIRIM_KOD"" caption=" BİRİM" :visible="true" :width="80" />
            <DxColumn data-field="MIKTAR" caption="MİKTAR" data-type="number" :visible="true" :width="100" :format="{
              type: 'fixedPoint',
              precision: 1,
              thousandsSeparator: ',',
            }" />
            <DxColumn data-field="GELEN_MIKTAR" caption="GELEN MİKTAR" data-type="number" :visible="true" :width="100"
              :format="{
                type: 'fixedPoint',
                precision: 1,
                thousandsSeparator: ',',
              }" />
            <DxColumn data-field="KALAN_MIKTAR" caption="KALAN MİKTAR" data-type="number" :visible="true" :width="100"
              :format="{
                type: 'fixedPoint',
                precision: 1,
                thousandsSeparator: ',',
              }" />
            <DxColumn data-field="REZERVLI_MIKTAR" caption="REZERVLİ MİKTAR" data-type="number" :visible="true"
              :width="100" :format="{
                type: 'fixedPoint',
                precision: 1,
                thousandsSeparator: ',',
              }" />
            <DxColumn data-field="BIRIM_FIYAT" caption="BİRİM FİYAT" data-type="number" :visible="true" :width="100"
              :format="{
                type: 'fixedPoint',
                precision: 3,
                thousandsSeparator: ',',
              }" />
            <DxColumn data-field="YP_BIRIM_FIYAT" caption="YP BİRİM FİYAT" data-type="number" :visible="true"
              :width="100" :format="{
                type: 'fixedPoint',
                precision: 1,
                thousandsSeparator: ',',
              }" />
            <DxColumn data-field="ISKONTO_TUTAR" caption="ISKONTO TUTAR" data-type="number" :visible="true" :width="100"
              :format="{
                type: 'fixedPoint',
                precision: 1,
                thousandsSeparator: ',',
              }" />
            <DxColumn data-field="YP_ISKONTO_TUTAR" caption="YP ISKONTO TUTAR" data-type="number" :visible="true"
              :width="100" :format="{
                type: 'fixedPoint',
                precision: 1,
                thousandsSeparator: ',',
              }" />
            <DxColumn data-field="NET_BIRIM_FIYAT" caption="NET BİRİM FİYAT" data-type="number" :visible="true"
              :width="100" :format="{
                type: 'fixedPoint',
                precision: 1,
                thousandsSeparator: ',',
              }" />
            <DxColumn data-field="YP_NET_BIRIM_FIYAT" caption="YP NET BİRİM FİYAT" data-type="number" :visible="true"
              :width="100" :format="{
                type: 'fixedPoint',
                precision: 1,
                thousandsSeparator: ',',
              }" />
            <DxColumn data-field="HAREKET_NET_TUTAR" caption="HAREKET NET TUTAR" data-type="number" :visible="true"
              :width="100" :format="{
                type: 'fixedPoint',
                precision: 1,
                thousandsSeparator: ',',
              }" />
            <DxColumn data-field="YP_NET_TUTAR" caption="YP NET TUTAR" data-type="number" :visible="true" :width="100"
              :format="{
                type: 'fixedPoint',
                precision: 1,
                thousandsSeparator: ',',
              }" />
            <DxColumn data-field="PARA_BIRIMI" caption="PARA BİRİMİ" :visible="true" :width="90" />
            <DxColumn data-field="KUR" caption="KUR" data-type="number" :visible="true" :width="100" :format="{
              type: 'fixedPoint',
              precision: 1,
              thousandsSeparator: ',',
            }" />
            <DxColumn data-field="KUR_DOC_DATE" caption="KUR DOC TARİH" data-type="date" :visible="true" :width="150"
              :format="{
                formatter: (date) => {
                  const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                  }).format(new Date(date));

                  return formattedDate.replace(/\//g, '.');
                },
              }" alignment="center" />
            <DxColumn data-field="USD_KUR" caption="USD KUR" data-type="number" :visible="true" :width="100" :format="{
              type: 'fixedPoint',
              precision: 1,
              thousandsSeparator: ',',
            }" />
            <DxColumn data-field="USD_NET_TUTAR" caption="USD NET TUTAR" data-type="number" :visible="true" :width="100"
              :format="{
                type: 'fixedPoint',
                precision: 1,
                thousandsSeparator: ',',
              }" />
            <DxColumn data-field="USD_KDV_TUTAR" caption="USD KDV TUTAR" data-type="number" :visible="true" :width="100"
              :format="{
                type: 'fixedPoint',
                precision: 1,
                thousandsSeparator: ',',
              }" />
            <DxColumn data-field="USD_GENEL_TUTAR" caption="USD GENEL TUTAR" data-type="number" :visible="true"
              :width="100" :format="{
                type: 'fixedPoint',
                precision: 1,
                thousandsSeparator: ',',
              }" />
            <DxColumn data-field="USD_KALAN_NET_TUTAR" caption="USD KALAN NET TUTAR" data-type="number" :visible="true"
              :width="100" :format="{
                type: 'fixedPoint',
                precision: 1,
                thousandsSeparator: ',',
              }" />
            <DxColumn data-field="USD_KALAN_KDV_TUTAR" caption="USD KALAN KDV TUTAR" data-type="number" :visible="true"
              :width="100" :format="{
                type: 'fixedPoint',
                precision: 1,
                thousandsSeparator: ',',
              }" />
            <DxColumn data-field="USD_KALAN_GENEL_TUTAR" caption="USD KALAN GENEL TUTAR" data-type="number"
              :visible="true" :width="100" :format="{
                type: 'fixedPoint',
                precision: 1,
                thousandsSeparator: ',',
              }" />
            <DxColumn data-field="order_d_id" caption="ORDER D ID" data-type="number" :visible="true" :width="80" />
            <DxColumn data-field="order_m_id" caption="ORDER M ID" data-type="number" :visible="true" :width="80" />
            <DxColumn data-field="offer_m_id" caption="OFFER M ID" data-type="number" :visible="true" :width="80" />
            <DxColumn data-field="co_id" caption="CO ID" data-type="number" :visible="true" :width="80" />
            <DxColumn data-field="branch_id" caption="BRANCH ID" data-type="number" :visible="true" :width="80" />
            <DxColumn data-field="entity_id" caption="ENTITY ID" data-type="number" :visible="true" :width="90" />
            <DxColumn data-field="item_name" caption="STOK ADI" :visible="true" :width="180" />
            <DxColumn data-field="satici_id" caption="SATICI ID" data-type="number" :visible="true" :width="80" />
            <DxColumn data-field="sip_olusturan_id" caption="SİP OLUŞTURAN ID" data-type="number" :visible="true"
              :width="80" />
            <DxColumn data-field="sip_olusturan" caption="SİP OLUŞTURAN" :visible="true" :width="150" />
            <DxColumn data-field="sip_degistiren_id" caption="SİP DEĞİŞTİREN ID" data-type="number" :visible="true"
              :width="80" />
            <DxColumn data-field="sip_degistiren" caption="SİP DEĞİŞTİREN" :visible="true" :width="150" />
            <DxColumn data-field="tek_olusturan_id" caption="TEKLİF OLUŞTURAN ID" data-type="number" :visible="true"
              :width="80" />
            <DxColumn data-field="tek_olusturan" caption="TEKLİF OLUŞTURAN" :visible="true" :width="150" />
            <DxColumn data-field="tek_degistiren_id" caption="TEKLİF DEĞİŞTİREN ID" data-type="number" :visible="true"
              :width="80" />
            <DxColumn data-field="tek_degistiren" caption="TEKLİF DEĞİŞTİREN" :visible="true" :width="150" />
            <DxColumn data-field="detay_not1" caption="DETAY NOT 1" :visible="true" :width="150" />
            <DxColumn data-field="detay_not2" caption="DETAY NOT 2" :visible="true" :width="150" />
            <DxColumn data-field="detay_not3" caption="DETAY NOT 3" :visible="true" :width="150" />
            <DxColumn data-field="genel_not1" caption="GENEL NOT 1" :visible="true" :width="150" />
            <DxColumn data-field="genel_not2" caption="GENEL NOT 2" :visible="true" :width="150" />
            <DxColumn data-field="genel_not3" caption="GENEL NOT 3" :visible="true" :width="150" />



            <DxLoadPanel :key="loadingVisible" v-model:visible="loadingVisible" :show-indicator="true" :show-pane="true"
              :shading="true" />
            <DxSelection mode="multiple" select-all-mode="page" show-check-boxes-mode="onClick" />
            <DxGrouping :auto-expand-all="false" />
            <DxGroupPanel :visible="true" />
            <DxHeaderFilter :visible="true" />
            <DxFilterPanel :visible="true" />
            <DxFilterRow :visible="true" />
            <DxSearchPanel :visible="true" :width="240" />
            <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always" />
            <DxSorting mode="multiple" />
            <DxExport :enabled="true" :allow-export-selected-data="false" />
            <DxColumnChooser height="540px" :enabled="true" mode="select" :position="position">
              <DxColumnChooserSearch :enabled="true" />
              <DxColumnChooserSelection :allow-select-all="true" :select-by-click="true" :recursive="true" />
            </DxColumnChooser>

            <DxToolbar>
              <DxItem name="groupPanel" />
              <DxItem location="before" locate-in-menu="auto" template="filtreTarih1" />
              <DxItem location="before" locate-in-menu="auto" template="filtreTarih2" />
              <DxItem location="before" locate-in-menu="auto" template="filtreFirma" />
              <DxItem location="before" locate-in-menu="auto" template="yenileTemplate"
                menu-item-template="menuYenileTemplate" @click="Yenile('filtre')" />
              <DxItem location="after" locate-in-menu="auto" template="filtreTemizleTemplate"
                menu-item-template="menuFiltreTemizleTemplate" @click="FiltreTemizle" />

              <DxItem name="exportButton" />
              <DxItem name="columnChooserButton" />
              <DxItem name="searchPanel" />
            </DxToolbar>

            <DxSummary>
              <DxGroupItem :align-by-column="true" column="BELGE_TARIHI" summary-type="count" display-format="{0} adet"
                :alignment="right" />
              <DxTotalItem :align-by-column="true" column="BELGE_TARIHI" summary-type="count" display-format="{0} adet"
                :alignment="right" />
            </DxSummary>

            <template #filtreTarih1>
              <div style="margin-top: -10px;">
                <DxDateBox label="Başlangıç Tarihi" width="150" label-mode="floating" v-model:value="filterValue"
                  type="date" />
              </div>
            </template>

            <template #filtreTarih2>
              <div style="margin-top: -10px; margin-left: 5px;">
                <DxDateBox label="Bitiş Tarihi" width="150" label-mode="floating" v-model:value="filterValue1"
                  type="date" />
              </div>
            </template>

            <template #filtreFirma>
              <div style="inline-size: 160px; margin-top: -10px; margin-left: 5px;">
                <DxSelectBox :data-source="firmalar" v-model:value="firma" :show-clear-button="true" label="Firma"
                  label-mode="floating" display-expr="FIRMA" value-expr="FIRMA" style="inline-size: 100%"
                  search-mode="contains" search-expr="FIRMA" :search-timeout="200" :search-enabled="true" />
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

          </DxDataGrid>
        </div>
      </VCol>
    </VCardText>
  </VCard>

  <DxPopup v-model:visible="popupTeslimTarihVisible" :width="400" :height="630" :hide-on-outside-click="false"
    :show-close-button="true" title="Teslim Tarihini Giriniz">
    <template #content>
      <VCol cols="12">
        <div class="popup-center">
          <DxCalendar ref="calendarRef" v-model:value="teslimTarihi" :show-week-numbers="true"
            :select-week-on-click="true" selection-mode="single" :show-today-button="true" />
        </div>
      </VCol>
      <VCol cols="12">
        <div class="popup-center mt-0 pa-0">
          <DxTextArea :height="100" :width="300" :max-length="255" label="Açıklama" @input="onMessageInput"
            v-model="tarihNotu" :auto-resize-enabled="false" aria-required="true" />
        </div>
      </VCol>
    </template>
    <DxToolbarItem v-if="girildi === true" widget="dxButton" toolbar="bottom" location="center" :options="kaydetOptions"
      @click="TeslimTarihKaydet()" />
    <DxToolbarItem widget="dxButton" toolbar="bottom" location="center" :options="vazgecOptions" />
  </DxPopup>

  <DxPopup v-model:visible="popupNotGirVisible" :width="600" :height="690" :hide-on-outside-click="false"
    :show-close-button="true" title="Notlarınızı Giriniz">
    <div class="text-center">
      <h3> {{ belgeNo }}</h3>
      <h3> {{ cariAd }}</h3>
      <p> {{ stokAdi }}</p>
      <hr>
    </div>
    <VCol cols="12">
      <div class="popup-center mt-0 pa-0">
        <DxTextArea :height="80" :width="500" :max-length="999" label="Not 1" v-model="not1"
          :auto-resize-enabled="true" />
      </div>
    </VCol>
    <VCol cols="12">
      <div class="popup-center mt-0 pa-0">
        <DxTextArea :height="80" :width="500" :max-length="999" label="Not 2" v-model="not2"
          :auto-resize-enabled="true" />
      </div>
    </VCol>
    <VCol cols="12">
      <div class="popup-center mt-0 pa-0">
        <DxTextArea :height="80" :width="500" :max-length="999" label="Not 3" v-model="not3"
          :auto-resize-enabled="true" />
      </div>
    </VCol>

    <VCol cols="12">
      <div class="popup-center">
        <DxSwitch v-model:value="notGuncelle" />
        <div v-if="notGuncelle">
          <h3 class="ms-2">Tüm Sipariş </h3>
        </div>
        <div v-else>
          <h3 class="ms-2">Seçili Kayıt </h3>
        </div>
      </div>
    </VCol>


    <DxToolbarItem widget="dxButton" toolbar="bottom" location="center" :options="kaydetOptions" @click="NotKaydet()" />
    <DxToolbarItem widget="dxButton" toolbar="bottom" location="center" :options="vazgecOptions" />
  </DxPopup>
</template>


<script setup lang="ts">
import axios from 'axios'
import type { DxDataGridTypes } from 'devextreme-vue/data-grid'
import DxCalendar from 'devextreme-vue/calendar';
import {
  DxColumn,
  DxDataGrid,
  DxExport,
  DxFilterPanel,
  DxFilterRow,
  DxGroupItem,
  DxGroupPanel,
  DxHeaderFilter,
  DxScrolling,
  DxSearchPanel,
  DxSorting,
  DxSelection,
  DxSummary,
  DxToolbar,
  DxTotalItem,
  DxGrouping,
  DxColumnChooser,
  DxColumnChooserSearch,
  DxColumnChooserSelection
} from 'devextreme-vue/data-grid'
import { DxButton } from 'devextreme-vue/button'
import DxSelectBox from 'devextreme-vue/select-box'
import { DxLoadPanel } from 'devextreme-vue/load-panel'
import { exportDataGrid } from 'devextreme/excel_exporter'
import { DxItem } from 'devextreme-vue/tabs'
import { ref } from 'vue'
import { Workbook } from 'exceljs'
import { saveAs } from 'file-saver-es'
import DxContextMenu, { DxContextMenuTypes } from 'devextreme-vue/context-menu'
import { usePageTitleStore } from '@/stores/pageTitle'
import notify from "devextreme/ui/notify";
import DxDateBox from "devextreme-vue/date-box";
import { DxPopup, DxToolbarItem } from 'devextreme-vue/popup'
import DxTextArea from 'devextreme-vue/text-area';
import { DxSwitch } from 'devextreme-vue/switch';

const formatDate = date => {
  if (!date)
    return null
  const d = new Date(date)
  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')

  return `${year}-${month}-${day}`
}

document.title = "OFT - Satınalma Siparişleri";

const pageTitleStore = usePageTitleStore()
const popupTeslimTarihVisible = ref(false)
const popupNotGirVisible = ref(false)
const teslimTarihi = ref(new Date())
const tarihNotu = ref('')
const not1 = ref('')
const not2 = ref('')
const not3 = ref('')
const notGuncelle = ref(false)
const belgeNo = ref('')
const cariAd = ref('')
const stokAdi = ref('')

const loadingVisible = ref<boolean>(false)
const gridData = ref([])
const firmalar = ref([])
const firma = ref('')
const gridKey = ref(Date.now())
const selectedRows = ref([])

const userData = useCookie<any>('userData')
const dataGridRef = ref<DxDataGrid | null>(null)
const initialFilter = ref<any>(null)
const goster = ref(true)
const totalRecord = ref(0)
const stokId = ref(0)
const expandAll = ref(false)
const now = new Date()
const firstDayOfWeek = new Date(now)
firstDayOfWeek.setDate(now.getDate() - now.getDay() + 1) // Pazartesi
const lastDayOfWeek = new Date(now)
lastDayOfWeek.setDate(now.getDate() - now.getDay() + 7) // Pazar
const initialDates = [firstDayOfWeek, lastDayOfWeek]
const selectedDateRange = ref(initialDates)
const filterValue = ref(firstDayOfWeek) // Formatlanmış başlangıç tarihi
const filterValue1 = ref(lastDayOfWeek) // Formatlanmış bitiş tarihi
const selectedRow = ref<any | null>(null);

const Yenile = async (kapsam: string) => {
  gridData.value = [];
  await getSatinalmalar(kapsam)
}

const getSatinalmalar = async (kapsam: string) => {
  selectedDateRange.value = [filterValue.value, filterValue1.value]

  filterValue.value = formatDate(selectedDateRange.value[0])
  filterValue1.value = formatDate(selectedDateRange.value[1])

  loadingVisible.value = true
  try {
    const response = await axios.get('/api/satinalma-siparisleri', {
      params: {
        filterValue: filterValue.value,
        filterValue1: filterValue1.value,
        itemID: null,
        firma: firma.value,
        kapsam: kapsam,
      },
    })
    gridData.value = response.data.satinalma
  } catch (error) {
    console.error('Veri çekilirken hata oluştu: ', error)
  } finally {
    loadingVisible.value = false
  }
}

const onSelectionChanged = (e: any) => {
  selectedRows.value = e.selectedRowsData // Seçilen satırların tüm verileri
}

const getFirmalar = async () => {
  try {
    const response = await axios.get('/api/firmalar')
    firmalar.value = response.data.firmalar
  } catch (error) {
    console.error('Veri çekilirken hata oluştu: ', error)
  } finally {
    loadingVisible.value = false
  }
}

const girildi = ref(false);

const onMessageInput = (e: any) => {
  const value = e.event?.target?.value
  if (value !== undefined) {
    girildi.value = value.length > 0
  } else {
    console.warn('Değer bulunamadı:', e)
  }
}

const kaydetOptions = {
  width: 100,
  type: 'success',
  text: 'Kaydet',
  stylingMode: 'contained',
}

const vazgecOptions = {
  width: 100,
  type: 'normal',
  text: 'Vazgeç',
  stylingMode: 'contained',
  onClick: () => {
    popupTeslimTarihVisible.value = false
    popupNotGirVisible.value = false
  },
}

onMounted(async () => {
  pageTitleStore.setTitle('Satınalma Siparişleri')
  getFirmalar();
  loadGridState()
  const currentWeek = getCurrentWeek()
  axios
    .post("/api/log-kayit", {
      userId: userData.value.id,
      sayfa: 'Satınalma Siparişleri',
      eylem: 'Yükleme',
    });
})

const loadGridState = () => {
  const savedState = localStorage.getItem(`${userData.value.id}_satinalmaspr`)
  if (savedState)
    dataGridRef.value?.instance.state(JSON.parse(savedState))

  // console.log("Grid durumu yüklendi:", savedState);
}

const saveGridState = () => {
  const state = dataGridRef.value?.instance.state()

  localStorage.setItem(`${userData.value.id}_satinalmaspr`, JSON.stringify(state))

  // console.log("Grid durumu kaydedildi:", state);
}

const onStateResetClick = () => {
  localStorage.removeItem(`${userData.value.id}_satinalmaspr`)
  dataGridRef.value!.instance!.state(null)
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

const FiltreTemizle = () => {
  dataGridRef.value?.instance.clearFilter()
}

const onContextMenuPreparing = (e: any) => {
  if (e.rowIndex === -1)
    return
  const data = e.row!.data!
  selectedRow.value = data
}

const onExporting = (e: DxDataGridTypes.ExportingEvent) => {
  const workbook = new Workbook()
  const worksheet = workbook.addWorksheet('SatinalmaSiparisleri')

  exportDataGrid({
    component: e.component,
    worksheet,
    autoFilterEnabled: true,
  }).then(() => {
    workbook.xlsx.writeBuffer().then(buffer => {
      saveAs(
        new Blob([buffer], { type: 'application/octet-stream' }),
        'SatinalmaSiparisleri.xlsx',
      )
    })
  })

  e.cancel = true
}

const formatNumber = number => {
  return new Intl.NumberFormat('tr-TR', { maximumFractionDigits: 0 }).format(
    number,
  )
}

const NotGir = () => {
  if (selectedRows.value.length === 0) {
    notify('Önce kayıt seçmelisiniz...', 'error', 2000)
    return
  }
  notGuncelle.value = false
  not1.value = selectedRows.value[0].detay_not1
  not2.value = selectedRows.value[0].detay_not2
  not3.value = selectedRows.value[0].detay_not3
  belgeNo.value = selectedRows.value[0].BELGE_NO
  cariAd.value = selectedRows.value[0].CARI_AD
  stokAdi.value = selectedRows.value[0].STOK_HIZMET_AD
  popupNotGirVisible.value = true
}

const NotKaydet = async () => {
  loadingVisible.value = true

  const updateData = selectedRows.value.map((row: any) => {
    return {
      sayfa: 'Müşteri Siparişleri',
      order_d_id: row.order_d_id,
      order_m_id: row.order_m_id,
      not1: not1.value,
      not2: not2.value,
      not3: not3.value,
      notGuncelle: notGuncelle.value,
    }
  })
  const userID = userData.value.id
  try {

    // Grid verisini güncelleme
    updateData.forEach((updatedRow) => {
      const rowsToUpdate = notGuncelle.value
        ? gridData.value.filter((row: any) => row.order_m_id === updatedRow.order_m_id)
        : gridData.value.filter((row: any) => row.order_d_id === updatedRow.order_d_id);

      rowsToUpdate.forEach((row) => {
        row.detay_not1 = updatedRow.not1;
        row.detay_not2 = updatedRow.not2;
        row.detay_not3 = updatedRow.not3;
      });
    });

    console.log('index:', updateData)
    const response = await axios.post('/api/notgir', { updateData, userID })

    selectedRows.value = []
    clearSelection()
    notify('Kayıt başarıyla güncellendi...', 'success', 1500)
  }
  catch (error) {
    console.error('Güncelleme sırasında hata oluştu:', error)
  }
  popupNotGirVisible.value = false
  loadingVisible.value = false

}

const TeslimTarihDegistir = () => {
  if (selectedRows.value.length === 0) {
    notify('Önce iş emrini seçmelisiniz...', 'error', 1500)
    return
  }
  teslimTarihi.value = new Date(selectedRows.value[0].teslim_tarihi)
  tarihNotu.value = selectedRows.value[0].teslim_tarihi_not
  popupTeslimTarihVisible.value = true
}

const TeslimTarihKaydet = async () => {
  const updateData = selectedRows.value.map((row: any) => {
    return {
      sayfa: 'Satınalma Siparişleri',
      order_d_id: row.order_d_id,
      order_m_id: row.order_m_id,
      tarihNotu: tarihNotu.value,
      teslimTarih: `${teslimTarihi.value.getFullYear()}-${(teslimTarihi.value.getMonth() + 1)
        .toString()
        .padStart(2, '0')}-${teslimTarihi.value.getDate().toString().padStart(2, '0')}`,
    }
  })
  const userID = userData.value.id
  try {
    const response = await axios.post('/api/teslimtarihidegistir', { updateData, userID })

    // Grid verisini güncelleme
    updateData.forEach((updatedRow) => {
      const index = gridData.value.findIndex((row: any) => row.order_d_id === updatedRow.order_d_id)
      if (index !== -1) {
        gridData.value[index].teslim_tarihi = updatedRow.teslimTarih
        gridData.value[index].teslim_tarihi_not = updatedRow.tarihNotu
      }
    })

    selectedRows.value = []
    clearSelection()
    notify('Kayıt başarıyla güncellendi...', 'success', 1500)
  }
  catch (error) {
    console.error('Güncelleme sırasında hata oluştu:', error)
  }
  popupTeslimTarihVisible.value = false
}


const clearSelection = () => {
  const dataGrid = dataGridRef.value!.instance!
  dataGrid.clearSelection()
}

const menuItems = [
  { text: 'Filtre Yenile' },
  { text: 'Açıklar' },
  { text: 'Kapalılar' },
  { text: 'Tümünü Yenile' },
  { text: 'Teslim Tarihi Değiştir' },
  { text: 'Not Gir' },
  { text: 'Düzen Yükle' },
  { text: 'Düzen Kaydet' },
  { text: 'Düzen Sıfırla' },
]

function itemClick({ itemData }: DxContextMenuTypes.ItemClickEvent) {
  if (!itemData?.items) {
    switch (itemData?.text) {
      case 'Filtre Yenile':
        Yenile('filtre')
        break;
      case 'Açıklar':
        Yenile('aciklar')
        break;
      case 'Kapalılar':
        Yenile('kapalilar')
        break;
      case 'Tümünü Yenile':
        Yenile('tumu')
        break;
      case 'Not Gir':
        NotGir()
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
      case 'Teslim Tarihi Değiştir':
        TeslimTarihDegistir()
        break;
      default:
        break;
    }
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

.popup-center {
  display: flex;
  align-items: center;

  /* Dikeyde ortalama */
  justify-content: center;

  /* Yatayda ortalama */
  block-size: 100%;

  /* Popup içeriğinin tamamını kaplasın */
}

.full-width-content {
  width: 100%;
  margin-top: 10px;
}

.full-width-content .textarea-wrapper>.dx-widget {
  margin-bottom: 20px;
}

.textarea-wrapper {
  padding: 0 20px;
}
</style>

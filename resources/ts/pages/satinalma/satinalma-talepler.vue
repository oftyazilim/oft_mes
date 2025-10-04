<template>
  <VCard class="mt-0 pa-0">
    <VCardText class="mt-0 pa-2 ">
      <VCol cols="12" class="mt-2 pa-1 pe-2 pt-0">
        <div id="liste" style="margin-block: -10px 10px;">
          <DxContextMenu :data-source="menuItems" :width="200" target="#grid" @item-click="itemClick" />

          <DxDataGrid id="grid" ref="dataGridRef" :key="gridKey" class="grid" :data-source="gridData"
            key-expr="talep_no" :show-borders="true" focused-row-enabled="true" row-alternation-enabled="true"
            :min-width="200" allow-column-reordering="true" column-auto-width="true" allow-column-resizing="true"
            @contextMenuPreparing="onContextMenuPreparing" column-resizing-mode="widget"
            :auto-navigate-to-focused-row="true" width="100%" @exporting="onExporting">

            <DxColumn data-field="talep_tarihi" caption="TALEP TARİHİ" data-type="date" :width="150" :visible="true"
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
            <DxColumn data-field="onay_tarihi" caption="ONAY TARİHİ" data-type="date" :width="150" :visible="true"
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
            <DxColumn data-field="talep_no" caption="TALEP NO" :visible="true" :width="120" />
            <DxColumn data-field="durumu" caption="DURUMU" :visible="true" :width="100" />
            <DxColumn data-field="ONAY_DURUMU" caption="ONAY DURUMU" :visible="true" :width="120" />
            <DxColumn data-field="kaynak" caption="KAYNAK" :visible="true" :width="150" />
            <DxColumn data-field="tipi" caption="TİPİ" :visible="true" :width="90" />
            <DxColumn data-field="item_id" caption="STOK ID" :visible="true" :width="90" />
            <DxColumn data-field="item_code" caption="STOK KODU" :visible="true" :width="130" />
            <DxColumn data-field="item_name" caption="STOK ADI" :visible="true" :width="280" />
            <DxColumn data-field="miktar" caption="MİKTAR" data-type="number" :visible="true" :width="100" :format="{
              type: 'fixedPoint',
              precision: 1,
              thousandsSeparator: ',',
            }" />
            <DxColumn data-field="qty_ordered" data-type="number" :visible="true" :width="100" :format="{
              type: 'fixedPoint',
              precision: 1,
              thousandsSeparator: ',',
            }" />
            <DxColumn data-field="qty_free_prm" data-type="number" :visible="true" :width="100" :format="{
              type: 'fixedPoint',
              precision: 1,
              thousandsSeparator: ',',
            }" />
            <DxColumn data-field="qty_free_sec" data-type="number" :visible="true" :width="100" :format="{
              type: 'fixedPoint',
              precision: 1,
              thousandsSeparator: ',',
            }" />
            <DxColumn data-field="co_id" data-type="number" :visible="true" :width="100" :format="{
              type: 'fixedPoint',
              precision: 1,
              thousandsSeparator: ',',
            }" />
            <DxColumn data-field="branch_id" data-type="number" :visible="true" :width="100" :format="{
              type: 'fixedPoint',
              precision: 1,
              thousandsSeparator: ',',
            }" />
            <DxColumn data-field="note_1" :visible="true" :width="150" />
            <DxColumn data-field="note_2" :visible="true" :width="150" />
            <DxColumn data-field="item_line_type" data-type="number" :visible="true" :width="150" />
            <DxColumn data-field="psm_request_status" data-type="number" :visible="true" :width="150" />
            <DxColumn data-field="request_status" data-type="number" :visible="true" :width="150" />




            <DxLoadPanel :key="loadingVisible" v-model:visible="loadingVisible" :show-indicator="true" :show-pane="true"
              :shading="true" />
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
              <DxItem location="before" locate-in-menu="auto" template="tarih"
                menu-item-template="menuYenileTarihTemplate" @click="YenileTarih" />
              <DxItem location="before" locate-in-menu="auto" template="tumu"
                menu-item-template="menuYenileTumuTemplate" @click="YenileTumu" />
              <DxItem location="after" locate-in-menu="auto" template="filtreTemizleTemplate"
                menu-item-template="menuFiltreTemizleTemplate" @click="FiltreTemizle" />

              <DxItem name="exportButton" />
              <DxItem name="columnChooserButton" />
              <DxItem name="searchPanel" />
            </DxToolbar>

            <DxSummary>
              <DxGroupItem :align-by-column="true" column="talep_tarihi" summary-type="count" display-format="{0} adet"
                :alignment="right" />
              <DxTotalItem :align-by-column="true" column="talep_tarihi" summary-type="count" display-format="{0} adet"
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

            <template #tumu>
              <DxButton id="sayim" icon="refresh" text="Tümü" styling-mode="text" hint="Tümünü Yenile" />
            </template>
            <template #menuYenileTumuTemplate>
              <div style="display: flex; align-items: center; margin-inline-start: 13px;">
                <i class="dx-icon dx-icon-refresh"></i>
                <span style="margin-inline-start: 8px;">Tümü</span>
              </div>
            </template>

            <template #tarih>
              <DxButton id="sayim" icon="refresh" text="Tarih" styling-mode="text" hint="Tarih Aralığını Yenile" />
            </template>
            <template #menuYenileTarihTemplate>
              <div style="display: flex; align-items: center; margin-inline-start: 13px;">
                <i class="dx-icon dx-icon-refresh"></i>
                <span style="margin-inline-start: 8px;">Tarih</span>
              </div>
            </template>

          </DxDataGrid>
        </div>
      </VCol>
    </VCardText>
  </VCard>

</template>


<script setup lang="ts">
import { usePageTitleStore } from '@/stores/pageTitle'
import axios from 'axios'
import { DxButton } from 'devextreme-vue/button'
import DxContextMenu, { DxContextMenuTypes } from 'devextreme-vue/context-menu'
import type { DxDataGridTypes } from 'devextreme-vue/data-grid'
import {
  DxColumn,
  DxColumnChooser,
  DxColumnChooserSearch,
  DxColumnChooserSelection,
  DxDataGrid,
  DxExport,
  DxFilterPanel,
  DxFilterRow,
  DxGrouping,
  DxGroupItem,
  DxGroupPanel,
  DxHeaderFilter,
  DxScrolling,
  DxSearchPanel,
  DxSorting,
  DxSummary,
  DxToolbar,
  DxTotalItem
} from 'devextreme-vue/data-grid'
import DxDateBox from "devextreme-vue/date-box"
import { DxLoadPanel } from 'devextreme-vue/load-panel'
import { DxItem } from 'devextreme-vue/tabs'
import { exportDataGrid } from 'devextreme/excel_exporter'
import { Workbook } from 'exceljs'
import { saveAs } from 'file-saver-es'
import { ref } from 'vue'

const formatDate = date => {
  if (!date)
    return null
  const d = new Date(date)
  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')

  return `${year}-${month}-${day}`
}

const pageTitleStore = usePageTitleStore()

const loadingVisible = ref<boolean>(false)
const gridData = ref([])
const firmalar = ref([])
const firma = ref('')
const gridKey = ref(Date.now())

const userData = useCookie<any>('userData')
const dataGridRef = ref<DxDataGrid | null>(null)
const initialFilter = ref<any>(null)
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
const tarihAralik = ref(true);

document.title = "OFT - Talepler";

const YenileTumu = async () => {
  tarihAralik.value = false
  gridData.value = [];
  await getTalepler()
}

const YenileTarih = async () => {
  tarihAralik.value = true
  gridData.value = [];
  await getTalepler()
}

const getTalepler = async () => {
  selectedDateRange.value = [filterValue.value, filterValue1.value]

  // Ensure filterValue and filterValue1 are always Date objects
  if (selectedDateRange.value[0]) filterValue.value = new Date(selectedDateRange.value[0])
  if (selectedDateRange.value[1]) filterValue1.value = new Date(selectedDateRange.value[1])

  // loadingVisible.value = true
  try {
    const response = await axios.get('/api/satinalma-talepleri', {
      params: {
        filterValue: filterValue.value,
        filterValue1: filterValue1.value,
        itemID: null,
        firma: null,
        tarihAralik: tarihAralik.value,
      },
    })
    gridData.value = response.data.talepler
  } catch (error) {
    console.error('Veri çekilirken hata oluştu: ', error)
  } finally {
    loadingVisible.value = false
  }
}

onMounted(async () => {
  pageTitleStore.setTitle('Satınalma Talepleri')
  loadGridState()
  const currentWeek = getCurrentWeek()
})

const loadGridState = () => {
  const savedState = localStorage.getItem(`${userData.value.id}_satinalmatlp`)
  if (savedState)
    dataGridRef.value?.instance.state(JSON.parse(savedState))

  // console.log("Grid durumu yüklendi:", savedState);
}

const saveGridState = () => {
  const state = dataGridRef.value?.instance.state()

  localStorage.setItem(`${userData.value.id}_satinalmatlp`, JSON.stringify(state))

  // console.log("Grid durumu kaydedildi:", state);
}

const onStateResetClick = () => {
  localStorage.removeItem(`${userData.value.id}_satinalmatlp`)
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
  const worksheet = workbook.addWorksheet('SatinalmaTalepleri')

  exportDataGrid({
    component: e.component,
    worksheet,
    autoFilterEnabled: true,
  }).then(() => {
    workbook.xlsx.writeBuffer().then(buffer => {
      saveAs(
        new Blob([buffer], { type: 'application/octet-stream' }),
        'SatinalmaTalepleri.xlsx',
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

const menuItems = [
  // { text: 'Yenile' },
  { text: 'Düzen Yükle' },
  { text: 'Düzen Kaydet' },
  { text: 'Düzen Sıfırla' },
  // { text: 'Çıktı Al' },
]

function itemClick({ itemData }: DxContextMenuTypes.ItemClickEvent) {
  if (!itemData?.items) {
    switch (itemData?.text) {
      // case 'Yenile':
      //   YenileMalzemeler()
      //   break;
      case 'Düzen Yükle':
        loadGridState()
        break;
      case 'Düzen Kaydet':
        saveGridState()
        break;
      case 'Düzen Sıfırla':
        onStateResetClick()
        break;
      // case 'Çıktı Al':
      //   CiktiAl()
      //   break;
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
</style>

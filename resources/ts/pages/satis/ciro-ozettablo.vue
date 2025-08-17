<template>
  <VCard class="mt-0 pa-0 pt-2">
    <div>
      <VCardTitle>
        <div class="demo-space-x">
          <DxDateBox label="Başlangıç Tarihi" width="150" label-mode="floating" v-model:value="filterValue"
            type="date" />
          <DxDateBox label="Bitiş Tarihi" width="150" label-mode="floating" v-model:value="filterValue1" type="date" />
          <DxSelectBox :items="durum" v-model:value="acikKapali" :show-clear-button="true" label="Açık-Kapalı"
            label-mode="floating" width="140" />
          <DxButton class="mt-1" icon="refresh" text="Yenile" @click="Yenile" />
          <DxButton class="mt-1" icon="undo" text="Düzen Sıfırla" @click="DuzenReset()" />
          <VSwitch v-model="chartGoster" label="Grafik Göster" value="chartGoster" />
        </div>
      </VCardTitle>
      <div class="pa-2" v-show="chartGoster">
        <div class="long-title">
          <h3>Ciro Tablosu</h3>
        </div>
        <DxChart ref="chart">
          <DxTooltip :enabled="true" :customize-tooltip="customizeTooltip" />
          <DxAdaptiveLayout :width="450" />
          <DxSize :height="320" />
          <DxCommonSeriesSettings type="bar" />
        </DxChart>
      </div>

      <DxPivotGrid class="mt-0 pt-0" id="pivotgrid" ref="grid" :data-source="dataSource"
        :allow-sorting-by-summary="true" :allow-filtering="true" :show-borders="true" :show-column-grand-totals="true"
        :show-row-grand-totals="true" :show-row-totals="true" :show-column-totals="true" @exporting="onExporting"
        @content-ready="customizeCell" :on-context-menu-preparing="onContextMenuPreparing" @cell-click="onCellClick">

        <DxHeaderFilter :show-relevant-values="false" :width="300" :height="400">
          <DxSearch :enabled="true" />
        </DxHeaderFilter>
        <DxLoadPanel :key="loadingVisible" v-model:visible="loadingVisible" :show-indicator="true" :show-pane="true"
          :shading="true" />
        <DxFieldChooser :allow-search="true" />
        <DxFieldPanel :show-data-fields="true" :show-row-fields="true" :show-column-fields="true"
          :show-filter-fields="true" :allow-field-dragging="true" :visible="true" />
        <DxExport :enabled="true" />
        <DxStateStoring :enabled="true" type="localStorage" storage-key="dx-widget-gallery-pivotgrid-ciro-storing" />

        <template #fieldChooserButton>
          <DxButton icon="plus" text="Özel Buton" @click="Yenile()" />
        </template>

      </DxPivotGrid>
    </div>
    <div class="export-options">
      <div class="caption">Dışa Aktarma Seçenekleri</div>
      <div class="options">
        <DxCheckBox id="export-data-field-headers" :value="exportDataFieldHeaders"
          :on-value-changed="onExportDataFieldHeadersChanged" text="Alan Başlıklarını Aktar" />
        <DxCheckBox id="export-row-field-headers" :value="exportRowFieldHeaders"
          :on-value-changed="onExportRowFieldHeadersChanged" text="Satır Alan Başlıklarını Dışa Aktar" />
        <DxCheckBox id="export-column-field-headers" :value="exportColumnFieldHeaders"
          :on-value-changed="onExportColumnFieldHeadersChanged" text="Sütun Alan Başlıklarını Dışa Aktar" />
        <DxCheckBox id="export-filter-field-headers" :value="exportFilterFieldHeaders"
          :on-value-changed="onExportFilterFieldHeadersChanged" text="Filtre Alan Başlıklarını Dışa Aktar" />
      </div>
    </div>
  </VCard>

  <DxPopup :width="1500" :height="900" :title="popupTitle" v-model:visible="popupVisible" @shown="onShown"
    :show-close-button="true">

    <DxDataGrid id="grid" :ref="dataGridRef" :data-source="drillDownDataSource" key-expr="satir_id" :show-borders="true"
      :row-alternation-enabled="false" :min-width="200" :allow-column-reordering="true" :column-auto-width="false"
      :allow-column-resizing="true" column-resizing-mode="widget" @cell-prepared="onCellPrepared">

      <!-- <DxColumn type="selection" :fixed="true" fixedPosition="left" /> -->
      <DxColumn data-field="id" caption="ID" :visible="false" :min-width="90" />
      <DxColumn data-field="haftax" caption="HFT" data-type="string" :visible="false" :width="20" />
      <DxColumn data-field="OPERASYON" caption="OPERASYON" :visible="operasyon" :width="120" />
      <DxColumn data-field="siparis_belge_no" caption="SİPARİŞ NO" :width="90" :visible="true" :allow-sorting="false" />
      <DxColumn data-field="cari_ad" caption="MÜŞTERİ" :visible="true" :min-width="140" :allow-sorting="false" />
      <DxColumn data-field="stok_kodu" caption="STOK KODU" :visible="true" :width="120" :allow-sorting="false" />
      <DxColumn data-field="stok_adi" caption="STOK ADI" :min-width="200" :allow-sorting="false" />
      <DxColumn data-field="isemri_no" caption="İŞ EMRİ NO" :width="120" :allow-sorting="false" />
      <DxColumn data-field="teslim_tarihi" caption="TESLİM TARİHİ" data-type="date" :width="140" :visible="operasyon"
        :format="{
          formatter: (date) => {
            const formattedDate = new Intl.DateTimeFormat('tr-TR', {
              year: 'numeric',
              month: '2-digit',
              day: '2-digit',
            }).format(new Date(date));
            return formattedDate.replace(/\//g, '.');
          },
        }" :allow-sorting="false" />
      <DxColumn data-field="kalan_miktar" caption="KALAN MİKTAR" data-type="number" :width="100" :visible="true"
        :allow-sorting="false" :format="{
          type: 'fixedPoint',
          precision: 1,
          thousandsSeparator: ',',
        }" />
      <DxColumn data-field="isemri_miktari" caption="İŞ EMRİ MİKTARI" data-type="number" :width="90" :visible="true"
        :format="{
          type: 'fixedPoint',
          precision: 1,
          thousandsSeparator: ',',
        }" />
      <DxColumn data-field="operasyon_hazirlik_suresi" caption="HAZIRLIK SÜRESİ" data-type="number" :width="90"
        :visible="true" :format="{
          type: 'fixedPoint',
          precision: 1,
          thousandsSeparator: ',',
        }" />
      <DxColumn data-field="operasyon_suresi" caption="OPERASYON SÜRESİ" data-type="number" :width="90" :visible="true"
        :format="{
          type: 'fixedPoint',
          precision: 1,
          thousandsSeparator: ',',
        }" :allow-sorting="false" />
      <DxColumn data-field="satir_id" caption="SATIR ID" :visible="false" :min-width="90" :allow-sorting="false" />

      <DxHeaderFilter :visible="true" />
      <DxFilterRow :visible="true" />
      <DxSearchPanel :visible="true" :width="240" />
      <DxSorting mode="multiple" />
    </DxDataGrid>
  </DxPopup>

</template>


<script setup lang="ts">
import axios from 'axios';
import {
  DxAdaptiveLayout,
  DxChart,
  DxCommonSeriesSettings,
  DxSize,
  DxTooltip,
} from 'devextreme-vue/chart';
import DxCheckBox from 'devextreme-vue/check-box';
import { DxColumn, DxDataGrid, DxFilterRow, DxSearchPanel, DxSorting } from 'devextreme-vue/data-grid';
import { DxLoadPanel } from 'devextreme-vue/load-panel';
import {
  DxExport,
  DxFieldChooser,
  DxFieldPanel,
  DxHeaderFilter,
  DxPivotGrid,
  DxPivotGridTypes,
  DxSearch,
  DxStateStoring,
} from 'devextreme-vue/pivot-grid';
import { DxPopup } from 'devextreme-vue/popup';
import { exportPivotGrid } from 'devextreme/excel_exporter';
import { Workbook } from 'exceljs';
import { saveAs } from 'file-saver-es';
import { computed, onMounted, ref, watch } from 'vue';
import DxToolbar, { DxItem } from 'devextreme-vue/toolbar';
import DxButton, { DxButtonTypes } from 'devextreme-vue/button';
import DxDateBox from "devextreme-vue/date-box";
import DxSelectBox from 'devextreme-vue/select-box'
import { usePageTitleStore } from "@/stores/pageTitle";

const chart = ref<DxChart>();
const dateRange = ref<string | null>(null);
const pageTitleStore = usePageTitleStore();

const grid = ref<DxPivotGrid>();
const gridData = ref([]);
const dataGridRef = ref<DxDataGrid>();
const exportDataFieldHeaders = ref(false);
const exportRowFieldHeaders = ref(false);
const exportColumnFieldHeaders = ref(false);
const exportFilterFieldHeaders = ref(false);
const drillDownDataSource = ref<DataSource>(null);
const popupTitle = ref('');
const popupVisible = ref(false);
const loadingVisible = ref<boolean>(false)
const chartGoster = ref<boolean>(false)
const acikKapali = ref('')
const durum = ref(["Açık", "Kapalı"])
const now = new Date()
const firstDayOfWeek = new Date(now)
firstDayOfWeek.setDate(now.getDate() - now.getDay() + 1) // Pazartesi
const lastDayOfWeek = new Date(now)
lastDayOfWeek.setDate(now.getDate() - now.getDay() + 7) // Pazar
const initialDates = [firstDayOfWeek, lastDayOfWeek]
const selectedDateRange = ref(initialDates)
const filterValue = ref(firstDayOfWeek) // Formatlanmış başlangıç tarihi
const filterValue1 = ref(lastDayOfWeek) // Formatlanmış bitiş tarihi

document.title = "OFT - Cirolar Özet Tablo";

function onCellClick(e: DxPivotGridTypes.CellClickEvent) {
  return;
  if (e.area === 'data') {
    const pivotGridDataSource = e.component.getDataSource();
    const rowPathLength = e.cell.rowPath.length;
    const rowPathName = e.cell.rowPath[rowPathLength - 1];
    const colPathLength = e.cell.columnPath.length;
    const colPathName = e.cell.columnPath[colPathLength - 1]; // En son sütun adı

    drillDownDataSource.value = pivotGridDataSource.createDrillDownDataSource(e.cell);
    popupTitle.value = `${rowPathName || 'Toplam'} Detay Bilgi (${colPathName})`;
    popupVisible.value = !popupVisible.value;
  }
}

const Yenile = async () => {
  await getData();
  grid.value?.instance?.getDataSource()?.reload();
};

function onShown() {
  dataGridRef.value?.instance?.updateDimensions();
}

// ⚡ dataSource REAKTİF HALE GETİRİLDİ
const dataSource = computed(() => ({
  fields: [
    {
      caption: 'CARİ ADI',
      width: 120,
      dataField: 'CARI_ADI',
      area: 'filter',
      filterType: 'include',
    },
    {
      caption: 'FATURA NO',
      width: 70,
      dataField: 'fatura_no',
      area: 'filter',
      filterType: 'include',
    },
    {
      caption: 'FATURA TARİHİ',
      width: 400,
      dataField: 'fatura_tarih',
      area: 'filter',
      filterType: 'include',
    }, {
      caption: 'FATURA TARİHİ (YIL)',
      area: 'row',
      dataField: 'fatura_tarih',
      groupInterval: 'year',
      dataType: 'date',
      filterType: 'include',
      expanded: true,
    }, {
      caption: 'FATURA TARİHİ (AY)',
      area: 'row',
      dataField: 'fatura_tarih',
      groupInterval: 'month',
      dataType: 'date',
      filterType: 'include',
      expanded: true,
    }, {
      caption: 'KANAL TİPİ',
      area: 'column',
      dataField: 'KANAL_TIPI',
      filterType: 'include',
    }, {
      caption: 'E-FATURA DURUMU',
      area: 'filter',
      dataField: 'E_FATURA_DURUMU',
      filterType: 'include',
    }, {
      caption: 'FATURA TARİHİ (HAFTA)',
      area: 'filter',
      dataField: 'hafta',
      groupInterval: 'week',
      dataType: 'date',
      filterType: 'include',
    }, {
      caption: 'MİKTAR',
      area: 'data',
      dataField: 'miktar',
      dataType: 'number',
      filterType: 'include',
      format: {
        type: 'fixedPoint', // Ondalık gösterim
        precision: 0,       // 1 ondalık basamak
        useGrouping: true,  // Binlik ayracı kullan
      },
    }, {
      caption: 'USD NET TUTAR',
      dataField: 'USD_NET_TUTAR',
      dataType: 'number',
      summaryType: 'sum',
      area: 'data',
      format: {
        type: 'fixedPoint', // Ondalık gösterim
        precision: 0,       // 1 ondalık basamak
        useGrouping: true,  // Binlik ayracı kullan
      },
    }, {
      caption: 'SATIŞ PERSONELİ',
      dataField: 'SATIŞ PERSONEL KODU',
      area: 'filter',
    }],
  store: gridData.value, // REAKTİF!
}));

const DuzenReset = () => {
  grid.value?.instance?.getDataSource()?.state({});
};

function onContextMenuPreparing(e) {
  const dataSource = e.component.getDataSource();
  const sourceField = e.field;

  if (sourceField) {

    e.items.push({
      text: 'Düzeni Sıfırla',
      onItemClick() {
        dataSource.state({})
      },
    });

    if (!sourceField.groupName || sourceField.groupIndex === 0) {
      e.items.push({
        text: 'Alanı Gizle',
        onItemClick() {
          let fieldIndex;
          if (sourceField.groupName) {
            fieldIndex = dataSource
              .getAreaFields(sourceField.area, true)[sourceField.areaIndex]
              .index;
          } else {
            fieldIndex = sourceField.index;
          }

          dataSource.field(fieldIndex, {
            area: null,
          });
          dataSource.load();
        },
      });
    }

    if (sourceField.dataType === 'number') {
      const setSummaryType = function (args) {
        dataSource.field(sourceField.index, {
          summaryType: args.itemData.value,
        });

        dataSource.load();
      };
      const menuItems: Record<string, any>[] = [];

      e.items.push({ text: 'Hesaplama Türü', items: menuItems });

      ['Sum', 'Count', 'Avg', 'Min', 'Max'].forEach((summaryType) => {
        const summaryTypeValue = summaryType.toLowerCase();

        menuItems.push({
          text: summaryType,
          value: summaryType.toLowerCase(),
          onItemClick: setSummaryType,
          selected: e.field.summaryType === summaryTypeValue,
        });
      });
    }
  }
}
function onExportDataFieldHeadersChanged({ value }) {
  exportDataFieldHeaders.value = value;
}
function onExportRowFieldHeadersChanged({ value }) {
  exportRowFieldHeaders.value = value;
}
function onExportColumnFieldHeadersChanged({ value }) {
  exportColumnFieldHeaders.value = value;
}
function onExportFilterFieldHeadersChanged({ value }) {
  exportFilterFieldHeaders.value = value;
}

function onExporting(e: DxPivotGridTypes.ExportingEvent) {
  const workbook = new Workbook();
  const worksheet = workbook.addWorksheet('Cirolar');

  worksheet.columns = [
    { width: 35 },
  ];
  exportPivotGrid({
    component: e.component,
    worksheet,
    topLeftCell: { row: 4, column: 1 },
    keepColumnWidths: false,
    exportDataFieldHeaders: exportDataFieldHeaders.value,
    exportRowFieldHeaders: exportRowFieldHeaders.value,
    exportColumnFieldHeaders: exportColumnFieldHeaders.value,
    exportFilterFieldHeaders: exportFilterFieldHeaders.value,
  }).then((cellRange) => {
    // Header
    const headerRow = worksheet.getRow(2);
    const worksheetView: any = worksheet.views[0];

    headerRow.height = 30;

    const columnFromIndex = worksheetView.xSplit + 1;
    const columnToIndex = columnFromIndex + 3;
    worksheet.mergeCells(2, columnFromIndex, 2, columnToIndex);

    const headerCell = headerRow.getCell(columnFromIndex);
    headerCell.value = 'Cirolar';
    headerCell.font = { name: 'Segoe UI Light', size: 22, bold: true };
    headerCell.alignment = { horizontal: 'left', vertical: 'middle', wrapText: true };

    // Footer
    const footerRowIndex = (cellRange?.to?.row ?? 0) + 2;
    const footerCell = worksheet.getRow(footerRowIndex).getCell(cellRange?.to?.column ?? 0);
    footerCell.value = 'www.oftyazilim.com';
    footerCell.font = { color: { argb: 'BFBFBF' }, italic: true };
    footerCell.alignment = { horizontal: 'right' };
  }).then(() => {
    workbook.xlsx.writeBuffer().then((buffer) => {
      saveAs(new Blob([buffer], { type: 'application/octet-stream' }), 'Cirolar.xlsx');
    });

  });
}
// ⚡ gridData değiştiğinde PivotGrid refresh yapıyor
watch(gridData, () => {
  grid.value?.instance?.getDataSource()?.reload();
  grid.value?.instance?.bindChart(chart.value?.instance, {
    dataFieldsDisplayMode: 'splitPanes',
    alternateDataFields: true,
  });
});

const getData = async () => {
  selectedDateRange.value = [filterValue.value, filterValue1.value]

  filterValue.value = formatDate(selectedDateRange.value[0])
  filterValue1.value = formatDate(selectedDateRange.value[1])

  loadingVisible.value = true
  try {
    const response = await axios.get('/api/ciro-ozettablo', {
      params: {
        filterValue: filterValue.value,
        filterValue1: filterValue1.value,
        acik_kapali: acikKapali.value,
        userId: userData.value.id
      },
    });

    gridData.value = response.data.ciro;

  } catch (error) {
    console.error('Veri çekilirken hata oluştu:', error);
  }
  loadingVisible.value = false
};

const userData = useCookie<any>('userData')

onMounted(async () => {
  // await getData();
  pageTitleStore.setTitle("Cirolar Özet Tablo");

  axios
    .post("/api/log-kayit", {
      userId: userData.value.id,
      sayfa: 'Cirolar (özet tablo)',
      eylem: 'Yükleme',
    });
});

const customizeTooltip = ({ seriesName, originalValue }) => {
  const valueText = (seriesName.indexOf('Total') !== -1)
    ? new Intl.NumberFormat('en-TR').format(originalValue)
    : originalValue;

  return {
    html: `${seriesName}<div class='currency'>${valueText}</div>`,
  };
};

const customizeCell = (e: any) => {
  nextTick(() => {
    const pivotGrid = e.component;
    const cells = pivotGrid._$element.find(".dx-pivotgrid-cell"); // Pivot hücrelerini al

    cells.each((_, cell) => {
      const $cell = $(cell);
      const cellValue = parseFloat($cell.text()); // Hücredeki değeri al

      if (!isNaN(cellValue)) {
        $cell.empty(); // İçeriği temizle
        const progressBar = document.createElement("div");
        $cell.append(progressBar);

        new DxProgressBar({
          elementAttr: { class: "progress-bar" },
          value: cellValue,
          showStatus: true,
        }).$mount(progressBar);
      }
    });
  });
};

</script>


<style>
#pivotgrid {
  margin-block-start: 20px;
}

.currency {
  text-align: center;
}

.long-title h3 {
  font-family:
    "Segoe UI Light",
    "Helvetica Neue Light",
    "Segoe UI",
    "Helvetica Neue",
    "Trebuchet MS",
    Verdana;
  font-size: 28px;
  font-weight: 200;
  margin-block-end: 20px;
  text-align: center;
}

.export-options {
  padding: 20px;
  background-color: rgba(191, 191, 191, 15%);
  margin-block-start: 20px;
}

.caption {
  font-size: 18px;
  font-weight: 500;
}

.options {
  display: grid;
  gap: 12px;
  grid-template-columns: repeat(1, 1fr);
  margin-block-start: 10px;
}

@media (min-width: 500px) {
  .options {
    grid-template-columns: 250px 1fr;
  }
}

@media (min-width: 1000px) {
  .options {
    grid-template-columns: repeat(4, 1fr);
  }
}
</style>

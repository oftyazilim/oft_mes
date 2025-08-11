<template>
  <VRow>
    <VCol cols="12">
      <DxDataGrid id="grid-satis" ref="dataGridRef" :data-source="gridData" key-expr="id" :show-borders="true"
        width="100%" :focused-row-enabled="true" :row-alternation-enabled="true" @exporting="onExporting"
        @focused-row-changed="onFocusedRowChanged" @cell-prepared="onCellPrepared" >

        <DxColumn data-field="id" caption="ID" :visible="true" width="90" />
        <DxColumn data-field="user_id" caption="USER ID" :visible="true" width="80" />
        <DxColumn data-field="tarih" caption="TARİH" data-type="date" :width="150" :visible="true" alignment="center"
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
          }" />
        <DxColumn data-field="sayfa" caption="SAYFA" :visible="true" width="150" />
        <DxColumn data-field="eylem" caption="EYLEM" :visible="true" width="150" />
        <DxColumn data-field="ip" caption="IP" :visible="true" width="120" />
        <DxColumn data-field="name" caption="KULLANICI" :visible="true" width="130" />
        <DxColumn data-field="unvan" caption="ÜNVAN" :visible="true" width="200" />
        <DxColumn data-field="proses" caption="PROSES" :visible="true" width="190" />
        <DxColumn data-field="ismerkezi_id" caption="İŞ MERKEZİ ID" :visible="true" width="90" />
        <DxColumn data-field="istasyon_id" caption="İSTASYON ID" :visible="true" width="90" />
        <DxColumn data-field="operasyon_id" caption="OPERASYON ID" :visible="true" width="90" />

        <DxGrouping :auto-expand-all="expandAll" />
        <DxGroupPanel :visible="true" />
        <DxHeaderFilter :visible="true" />
        <DxFilterPanel :visible="true" />
        <DxFilterRow :visible="true" />
        <!-- <DxFilterBuilderPopup :visible="true" /> -->
        <DxSearchPanel :visible="true" :width="240" placeholder="Ara..." />
        <DxLoadPanel :key="loadingVisible" v-model:visible="loadingVisible" :show-indicator="true" :show-pane="true"
        :shading="true" />
                <DxScrolling mode="virtual" />
        <DxExport :enabled="true" :allow-export-selected-data="false" />
        <DxColumnChooser height="540px" :enabled="true" mode="select">
          <DxPosition my="right top" at="right bottom" of=".dx-datagrid-column-chooser-button" />
          <DxColumnChooserSelection :allow-select-all="true" :select-by-click="true" :recursive="true" />
        </DxColumnChooser>

        <DxToolbar>
          <DxItem location="before" name="groupPanel" />
          <DxItem location="after" locate-in-menu="auto" template="yenileTemplate"
            menu-item-template="menuYenileTemplate" @click="Yenile" />
          <DxItem name="exportButton" />
          <DxItem name="columnChooserButton" />
          <DxItem name="searchPanel" />
        </DxToolbar>

        <DxLoadPanel v-model:visible="loadingVisible" :show-indicator="true" />

        <template #yenileTemplate>
          <DxButton icon="refresh" styling-mode="text" hint="Yenile" id="sayim" />
        </template>
        <template #menuYenileTemplate>
          <div style="display: flex; align-items: center;">
            <i class="dx-icon dx-icon-refresh"></i>
            <span style="margin-left: 8px;">Yenile</span>
          </div>
        </template>
      </DxDataGrid>
    </VCol>
  </VRow>
</template>

<script setup lang="ts">
import { onMounted, defineComponent, ref, computed } from "vue";
import {
  DxDataGrid,
  DxScrolling,
  DxSorting,
  DxEditing,
  DxFilterPanel,
  DxHeaderFilter,
  DxSearchPanel,
  DxFilterRow,
  DxFilterBuilderPopup,
  DxColumnChooser,
  DxPosition,
  DxColumnChooserSearch,
  DxColumnChooserSelection,
  DxGroupPanel,
  DxSummary,
  DxGroupItem,
  DxTotalItem,
  DxSortByGroupSummaryInfo,
  DxExport,
  DxDataGridTypes,
  DxColumn,
  DxItem,
  DxToolbar,
  DxGrouping
} from 'devextreme-vue/data-grid';
import { DxTextBoxTypes } from "devextreme-vue/text-box";
import axios from 'axios';
import notify from 'devextreme/ui/notify';
import { DxButton } from 'devextreme-vue/button';
import { exportDataGrid } from "devextreme/excel_exporter";
import { Workbook } from "exceljs";
import { saveAs } from "file-saver-es";
import { usePageTitleStore } from "@/stores/pageTitle";
import { DxLoadPanel } from 'devextreme-vue/load-panel'

const pageTitleStore = usePageTitleStore();
pageTitleStore.setTitle('Kullanıcı Logları');
pageTitleStore.setToplam('');
document.title = "OFT - Kullanıcı Logları";

const apiUrl = '/api/loglar'; // Backend API rotası
const expandAll = ref(true);
const gridData = ref([]);
const loadingVisible = ref(false);
const dataGridRef = ref<DxDataGrid | null>(null);

const toggleExpandAll = () => {
  expandAll.value = !expandAll.value;
};

const onCellPrepared = (e: any) => {
  if (e.rowType === "data" && e.column.dataField === "eylem") {
    e.cellElement.style.fontWeight = "bold";
  }
};

const Yenile = () => {
  getData();
}

const getData = async () => {
  loadingVisible.value = true;
  try {
    const response = await axios.get(apiUrl);
    gridData.value = response.data.data;
  } catch (error) {
    console.error('Error fetching data:', error);
    notify(`Veri alınamadı`, 'error', 1500)
  } finally {
    loadingVisible.value = false;
  }

};

onMounted(() => {
   getData();
   axios
    .post("/api/log-kayit", {
      userId: userData.value.id,
      sayfa: 'Kullanıcı Logları',
      eylem: 'Yükleme',
    });
});
const userData = useCookie<any>("userData");

const onExporting = (e: DxDataGridTypes.ExportingEvent) => {
  const workbook = new Workbook();
  const worksheet = workbook.addWorksheet("KullaniciLoglari");

  exportDataGrid({
    component: e.component,
    worksheet,
    autoFilterEnabled: true,
  }).then(() => {
    workbook.xlsx.writeBuffer().then((buffer) => {
      saveAs(
        new Blob([buffer], { type: "application/octet-stream" }),
        "KullaniciLoglari.xlsx"
      );
    });
  });

  e.cancel = true;
};

</script>


<style>
#grid-satis {
  display: flex;
  flex-direction: column;
  /* margin: 10px; */
  margin-top: -20px;
  margin-bottom: 20px;
  block-size: 85vh;
}
</style>

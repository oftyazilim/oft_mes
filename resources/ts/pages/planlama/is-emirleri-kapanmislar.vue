<template>
  <VCard class="mt-0 pa-0 pt-2">
    <VCardText class="mt-0 pa-2">
      <VCol cols="12" class="mt-2 pa-1 pe-2">
        <div id="liste" style="margin-block-end: -10px;">
          <DxContextMenu :data-source="menuItems" :width="200" target="#grid" @item-click="itemClick"/>

          <DxDataGrid id="grid" ref="dataGridRef" :key="gridKey" :data-source="gridData" key-expr="satir_id"
                      :show-borders="true" :focused-row-enabled="true" :row-alternation-enabled="true" :min-width="200"
                      @exporting="onExporting" :allow-column-reordering="true" :column-auto-width="false"
                      @focused-row-changed="onFocusedRowChanged" :allow-column-resizing="true"
                      column-resizing-mode="widget"
                      @cell-prepared="onCellPrepared" :auto-navigate-to-focused-row="true"
                      :on-option-changed="handleOptionChanged" @contextMenuPreparing="onContextMenuPreparing"
                      v-model:focused-row-key="focusedRowKey">

            <!-- <DxColumn type="selection" :fixed="true" fixedPosition="left" /> -->
            <DxColumn data-field="id" caption="ID" :visible="false" :min-width="90"/>
            <DxColumn data-field="hafta" caption="HAFTA" :fixed="true" :width="120" :visible="true" alignment="left"
                      :cell-template="weekCellTemplate"/>
            <DxColumn data-field="IS_ISTASYONU" caption="İST. ADI" :visible="true" :width="130"
            />
            <DxColumn data-field="IS_ISTASYONU_KODU" caption="İSTASYON KODU" :visible="false" :width="150"/>
            <DxColumn data-field="IS_ISTASYONU_ADI" caption="İSTASYON ADIx" :visible="false" :width="150"/>
            <DxColumn data-field="OPERASYON" caption="OPRSYN" :visible="true" :width="120"/>
            <DxColumn data-field="siparis_belge_no" caption="SİPARİŞ NO" :width="90" :visible="true"/>
            <DxColumn data-field="cari_ad" caption="MÜŞTERİ" :visible="true" :min-width="140"/>
            <DxColumn data-field="stok_kodu" caption="STOK KODU" :visible="true" :width="120"/>
            <DxColumn data-field="stok_adi" caption="STOK ADI" :min-width="200"/>
            <DxColumn data-field="isemri_id" caption="İŞ EMRİ ID" :width="150" :visible="false"/>
            <DxColumn data-field="isemri_no" caption="İŞ EMRİ NO" :width="120"/>
            <DxColumn data-field="teslim_tarihi" caption="TESLİM TARİHİ" data-type="date" :width="140" :visible="true"
                      :format="{
                formatter: (date: any) => {
                  const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                  }).format(new Date(date));
                  return formattedDate.replace(/\//g, '.');
                },
              }" :cell-template="getIconType1"/>
            <DxColumn data-field="planlanan_baslangic" caption="PLN BŞL" data-type="date" :width="130" :visible="false"
                      :format="{
                formatter: (date: any) => {
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
            <DxColumn data-field="kapanma_tarihi" caption="KAPANMA TARİHİ" data-type="date" :width="140" :visible="true"
                      :format="{
                formatter: (date) => {
                  const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                  }).format(new Date(date));

                  return formattedDate.replace(/\//g, '.');
                },
              }" alignment="left" :cell-template="getIconType"/>
            <DxColumn data-field="kalan_miktar" caption="KALAN MİKTAR" data-type="number" :width="90" :visible="true"/>

            <DxColumn data-field="surec" caption="SÜREÇ" data-type="number" :width="150" :visible="true"
                      cell-template="surecCellTemplate" alignment="center"/>
            <DxColumn data-field="siparis_miktari" caption="SİPARİŞ MİKTARI" data-type="number" :width="60"
                      :visible="true"/>
            <DxColumn data-field="isemri_miktari" caption="İŞ EMRİ MİKTARI" data-type="number" :width="60"
                      :visible="true"/>
            <DxColumn data-field="uretilen_toplam_miktar" caption="TOPLAM URETİLEN" data-type="number" :width="60"
                      :visible="false"/>
            <DxColumn data-field="uretilen_net_miktar" caption="NET URETILEN" data-type="number" :width="60"
                      :visible="true"/>
            <DxColumn data-field="toplam_hurda_miktari" caption="HURDA MİKTARI" data-type="number" :width="60"
                      :visible="true"/>
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
              }"/>
            <DxColumn data-field="aksesuar" caption="AKSESUAR" :width="60" cell-template="aksesuarTemplate"
                      alignment="center"/>

            <DxColumn data-field="isemri_tipi" caption="İŞ EMRİ TİPİ" :min-width="120" :width="140"/>

            <DxColumn data-field="teknik_not1" caption="PLN NOTU" :width="60"/>
            <DxColumn data-field="teknik_not2" caption="OPR NOTU" :width="90" alignment="center"/>
            <DxColumn data-field="kaydi_giren_kullanici" caption="KAYIT YAPAN" :min-width="120" :width="140"/>
            <DxColumn data-field="item_id" caption="ITEM ID" :visible="false" :min-width="90"/>
            <DxColumn data-field="satir_id" caption="SATIR ID" :visible="false" :min-width="90"/>


            <DxColumn data-field="sip_not1" caption="SİP NOT 1" :min-width="120"/>
            <DxColumn data-field="sip_not2" caption="SİP NOT 2" :min-width="120"/>
            <DxColumn data-field="sip_not3" caption="SİP NOT 3" :min-width="120"/>
            <DxColumn data-field="sip_not4" caption="SİP NOT 4" :min-width="120"/>
            <DxColumn data-field="CIKIS_DEPO" caption="ÇIKIŞ DEPO" :min-width="80"/>

            <DxLoadPanel :key="loadingVisible" v-model:visible="loadingVisible" :show-indicator="true" :show-pane="true"
                         :shading="true"/>
            <DxSelection mode="multiple" select-all-mode="page" show-check-boxes-mode="onClick"/>
            <DxGrouping :auto-expand-all="expandAll"/>
            <DxGroupPanel :visible="true"/>
            <DxHeaderFilter :visible="true"/>
            <DxFilterPanel :visible="true"/>
            <DxFilterRow :visible="goster"/>
            <DxSearchPanel :visible="true" :width="240"/>
            <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always"/>
            <DxSorting mode="multiple"/>
            <DxRowDragging :allow-reordering="true" :on-reorder="onReorder" :show-drag-icons="false"/>
            <DxExport :enabled="true" :allow-export-selected-data="false"/>

            <DxColumnChooser height="540px" :enabled="true" mode="select" :position="position">
              <DxColumnChooserSearch :enabled="true"/>
              <DxColumnChooserSelection :allow-select-all="true" :select-by-click="true" :recursive="true"/>
            </DxColumnChooser>
            <hr>
            <br>
            <DxToolbar>
              <DxItem location="before" name="groupPanel"/>
              <DxItem location="before" locate-in-menu="auto" template="collapseTemplate"/>
              <DxItem location="before" locate-in-menu="auto" template="filtreTarih"/>

              <DxItem location="before" locate-in-menu="auto" template="filtreGun" menu-item-template="menufiltreGun"
                      @click="Filtrele('g')"/>

              <DxItem location="before" locate-in-menu="auto" template="filtreAy" menu-item-template="menufiltreAy"
                      @click="Filtrele('a')"/>

              <DxItem location="before" locate-in-menu="auto" template="filtreYil" menu-item-template="menufiltreYil"
                      @click="Filtrele('y')"/>

              <DxItem location="after" locate-in-menu="auto" template="yenileTemplate"
                      menu-item-template="menuYenileTemplate" @click="Yenile"/>
              <DxItem location="after" locate-in-menu="auto" template="filtreTemizleTemplate"
                      menu-item-template="menuFiltreTemizleTemplate" @click="FiltreTemizle"/>
              <DxItem location="after" locate-in-menu="auto" template="filtreGosterTemplate"
                      menu-item-template="menuFiltreGosterTemplate" @click="toggleGoster"/>
              <DxItem name="exportButton"/>
              <DxItem name="columnChooserButton"/>
              <DxItem name="searchPanel"/>
            </DxToolbar>

            <DxSummary>
              <DxGroupItem :align-by-column="true" column="isemri_no" summary-type="count" display-format="{0} adet"
                :alignment="right" :customize-text="formatSummaryText" />
              <DxGroupItem :align-by-column="true" column="kalan_miktar" summary-type="sum" :alignment="right"
                :customize-text="formatSummaryText" />
              <DxTotalItem :align-by-column="true" column="isemri_no" summary-type="count" display-format="{0} adet"
                :alignment="right" :customize-text="formatSummaryText" />
              <DxTotalItem :align-by-column="true" column="kalan_miktar" summary-type="sum" :alignment="right"
                :customize-text="formatSummaryText" />
            </DxSummary>

            <template #filtreGun>
              <DxButton icon="filter" styling-mode="text" text="Gün" hint="Günlük" id="gun"/>
            </template>
            <template #menufiltreGun>
              <div style="display: flex; align-items: center">
                <i class="dx-icon dx-icon-filter"></i>
                <span style="margin-inline-start: 8px">Günlük</span>
              </div>
            </template>

            <template #filtreAy>
              <DxButton icon="filter" styling-mode="text" text="Ay" hint="Aylık" id="ay"/>
            </template>
            <template #menufiltreAy>
              <div style="display: flex; align-items: center">
                <i class="dx-icon dx-icon-filter"></i>
                <span style="margin-inline-start: 8px">Aylık</span>
              </div>
            </template>

            <template #filtreYil>
              <DxButton icon="filter" styling-mode="text" text="Yıl" hint="Yıllık" id="yil"/>
            </template>
            <template #menufiltreYil>
              <div style="display: flex; align-items: center">
                <i class="dx-icon dx-icon-filter"></i>
                <span style="margin-inline-start: 8px">Yıllık</span>
              </div>
            </template>

            <template #filtreTarih>
              <div style="
              inline-size: 280px;
              margin-block: -10px;
              margin-block-end: 20px;
            ">
                <DxDateRangeBox :value="selectedDateRange" :min="min" :max="max" @value-changed="onDateRangeChanged"
                                style="inline-size: 100%"/>
              </div>
            </template>


            <template #filtreTemizleTemplate>
              <DxButton icon="notequal" styling-mode="text" hint="Filtre Temizle" id="filtretemizle"/>
              <!-- <VIcon size="24" icon="tabler-x" /> -->
            </template>
            <template #menuFiltreTemizleTemplate>
              <div style="display: flex; align-items: center;">
                <i class="dx-icon dx-icon-undo"></i>
                <span style="margin-inline-start: 8px;">Filtre Temizle</span>
              </div>
            </template>

            <template #yenileTemplate>
              <DxButton icon="refresh" styling-mode="text" hint="Yenile" id="sayim"/>
            </template>
            <template #menuYenileTemplate>
              <div style="display: flex; align-items: center;">
                <i class="dx-icon dx-icon-refresh"></i>
                <span style="margin-inline-start: 8px;">Yenile</span>
              </div>
            </template>

            <template #filtreGosterTemplate>
              <DxButton icon="filter" styling-mode="text" hint="Filtre Goster"/>
            </template>
            <template #menuFiltreGosterTemplate>
              <div style="display: flex; align-items: center;">
                <i class="dx-icon dx-icon-filter"></i>
                <span style="margin-inline-start: 8px;">Filtre Göster</span>
              </div>
            </template>

            <template #collapseTemplate>
              <DxButton :icon="expandAll ? 'collapse' : 'expand'" hint="expandAll ? 'Daralt' : 'Genişlet'" width="40"
                        height="40" styling-mode="text" @click="toggleExpandAll"/>
            </template>

            <template #surecCellTemplate="{ data: cellData }">
              <SurecCell :cell-data="cellData" :max-deger="100"/>
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
            <template #aksesuarTemplate="{ data }">
              <template v-if="data.value === 'Aksesuarlı'">
                <i :class="['dx-icon', 'dx-icon-gift']" :style="{ fontSize: '20px', color: staticPrimaryColor }"></i>
              </template>
            </template>

          </DxDataGrid>
        </div>
      </VCol>
    </VCardText>
  </VCard>

  <DxPopup v-model:visible="popupMesajGosterVisible" :width="400" :height="220" :hide-on-outside-click="true"
           :show-close-button="true" :title=notBaslik>
    <template #content>
      <DxScrollView width="100%" height="100%">
        <div class="caption">{{ selectedRow.isemri_no }}</div>
        {{ planlamaNotu }}
      </DxScrollView>
    </template>
  </DxPopup>

</template>

<script setup lang="ts">


import {onMounted, ref} from "vue";
import {usePageTitleStore} from "@/stores/pageTitle";
// import { DxTooltip } from 'devextreme-vue/tooltip';
import {staticPrimaryColor} from "@/plugins/vuetify/theme";
import axios from "axios";
import {DxButton} from "devextreme-vue/button";
import DxSelectBox from "devextreme-vue/cjs/select-box";
import DxContextMenu, {DxContextMenuTypes} from 'devextreme-vue/context-menu';
import {
  DxColumn,
  DxColumnChooser,
  DxColumnChooserSearch,
  DxColumnChooserSelection,
  DxDataGrid,
  DxDataGridTypes,
  DxExport,
  DxFilterPanel,
  DxFilterRow,
  DxGrouping,
  DxGroupItem,
  DxTotalItem,
  DxGroupPanel,
  DxHeaderFilter,
  DxItem,
  DxScrolling,
  DxSearchPanel,
  DxSelection,
  DxSummary,
  DxToolbar,
  DxSorting,
  DxRowDragging,
  DxStateStoring,
} from "devextreme-vue/data-grid";
import DxDateBox from 'devextreme-vue/date-box';
import {DxLoadPanel} from 'devextreme-vue/load-panel';
import {DxPopup, DxToolbarItem} from 'devextreme-vue/popup';
import {DxSwitch} from 'devextreme-vue/switch';
import DxTextArea from 'devextreme-vue/text-area';
import DxTextBox from 'devextreme-vue/text-box';
import query from 'devextreme/data/query';
import {exportDataGrid} from "devextreme/excel_exporter";
import notify from "devextreme/ui/notify";
import {Workbook} from "exceljs";
import {saveAs} from "file-saver-es";
import SurecCell from "./SurecCell.vue";
import DxCalendar from 'devextreme-vue/calendar';
import {DxPopover} from 'devextreme-vue/popover';
import {DxTooltip} from 'devextreme-vue/tooltip';
import DxDateRangeBox from "devextreme-vue/date-range-box";

// Tarihi yyyy-MM-dd formatına dönüştüren fonksiyon
const formatDate = (date) => {
  if (!date) return null;
  const d = new Date(date);
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, "0"); // Ayları 0 tabanlı olduğu için +1
  const day = String(d.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
};

document.title = "OFT - Kapanmış İş Emirleri";

const pageTitleStore = usePageTitleStore();
const popupMesajGosterVisible = ref(false);
const expandAll = ref(false);
const notlar = ref([]);
const gridData = ref([]);
const goster = ref(false);

const dataGridRef = ref<DxDataGrid | null>(null);
const loadingVisible = ref<boolean>(false);
const position = {of: 'window'};
const planlamaNotu = ref(null);
const notBaslik = ref('');
const UretimNotu = ref(null);
const selectedNot = ref(null);
const textAreaValue = ref("");
const gridKey = ref(Date.now());
const focusedRowKey = ref(0);

const now = new Date();
const initialDates = [new Date(now.getTime()), new Date(now.getTime())];
const initialDatesMonth = [
  new Date(now.getFullYear(), now.getMonth(), 1), // Ayın ilk günü
  new Date(now.getTime()), // Geçerli gün
];

function formatSummaryText(e) {
  return new Intl.NumberFormat('tr-TR', { maximumFractionDigits: 0 }).format(e.value);
}

const initialDatesYear = [
  new Date(now.getFullYear(), 0, 1), // Yılın ilk günü
  new Date(now.getTime()), // Geçerli gün
];

const selectedDateRange = ref(initialDates);
const filterValue = ref(formatDate(selectedDateRange.value[0])); // Formatlanmış başlangıç tarihi
const filterValue1 = ref(formatDate(selectedDateRange.value[1])); // Formatlanmış bitiş tarihi

// Durumu localStorage'dan yükle
const loadGridState = () => {
  const savedState = localStorage.getItem("Kapanmislar");
  if (savedState) {
    dataGridRef.value?.instance.state(JSON.parse(savedState));
    // console.log("Grid durumu yüklendi:", savedState);
  }
};
const onStateResetClick = () => {
  localStorage.removeItem("Kapanmislar");
  dataGridRef.value!.instance!.state(null);
};
const saveGridState = () => {
  const state = dataGridRef.value?.instance.state();
  localStorage.setItem("Kapanmislar", JSON.stringify(state));
  // console.log("Grid durumu kaydedildi:", state);
};

const onDateRangeChanged = (newValue) => {
  if (!newValue || !newValue.value || newValue.value.length !== 2) {
    console.warn("Geçersiz tarih aralığı");
    selectedDateRange.value = initialDates;
    filterValue.value = formatDate(selectedDateRange.value.startDate);
    filterValue1.value = formatDate(selectedDateRange.value.endDate);
    return;
  }
  const [startDate, endDate] = newValue.value;
  selectedDateRange.value = [startDate, endDate];
  filterValue.value = formatDate(startDate);
  filterValue1.value = formatDate(endDate);
  getData();
};


const Filtrele = (e: string) => {
  switch (e) {
    case "g":
      selectedDateRange.value = initialDates;
      filterValue.value = formatDate(initialDates[0]);
      filterValue1.value = formatDate(initialDates[1]);
      break;
    case "a":
      selectedDateRange.value = initialDatesMonth;
      filterValue.value = formatDate(initialDatesMonth[0]);
      filterValue1.value = formatDate(initialDatesMonth[1]);
      break;
    case "y":
      selectedDateRange.value = initialDatesYear;
      filterValue.value = formatDate(initialDatesYear[0]);
      filterValue1.value = formatDate(initialDatesYear[1]);
      break;
  }
  getData();
};

const weekCellTemplate = (cellElement: HTMLElement, cellInfo: any): void => {
  const hasNoteP = cellInfo.data.teknik_not1 && cellInfo.data.teknik_not1.trim() !== "";
  const hasNoteU = cellInfo.data.teknik_not2 && cellInfo.data.teknik_not2.trim() !== "";

  // Ana metni ekle (hafta değeri)
  cellElement.innerText = cellInfo.text;

  const createIcon = (color: string, not: string) => {
    const icon = document.createElement("span");
    icon.className = "dx-icon dx-icon-square";
    icon.style.marginLeft = "8px"; // İkon ile metin arasına boşluk ekle
    icon.style.color = color; // İkon rengi
    icon.style.fontSize = "16"; // Ana span'ı etkisizleştir
    icon.style.display = "inline-flex";

    // Tıklama olayı ekle
    icon.addEventListener("click", () => {
      planlamaNotu.value = not === "Planlamanın Notu" ? cellInfo.data.teknik_not1 : cellInfo.data.teknik_not2;
      notBaslik.value = not;
      popupMesajGosterVisible.value = !popupMesajGosterVisible.value;
    });

    return icon;
  };

  if (hasNoteP) {
    let renk = '';
    switch (cellInfo.data.teknik_not1) {
      case "Mekanik eksik":
        renk = 'orange';
        break;
      case "Satınalma eksik":
        renk = 'red';
        break;
      case "Revizyon yapıldı. Müşteri / Arge":
        renk = 'gray';
        break;
      case "Ürün ağacı yok":
        renk = 'gray';
        break;
      case "Beklemede - Yönetim - Fiyat":
        renk = '#f79191';
        break;
      case "İptal - Müşteri":
        renk = 'darkgray';
        break;
      default:
        renk = '#a29bef'
        break;
    }
    // Kalite onayı bekleyenler mavi, Beklemede olanlar kademeli kırmızı, Müşteri iptali koyu gri işaretli olarak yapar mısınız.
    const plnIcon = createIcon(renk, "Planlamanın Notu");
    cellElement.appendChild(plnIcon);
  }

  if (hasNoteU) {
    const greenIcon = createIcon("green", "Üretimin Notu");
    cellElement.appendChild(greenIcon);
  }
};

// Tarih formatını 'DD.MM.YYYY' -> 'YYYY-MM-DD' olarak dönüştüren fonksiyon
const convertToISODate = (dateString: string | null | undefined) => {
  if (!dateString || typeof dateString !== "string") {
    return null;
  }
  const [day, month, year] = dateString.split(".");
  return `${year}-${month}-${day}`;
};

// Hücreye ikon tipi döndüren fonksiyon
const getIconType = (cellElement: HTMLElement, cellInfo: any): void => {
  // Tarihleri kontrol et ve dönüştür
  const kapanmaTarihiISO = convertToISODate(cellInfo.data.kapanma_tarihi);
  const planlananBitisISO = convertToISODate(cellInfo.data.planlanan_bitis_tarihi);

  // Eğer tarih formatı geçersizse varsayılan bir değer ata
  const date1 = planlananBitisISO ? new Date(planlananBitisISO) : new Date();
  const date2 = kapanmaTarihiISO ? new Date(kapanmaTarihiISO) : new Date();

  date1.setHours(0, 0, 0, 0);
  date2.setHours(0, 0, 0, 0);

  const diffInDays = Math.floor(
    (date1.getTime() - date2.getTime()) / (1000 * 60 * 60 * 24)
  );

  // Ana metni ekle (hafta değeri)
  cellElement.innerText = cellInfo.text;

  const createIcon = (color: string, dolu: boolean) => {
    const icon = document.createElement("span");
    icon.className = dolu ? "dx-icon dx-icon-isnotblank" : "dx-icon dx-icon-isblank";
    icon.style.marginRight = "8px"; // İkon ile metin arasına boşluk ekle
    icon.style.color = color; // İkon rengi
    icon.style.fontSize = "16"; // Ana span'ı etkisizleştir
    icon.style.display = "inline-flex";
    return icon;
  };

  if (diffInDays < 0) {
    const redIcon = createIcon("red", true);
    if (cellElement.firstChild) {
      cellElement.insertBefore(redIcon, cellElement.firstChild);
    } else {
      cellElement.appendChild(redIcon); // İlk çocuk yoksa sona ekle
    }
  } else if (diffInDays === 0) {
    const blueIcon = createIcon("blue", true);
    if (cellElement.firstChild) {
      cellElement.insertBefore(blueIcon, cellElement.firstChild);
    } else {
      cellElement.appendChild(blueIcon); // İlk çocuk yoksa sona ekle
    }
  } else if (diffInDays <= 3) {
    const orangeIcon = createIcon("orange", true);
    if (cellElement.firstChild) {
      cellElement.insertBefore(orangeIcon, cellElement.firstChild);
    } else {
      cellElement.appendChild(orangeIcon); // İlk çocuk yoksa sona ekle
    }
  } else {
    const greenIcon = createIcon("limegreen", true);
    if (cellElement.firstChild) {
      cellElement.insertBefore(greenIcon, cellElement.firstChild);
    } else {
      cellElement.appendChild(greenIcon); // İlk çocuk yoksa sona ekle
    }
  }
};
const getIconType1 = (cellElement: HTMLElement, cellInfo: any): void => {
  // Tarihleri kontrol et ve dönüştür
  const kapanmaTarihiISO = convertToISODate(cellInfo.data.kapanma_tarihi);
  const planlananBitisISO = convertToISODate(cellInfo.data.teslim_tarihi);

  // Eğer tarih formatı geçersizse varsayılan bir değer ata
  const date1 = planlananBitisISO ? new Date(planlananBitisISO) : new Date();
  const date2 = kapanmaTarihiISO ? new Date(kapanmaTarihiISO) : new Date();

  date1.setHours(0, 0, 0, 0);
  date2.setHours(0, 0, 0, 0);

  const diffInDays = Math.floor(
    (date1.getTime() - date2.getTime()) / (1000 * 60 * 60 * 24)
  );

  // Ana metni ekle (hafta değeri)
  cellElement.innerText = cellInfo.text;

  const createIcon = (color: string, dolu: boolean) => {
    const icon = document.createElement("span");
    icon.className = dolu ? "dx-icon dx-icon-isnotblank" : "dx-icon dx-icon-isblank";
    icon.style.marginRight = "8px"; // İkon ile metin arasına boşluk ekle
    icon.style.color = color; // İkon rengi
    icon.style.fontSize = "16"; // Ana span'ı etkisizleştir
    icon.style.display = "inline-flex";
    return icon;
  };

  if (diffInDays < 0) {
    const redIcon = createIcon("red", true);
    if (cellElement.firstChild) {
      cellElement.insertBefore(redIcon, cellElement.firstChild);
    } else {
      cellElement.appendChild(redIcon); // İlk çocuk yoksa sona ekle
    }
  } else if (diffInDays === 0) {
    const blueIcon = createIcon("blue", true);
    if (cellElement.firstChild) {
      cellElement.insertBefore(blueIcon, cellElement.firstChild);
    } else {
      cellElement.appendChild(blueIcon); // İlk çocuk yoksa sona ekle
    }
  } else if (diffInDays <= 3) {
    const orangeIcon = createIcon("orange", true);
    if (cellElement.firstChild) {
      cellElement.insertBefore(orangeIcon, cellElement.firstChild);
    } else {
      cellElement.appendChild(orangeIcon); // İlk çocuk yoksa sona ekle
    }
  } else {
    const greenIcon = createIcon("limegreen", true);
    if (cellElement.firstChild) {
      cellElement.insertBefore(greenIcon, cellElement.firstChild);
    } else {
      cellElement.appendChild(greenIcon); // İlk çocuk yoksa sona ekle
    }
  }
};

const selectedRow = ref({
  hafta: '',
  isemri_no: '',
  teslim_tarihi: '',
  siparis_belge_no: '',
  isemri_id: '',
  sip_not1: '',
  sip_not2: '',
  sip_not3: '',
  sip_not4: '',
  teknik_not1: '',
  teknik_not2: '',
  cari_ad: '',
  eksikler: '',
  kaydi_giren_kullanici: '',
  planlanan_baslangic: '',
  aksesuar: '',
  sip_detay_id: 0,
})

const numberedSteps = ref([
  {
    title: 'Sipariş Detayı',
    subtitle: 'Seçilmedi',
  },
  {
    title: 'Alt İş Emirleri',
    subtitle: 'Seçilmedi',
  },
  {
    title: 'Rota',
    subtitle: 'Tasarım aşamasında',
  },
]);

const currentStep = ref(0)

const vazgecOptions = {
  width: 100,
  type: 'normal',
  text: 'Vazgeç',
  stylingMode: 'contained',
  onClick: () => {
    popupTarihVisible.value = false;
    popupAksesuarVisible.value = false;
    popupDetayGosterVisible.value = false;
    popupIstasyonVisible.value = false;
  },
};

const clearSelection = () => {
  const dataGrid = dataGridRef.value!.instance!;
  dataGrid.clearSelection();
};

const onCellPrepared = (e: any) => {
  if (
    e.rowType === "data" &&
    ((e.column.dataField === "isemri_miktari" || e.column.dataField === "uretilen_net_miktar" || e.column.dataField === "kalan_miktar") && e.value > 0)
  ) {
    e.cellElement.style.fontWeight = "bold";
  }
  if (
    e.rowType === "data" &&
    (e.column.dataField === "toplam_hurda_miktari" && e.value > 0)
  ) {
    e.cellElement.style.color = "white";
    e.cellElement.style.fontWeight = "bold";
    e.cellElement.style.backgroundColor = "#EC2407";
  }
  if (
    e.rowType === "data" &&
    (e.column.dataField === "hafta")
  ) {
    e.cellElement.style.fontWeight = "bold";
  }
};

const FiltreTemizle = () => {
  dataGridRef.value?.instance.clearFilter();
};

const onFocusedRowChanged = (e: any) => {
  if (e.rowIndex === -1) return;
  const data = e.row!.data!;
  numberedSteps.value[0].subtitle = data.siparis_belge_no;
  selectedRow.value = data;
  focusedRowKey.value = data.satir_id;
  selectedRow.value.siparis_belge_no = data.siparis_belge_no != null ? data.siparis_belge_no : '';
  textAreaValue.value = data.teknik_not1 != null ? data.teknik_not1 : '';
  // console.log(selectedRow.value);
};

const onContextMenuPreparing = (e: any) => {
  const data = e.row!.data!;
};

const handleOptionChanged = (e: any) => {
  if (e.fullName === "dataSource") {
    e.component.option("loadPanel.enabled", false); // Yükleme panelini kapat
  }
};

const toggleGoster = () => {
  generatePDF();
  goster.value = !goster.value;
};
const getData = async () => {
  try {
    loadingVisible.value = true;
    const response = await axios.get("/api/isEmriKapanmislar", {
      params: {
        filterValue: filterValue.value,
        filterValue1: filterValue1.value,
        coID: userData.value.co_id,
      },
    });
    gridData.value = response.data.emirler;
  } catch (error) {
    console.error("Veri çekilirken hata oluştu: ", error);
  } finally {
    loadingVisible.value = false;
  }
};

const refreshGrid = () => {
  const gridInstance = dataGridRef.value?.instance; // DataGrid bileşeninin referansı
  if (gridInstance) {
    gridInstance.refresh();
  }
};

onMounted(async () => {
  // await getData();
  pageTitleStore.setTitle("Kapanmış İş Emirleri");

  loadGridState();
  nextTick(() => {
    dataGridRef.value?.instance.clearSelection();
  });
  axios
    .post("/api/log-kayit", {
      userId: userData.value.id,
      sayfa: 'Kapalı İş emirleri',
      eylem: 'Yükleme',
    });
});

const Yenile = async () => {
  await getData(); // Veri çekme işlemi
};

const toggleExpandAll = () => {
  expandAll.value = !expandAll.value;
};

const userData = useCookie<any>("userData");

const onExporting = (e: DxDataGridTypes.ExportingEvent) => {
  const workbook = new Workbook();
  const worksheet = workbook.addWorksheet("KapanmisIsEmirleri");

  exportDataGrid({
    component: e.component,
    worksheet,
    autoFilterEnabled: true,
  }).then(() => {
    workbook.xlsx.writeBuffer().then((buffer) => {
      saveAs(
        new Blob([buffer], {type: "application/octet-stream"}),
        "KapanmisIsEmirleri.xlsx"
      );
    });
  });

  e.cancel = true;
};

const menuItems = [
  {text: 'Yenile'},
  {text: 'Düzen Yükle'},
  {text: 'Düzen Kaydet'},
  {text: 'Düzen Sıfırla'},
];

function itemClick({itemData}: DxContextMenuTypes.ItemClickEvent) {
  if (!itemData?.items) {
    switch (itemData?.text) {
      case 'Yenile':
        getData()
        break;
      case 'Düzen Yükle':
        loadGridState();
        break;
      case 'Düzen Kaydet':
        saveGridState();
        break;
      case 'Düzen Sıfırla':
        onStateResetClick();
        break;
      default:
        break;
    }
  }
}
</script>


<style>
.v-progress-linear {
  inline-size: 100% !important;
}

#grid {
  display: flex;
  flex-direction: column;
  block-size: 85vh;
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

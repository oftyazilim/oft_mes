<template>
  <VCard class="mt-0 pa-0">
    <VCardText class="mt-2 pa-2">
      <div>
        <DxContextMenu :data-source="menuItems" :width="200" target="#grid" @item-click="itemClick" />

        <DxDataGrid id="grid" :data-source="gridData" key-expr="item_id" :key="gridKey" :show-borders="true"
          ref="dataGridRef" :column-auto-width="false" :focused-row-enabled="true" :width="'100%'"
          :allow-column-reordering="true" :allow-column-resizing="true" column-resizing-mode="widget"
          v-model:focused-row-key="focusedRowKey" @content-ready="onContentReady" :selected-rows-keys="selectedRowKeys"
          @focused-row-changed="onFocusedRowChanged" @row-prepared="onRowPrepared"
          @selection-changed="onSelectionChanged" @row-dbl-click="UrunSorgula" @exporting="onExporting">

          <DxColumn type="buttons" :width="30">
            <DxGridButton hint="Detay Göster" icon="eyeopen" :visible="true" :disabled="false" @click="UrunSorgula" />
          </DxColumn>
          <DxColumn data-field="item_id" caption="ID" data-type="number" :width="70" :sort-index="0" sort-order="asc" />
          <DxColumn data-field="item_code" caption="Ürün Kodu" :width="150" />
          <DxColumn data-field="item_name" caption="Ürün Adı" :width="350" />
          <DxColumn data-field="item_name2" caption="Ürün Adı 2" :width="150" />
          <DxColumn data-field="ana_depo_miktar" data-type="number" caption="Ana Depo" :visible="true" :width="100"
            :format="{ type: 'fixedPoint', precision: 1, thousandsSeparator: ',', }" />
          <DxColumn data-field="ist_depo_miktar" data-type="number" caption="İst. Depo" :visible="true" :width="100"
            :format="{ type: 'fixedPoint', precision: 1, thousandsSeparator: ',', }" />
          <DxColumn data-field="satinalma" data-type="number" caption="Satınalma" :visible="true" :width="100"
            :format="{ type: 'fixedPoint', precision: 1, thousandsSeparator: ',', }" />
          <DxColumn data-field="satislar" data-type="number" caption="Satış" :visible="true" :width="100"
            :format="{ type: 'fixedPoint', precision: 1, thousandsSeparator: ',', }" />
          <DxColumn data-field="min_seviye" data-type="number" caption="Min" :visible="true" :width="100"
            :format="{ type: 'fixedPoint', precision: 1, thousandsSeparator: ',', }" />
          <DxColumn data-field="max_seviye" data-type="number" caption="Max" :visible="true" :width="100"
            :format="{ type: 'fixedPoint', precision: 1, thousandsSeparator: ',', }" />
          <DxColumn data-field="kategori_ad1" caption="Kategori Adı 1" :width="200" />
          <DxColumn data-field="kategori_ad2" caption="Kategori Adı 2" :width="200" />
          <DxColumn data-field="kategori_ad3" caption="Kategori Adı 3" :width="200" />
          <DxColumn data-field="kategori_ad4" caption="Kategori Adı 4" :width="200" :visible="false" />
          <DxColumn data-field="kategori_ad5" caption="Kategori Adı 5" :width="200" :visible="false" />
          <DxColumn data-field="kategori_ad6" caption="Kategori Adı 6" :width="200" :visible="false" />
          <DxColumn data-field="kategori_ad7" caption="Kategori Adı 7" :width="200" :visible="false" />
          <DxColumn data-field="kategori_ad8" caption="Kategori Adı 8" :width="200" :visible="false" />
          <DxColumn data-field="kategori_ad9" caption="Kategori Adı 9" :width="200" :visible="false" />
          <DxColumn data-field="kategori_ad10" caption="Kategori Adı 10" :width="200" :visible="false" />
          <DxColumn data-field="kategori_ad11" caption="Kategori Adı 11" :width="200" :visible="false" />
          <DxColumn data-field="kategori_ad12" caption="Kategori Adı 12" :width="200" :visible="false" />
          <DxColumn data-field="kategori_ad13" caption="Kategori Adı 13" :width="200" :visible="false" />
          <DxColumn data-field="kategori_ad14" caption="Kategori Adı 14" :width="200" :visible="false" />
          <DxColumn data-field="kategori_ad15" caption="Kategori Adı 15" :width="200" :visible="false" />
          <DxColumn data-field="kategori_ad16" caption="Kategori Adı 16" :width="200" :visible="false" />
          <DxColumn data-field="kategori_ad17" caption="Kategori Adı 17" :width="200" :visible="false" />
          <DxColumn data-field="kategori_ad18" caption="Kategori Adı 18" :width="200" :visible="false" />
          <DxColumn data-field="kategori_ad19" caption="Kategori Adı 19" :width="200" :visible="false" />
          <DxColumn data-field="kategori_ad20" caption="Kategori Adı 20" :width="200" :visible="false" />
          <DxColumn data-field="add_string08" caption="Ölü Stok" :width="100" />
          <DxColumn data-field="add_dec07" :width="80" data-type="number"
            :format="{ type: 'fixedPoint', precision: 1, thousandsSeparator: ',', }" />
          <DxColumn data-field="add_dec08" :width="80" data-type="number"
            :format="{ type: 'fixedPoint', precision: 3, thousandsSeparator: ',', }" />
          <DxColumn data-field="add_string07" caption="Firma" :width="80" />

          <DxToolbar>
            <DxItem location="before" name="groupPanel" />
            <DxItem location="before" locate-in-menu="auto" template="collapseTemplate" />
            <DxItem location="before" locate-in-menu="auto" template="filtreKategori1" />
            <DxItem location="before" template="totalRecordTemplate" />
            <DxItem location="after" locate-in-menu="auto" template="yenileTemplate"
              menu-item-template="menuYenileTemplate" @click="Yenile" />
            <DxItem name="exportButton" />
            <DxItem name="columnChooserButton" />
            <DxItem name="searchPanel" />
          </DxToolbar>

          <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always" />
          <DxSelection mode="multiple" select-all-mode="allPages" show-check-boxes-mode="onClick" />
          <DxGrouping :auto-expand-all="expandAll" />
          <DxGroupPanel :visible="true" />
          <DxHeaderFilter :visible="true" />
          <DxFilterPanel :visible="true" />
          <DxFilterRow :visible="true" />
          <DxSearchPanel :visible="true" :width="240" />
          <DxSorting mode="multiple" />
          <DxExport :enabled="true" :allow-export-selected-data="false" />
          <DxLoadPanel v-model:visible="loadingVisible" :show-indicator="true" :show-pane="true" :shading="true"
            :message="loadingMessage || 'Yükleniyor...'" :width="260" :height="120" />

          <DxColumnChooser height="540px" :enabled="true" mode="select">
            <DxColumnChooserSearch :enabled="true" />
            <DxColumnChooserSelection :allow-select-all="true" :select-by-click="true" :recursive="true" />
          </DxColumnChooser>

          <DxSummary>
            <DxGroupItem :align-by-column="true" column="item_name" summary-type="count" display-format="{0} adet"
              alignment="right" :customize-text="formatSummaryText" />
            <!-- <DxTotalItem :align-by-column="true" column="item_name" summary-type="count" display-format="{0} adet"
              :alignment="right" :customize-text="formatSummaryText" /> -->
          </DxSummary>

          <template #yenileTemplate>
            <DxButton icon="refresh" styling-mode="text" hint="Yenile" />
          </template>
          <template #menuYenileTemplate>
            <div style="display: flex; align-items: center;">
              <i class="dx-icon dx-icon-refresh"></i>
              <span style="margin-inline-start: 8px;">Yenile</span>
            </div>
          </template>

          <template #collapseTemplate>
            <DxButton :icon="expandAll ? 'collapse' : 'expand'" hint="expandAll ? 'Daralt' : 'Genişlet'" width="40"
              height="40" styling-mode="text" @click="toggleExpandAll" />
          </template>

          <template #filtreKategori1>
            <div style="inline-size: 200px; margin-block-start: -10px; margin-inline-start: 5px;">
              <DxSelectBox :data-source="kategori1" v-model:value="ktg1" :show-clear-button="true" label="Kategori 1"
                label-mode="floating" display-expr="kategori_ad1" value-expr="kategori_ad1" style="inline-size: 100%;"
                search-mode="contains" search-expr="ktg_adi" :search-timeout="200" :search-enabled="true" />
            </div>
          </template>

          <template #totalRecordTemplate>
            <div class="informer">
              <div class="count">{{ formatNumber(totalRecord) }}</div>
              <span>Toplam Kayıt</span>
            </div>
          </template>
        </DxDataGrid>
      </div>
    </VCardText>
  </VCard>
  <VRow>
    <VCol md="6" class="mt-4">
      <VCard class="overflow-visible">
        <VCardText>
          <div class="w-100 sticky-header overflow-hidden rounded-t">
            <h5 class="text-h5 mb-2">
              Depo Bilgileri
            </h5>

            <DxDataGrid id="gridDepo" ref="dataGridRefD" :data-source="gridDataDepo" :show-borders="true"
              :column-auto-width="true" :row-alternation-enabled="true" :allow-column-resizing="true"
              column-resizing-mode="widget" @cell-prepared="onCellPreparedD" height="250px">

              <!-- <DxColumn data-field="co_code" caption="Firma" :visible="true" :min-width="60" /> -->
              <DxColumn data-field="branch_desc" caption="Firma Adı" :visible="true" :width="120" />
              <DxColumn data-field="whouse_code" caption="Depo Kodu" :visible="true" :width="80" />
              <DxColumn data-field="description" caption="Depo Adı" :visible="true" :min-width="180" />
              <DxColumn data-field="add_string01" caption="Raf Yeri" :visible="true" :width="80" />
              <DxColumn data-field="qty_prm" caption="Miktar" data-type="number" :visible="true" :min-width="50"
                :format="{ type: 'fixedPoint', precision: 2, thousandsSeparator: ',', }" />
              <DxColumn data-field="unit_code" caption="Birim" :visible="true" :min-width="40" />
            </DxDataGrid>
            <h5 class="text-h5 mt-2">
              Ana Depo: <span class="toplam">{{ formatNumber(formDatam.toplama, 1) }}</span>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; İstasyon Depo: <span class="toplam">{{ formatNumber(formDatam.toplami, 1)
                }}</span>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Genel: <span class="toplam">{{
                formatNumber(formDatam.toplama + formDatam.toplami, 1) }}</span>
            </h5>

          </div>
        </VCardText>
      </VCard>
    </VCol>
    <VCol cols="6" class="mt-4 ps-0">
      <VCard class="overflow-visible">
        <VCardText>
          <div class="w-100 sticky-header overflow-hidden rounded-t">
            <h5 class="text-h5 mb-2">
              Sayım Bilgileri
            </h5>

            <DxDataGrid id="gridSayim" :data-source="gridDataSayim" :show-borders="true" :column-auto-width="true"
              :row-alternation-enabled="true" :allow-column-resizing="true" column-resizing-mode="widget"
              height="250px">

              <DxColumn data-field="id" caption="#" :visible="true" :min-width="50" />
              <DxColumn data-field="qty_prm" caption="Miktar" data-type="number" :visible="true" :min-width="60"
                :format="{ type: 'fixedPoint', precision: 2, thousandsSeparator: ',', }" />
              <DxColumn data-field="note1" caption="Raf Yeri" :visible="true" :min-width="60" />
              <DxColumn data-field="whouse_code" caption="Depo" :visible="true" :min-width="40" />
              <DxColumn data-field="sorumlu" caption="Sorumlu" :visible="true" :min-width="80" />
              <DxColumn data-field="create_date" caption="Sayım Tarihi" :visible="true" :min-width="60" data-type="date"
                :format="{
                  formatter: (date: string | number | Date) => {
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
              <DxColumn data-field="doc_no" caption="Sayım No" :visible="true" :min-width="60" />
              <DxColumn data-field="description" caption="Depo Adı" :visible="true" :min-width="60" />
              <DxColumn data-field="serial_m_id" :visible="true" :min-width="60" />
              <DxColumn data-field="barcode" :visible="true" :min-width="60" />
              <DxColumn data-field="item_id" :visible="true" :min-width="60" />
              <DxColumn data-field="item_code" :visible="true" :min-width="60" />
              <DxColumn data-field="item_name" :visible="true" :min-width="60" />
              <DxColumn data-field="qty" :visible="true" :min-width="60" />
              <DxColumn data-field="qty_free_prm" :visible="true" :min-width="60" />
              <DxColumn data-field="qty_free_sec" :visible="true" :min-width="60" />
              <DxColumn data-field="create_user_id" :visible="true" :min-width="60" />
              <DxColumn data-field="update_user_id" :visible="true" :min-width="60" />
              <DxColumn data-field="update_date" :visible="true" :min-width="60" data-type="date" :format="{
                formatter: (date: string | number | Date) => {
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
              <DxColumn data-field="whouse_id" :visible="true" :min-width="60" />
              <DxColumn data-field="location_id" :visible="true" :min-width="60" />
              <DxColumn data-field="us_id" :visible="true" :min-width="60" />
              <DxColumn data-field="unit_id" :visible="true" :min-width="60" />
              <DxColumn data-field="cycle_count_m_id" :visible="true" :min-width="60" />
              <DxColumn data-field="cycle_count_d_id" :visible="true" :min-width="60" />
              <DxColumn data-field="co_id" :visible="true" :min-width="60" />
              <DxColumn data-field="branch_id" :visible="true" :min-width="60" />
              <DxColumn data-field="is_passive" :visible="true" :min-width="60" />
            </DxDataGrid>
            <h5 class="text-h5 mt-2">
              Toplam: <span class="toplam">{{ formatNumber(formDatam.toplamSayim, 1) }}</span>
            </h5>

          </div>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
  <VRow>
    <VCol md="6" class="mt-0 pt-0">
      <VCard class="overflow-visible">
        <VCardText>
          <div class="w-100 sticky-header overflow-hidden rounded-t">
            <h5 class="text-h5 mb-2">
              Satınalma Geçmişi
            </h5>

            <DxDataGrid id="gridSatinAlma" ref="dataGridRefSA" :data-source="gridDataSatinAlma" :show-borders="true"
              :column-auto-width="false" :row-alternation-enabled="true" :allow-column-resizing="true"
              column-resizing-mode="widget" height="250px" width="100%">
              <DxColumn data-field="fatura_tarih" caption="Fatura Tarihi" :visible="true" :width="100" data-type="date"
                :format="{
                  formatter: (date: string | number | Date) => {
                    const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                      year: 'numeric',
                      month: '2-digit',
                      day: '2-digit',
                    }).format(new Date(date));
                    return formattedDate.replace(/\//g, '.');
                  },
                }" sort-order="desc" />
              <DxColumn data-field="fatura_no" caption="Fatura No" :visible="true" :width="150" />
              <DxColumn data-field="CARI_ADI" caption="Cari Adı" :visible="true" :min-width="120" />
              <DxColumn data-field="miktar" caption="Miktar" data-type="number" :visible="true" :width="100"
                :format="{ type: 'fixedPoint', precision: 1, thousandsSeparator: ',', }" />
              <DxColumn data-field="birim" caption="Birim" :visible="true" :width="60" />
              <DxColumn data-field="birim_fiyat" data-type="number" caption="Birim Fiyat $" :visible="true" :width="100"
                :format="{ type: 'fixedPoint', precision: 3, thousandsSeparator: ',', }" />
              <DxColumn data-field="USD_NET_TUTAR" data-type="number" caption="Net Tutar $" :visible="true" :width="100"
                :format="{ type: 'fixedPoint', precision: 2, thousandsSeparator: ',', }" />
              <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always" />
            </DxDataGrid>
            <h5 class="text-h5 mt-2">
              Toplam: <span class="toplam">{{ formatNumber(toplamAlim, 1) }}</span>
            </h5>

          </div>
        </VCardText>
      </VCard>
    </VCol>
    <VCol cols="6" class="ps-0 mt-0 pt-0">
      <VCard class="overflow-visible">
        <VCardText>
          <div class="w-100 sticky-header overflow-hidden rounded-t">
            <h5 class="text-h5 mb-2">
              Satış Geçmişi
            </h5>

            <DxDataGrid id="gridSiparis" :data-source="gridDataSiparis" :show-borders="true" :column-auto-width="false"
              :row-alternation-enabled="true" :allow-column-resizing="true" column-resizing-mode="widget" height="250px"
              width="100%">
              <DxColumn data-field="fatura_tarih" caption="Fatura Tarihi" :visible="true" :width="100" data-type="date"
                :format="{
                  formatter: (date: string | number | Date) => {
                    const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                      year: 'numeric',
                      month: '2-digit',
                      day: '2-digit',
                    }).format(new Date(date));
                    return formattedDate.replace(/\//g, '.');
                  },
                }" sort-order="desc" />
              <DxColumn data-field="fatura_no" caption="Fatura No" :visible="true" :width="150" />
              <DxColumn data-field="CARI_ADI" caption="Cari Adı" :visible="true" :min-width="120" />
              <DxColumn data-field="miktar" caption="Miktar" data-type="number" :visible="true" :width="100"
                :format="{ type: 'fixedPoint', precision: 1, thousandsSeparator: ',', }" />
              <DxColumn data-field="birim" caption="Birim" :visible="true" :width="60" />
              <DxColumn data-field="birim_fiyat" data-type="number" caption="Birim Fiyat $" :visible="true" :width="100"
                :format="{ type: 'fixedPoint', precision: 3, thousandsSeparator: ',', }" />
              <DxColumn data-field="USD_NET_TUTAR" data-type="number" caption="Net Tutar $" :visible="true" :width="100"
                :format="{ type: 'fixedPoint', precision: 2, thousandsSeparator: ',', }" />
              <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always" />
            </DxDataGrid>
            <h5 class="text-h5 mt-2">
              Toplam: <span class="toplam">{{ formatNumber(toplamSiparis, 1) }}</span>
            </h5>

          </div>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
  <VRow>
    <VCol md="6" class="mt-0 pt-0">
      <VCard class="overflow-visible">
        <VCardText>
          <div class="w-100 sticky-header overflow-hidden rounded-t">
            <h5 class="text-h5 mb-2">
              Üretim Sarf Bilgileri
            </h5>

            <DxDataGrid id="gridUretilmis" ref="dataGridRefUr" :data-source="gridDataUretimSarf" :show-borders="true"
              :column-auto-width="false" :row-alternation-enabled="true" :allow-column-resizing="true"
              column-resizing-mode="widget" height="250px" width="100%">
              <DxColumn data-field="create_date" caption="Üretim Tarihi" :visible="true" :width="100" data-type="date"
                :format="{
                  formatter: (date: string | number | Date) => {
                    const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                      year: 'numeric',
                      month: '2-digit',
                      day: '2-digit',
                    }).format(new Date(date));
                    return formattedDate.replace(/\//g, '.');
                  },
                }" sort-order="desc" />
              <DxColumn data-field="co_desc" caption="Firma" :visible="true" :width="150" />
              <DxColumn data-field="worder_no" caption="İş Emri No" :visible="true" :width="110" />
              <DxColumn data-field="cari_ad" caption="Müşteri" :visible="true" :min-width="120" />
              <DxColumn data-field="description" caption="Depo" :visible="true" :min-width="120" />
              <DxColumn data-field="sarf" caption="Miktar" data-type="number" :visible="true" :width="100"
                :format="{ type: 'fixedPoint', precision: 1, thousandsSeparator: ',', }" />

              <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always" />
              <DxHeaderFilter :visible="true" />

              <DxSummary>
                <DxTotalItem :align-by-column="true" column="sarf" summary-type="sum" display-format="{0}"
                  alignment="right" :customize-text="formatSummaryText" />
              </DxSummary>
            </DxDataGrid>
            <h5 class="text-h5 mt-2">
              Toplam: <span class="toplam">{{ formatNumber(toplamSarf, 1) }}</span>
            </h5>

          </div>
        </VCardText>
      </VCard>
    </VCol>
    <VCol cols="6" class="ps-0 mt-0 pt-0">
      <VCard class="overflow-visible">
        <VCardText>
          <div class="w-100 sticky-header overflow-hidden rounded-t">
            <h5 class="text-h5 mb-2">
              Plan Sarf Bilgileri
            </h5>

            <DxDataGrid id="gridPlanlanmis" ref="dataGridRefPl" :data-source="gridDataPlanSarf" :show-borders="true"
              :column-auto-width="false" :row-alternation-enabled="true" :allow-column-resizing="true"
              column-resizing-mode="widget" height="250px" width="100%">
              <DxColumn data-field="create_date" caption="Üretim Tarihi" :visible="true" :width="100" data-type="date"
                :format="{
                  formatter: (date: string | number | Date) => {
                    const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                      year: 'numeric',
                      month: '2-digit',
                      day: '2-digit',
                    }).format(new Date(date));
                    return formattedDate.replace(/\//g, '.');
                  },
                }" sort-order="desc" />
              <DxColumn data-field="co_desc" caption="Firma" :visible="true" :width="150" />
              <DxColumn data-field="worder_no" caption="İş Emri No" :visible="true" :width="110" />
              <DxColumn data-field="cari_ad" caption="Müşteri" :visible="true" :min-width="120" />
              <DxColumn data-field="description" caption="Depo" :visible="true" :min-width="120" />
              <DxColumn data-field="sarf" caption="Miktar" data-type="number" :visible="true" :width="100"
                :format="{ type: 'fixedPoint', precision: 1, thousandsSeparator: ',', }" />

              <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always" />
              <DxHeaderFilter :visible="true" />

              <DxSummary>
                <DxTotalItem :align-by-column="true" column="sarf" summary-type="sum" display-format="{0}"
                  alignment="right" :customize-text="formatSummaryText" />
              </DxSummary>
            </DxDataGrid>
            <h5 class="text-h5 mt-2">
              Toplam: <span class="toplam">{{ formatNumber(toplamPlan, 1) }}</span>
            </h5>

          </div>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>

  <VRow>
    <VCol md="6" class="mt-0 pt-0">
      <VCard class="overflow-visible">
        <VCardText>
          <div class="w-100 sticky-header overflow-hidden rounded-t">
            <h5 class="text-h5 mb-2">
              Satınalma Fişleri (Açıklar)
            </h5>

            <DxDataGrid id="gridSatinAlmaFisleri" ref="dataGridRefSAF" :data-source="gridDataFisler"
              :show-borders="true" :column-auto-width="false" :row-alternation-enabled="true"
              :allow-column-resizing="true" column-resizing-mode="widget" height="150px" width="100%">
              <DxColumn data-field="teslim_tarihi" caption="Teslim Tarihi" :visible="true" :width="100" data-type="date"
                :format="{
                  formatter: (date: string | number | Date) => {
                    const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                      year: 'numeric',
                      month: '2-digit',
                      day: '2-digit',
                    }).format(new Date(date));
                    return formattedDate.replace(/\//g, '.');
                  },
                }" sort-order="desc" />
              <DxColumn data-field="BELGE_NO" caption="Belge No" :visible="true" :width="100" />
              <DxColumn data-field="CARI_AD" caption="Cari Adı" :visible="true" :min-width="120" />
              <DxColumn data-field="KALAN_MIKTAR" caption="Miktar" data-type="number" :visible="true" :width="100"
                :format="{ type: 'fixedPoint', precision: 1, thousandsSeparator: ',', }" />
              <DxColumn data-field="birim_fiyat" data-type="number" caption="Birim Fiyat $" :visible="true" :width="100"
                :format="{ type: 'fixedPoint', precision: 3, thousandsSeparator: ',', }" />
              <DxColumn data-field="USD_NET_TUTAR" data-type="number" caption="Net Tutar $" :visible="true" :width="100"
                :format="{ type: 'fixedPoint', precision: 2, thousandsSeparator: ',', }" />
              <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always" />
            </DxDataGrid>
            <h5 class="text-h5 mt-2">
              Toplam: <span class="toplam">{{ formatNumber(toplamFis, 1) }}</span>
            </h5>

          </div>
        </VCardText>
      </VCard>
    </VCol>
    <VCol cols="6" class="ps-0 mt-0 pt-0">
      <VCard class="overflow-visible">
        <VCardText>
          <div class="w-100 sticky-header overflow-hidden rounded-t">
            <h5 class="text-h5 mb-2">
              Talepler
            </h5>

            <DxDataGrid id="gridTalepler" :data-source="gridDataTalepler" :show-borders="true"
              :column-auto-width="false" :row-alternation-enabled="true" :allow-column-resizing="true"
              column-resizing-mode="widget" height="150px" width="100%">
              <DxColumn data-field="talep_tarihi" caption="Talep Tarihi" :visible="true" :width="110" data-type="date"
                :format="{
                  formatter: (date: string | number | Date) => {
                    const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                      year: 'numeric',
                      month: '2-digit',
                      day: '2-digit',
                    }).format(new Date(date));
                    return formattedDate.replace(/\//g, '.');
                  },
                }" sort-order="desc" />
              <DxColumn data-field="onay_tarihi" caption="Onay Tarihi" :visible="true" :width="110" data-type="date"
                :format="{
                  formatter: (date: string | number | Date) => {
                    const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                      year: 'numeric',
                      month: '2-digit',
                      day: '2-digit',
                    }).format(new Date(date));
                    return formattedDate.replace(/\//g, '.');
                  },
                }" />
              <DxColumn data-field="talep_no" caption="Talep No" :visible="true" :width="100" />
              <DxColumn data-field="co_id" caption="Co ID" :visible="true" :min-width="60" />
              <DxColumn data-field="note1" caption="Not 1" :visible="true" :min-width="100" />
              <DxColumn data-field="ONAY_DURUMU" caption="Onay" :visible="true" :min-width="70" />
              <DxColumn data-field="miktar" caption="Miktar" data-type="number" :visible="true" :width="80"
                :format="{ type: 'fixedPoint', precision: 1, thousandsSeparator: ',', }" />

              <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always" />
            </DxDataGrid>
            <h5 class="text-h5 mt-2">
              Toplam: <span class="toplam">{{ formatNumber(toplamTalep, 1) }}</span>
            </h5>

          </div>
        </VCardText>
      </VCard>
    </VCol>
    <VCol md="12" class="mt-0 pt-0">
      <VCard class="overflow-visible">
        <VCardText>
          <div class="w-100 sticky-header overflow-hidden rounded-t">
            <h5 class="text-h5 mb-2">
              Satış Fişleri (Açıklar)
            </h5>

            <DxDataGrid id="gridSatisFisleri" ref="dataGridRefSF" :data-source="gridDataSatisFisler"
              :show-borders="true" :column-auto-width="false" :row-alternation-enabled="true"
              :allow-column-resizing="true" column-resizing-mode="widget" height="200px" width="100%">
              <DxColumn data-field="BELGE_NO" caption="Belge No" :visible="true" :width="100" />
              <DxColumn data-field="BELGE_TARIHI" caption="Belge Tarihi" :visible="true" :width="120" data-type="date"
                :format="{
                  formatter: (date: string | number | Date) => {
                    const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                      year: 'numeric',
                      month: '2-digit',
                      day: '2-digit',
                    }).format(new Date(date));
                    return formattedDate.replace(/\//g, '.');
                  },
                }" />
              <DxColumn data-field="teslim_tarihi" caption="Teslim Tarihi" :visible="true" :width="120" data-type="date"
                :format="{
                  formatter: (date: string | number | Date) => {
                    const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                      year: 'numeric',
                      month: '2-digit',
                      day: '2-digit',
                    }).format(new Date(date));
                    return formattedDate.replace(/\//g, '.');
                  },
                }" sort-order="desc" />
              <DxColumn data-field="CARI_AD" caption="Cari Adı" :visible="true" :min-width="120" />
              <DxColumn data-field="CARI_TIPI" caption="Cari Tipi" :visible="true" :min-width="90" />
              <DxColumn data-field="ODEME_VADESI" caption="Ödeme Vadesi" :visible="true" :min-width="100" />
              <DxColumn data-field="satici_adi" caption="Satıcı Adı" :visible="true" :width="100" />
              <DxColumn data-field="sip_olusturan" caption="Fiş Oluşturan" :visible="true" :width="100" />
              <DxColumn data-field="MIKTAR" caption="Miktar" data-type="number" :visible="true" :width="100"
                :format="{ type: 'fixedPoint', precision: 1, thousandsSeparator: ',', }" />
              <DxColumn data-field="KALAN_MIKTAR" caption="Kalan Miktar" data-type="number" :visible="true" :width="100"
                :format="{ type: 'fixedPoint', precision: 1, thousandsSeparator: ',', }" />
              <DxColumn data-field="BIRIM_KOD" caption="Birim" :visible="true" :min-width="100" />
              <DxColumn data-field="birim_fiyat" data-type="number" caption="Birim Fiyat $" :visible="true" :width="100"
                :format="{ type: 'fixedPoint', precision: 3, thousandsSeparator: ',', }" />
              <DxColumn data-field="USD_NET_TUTAR" data-type="number" caption="Net Tutar $" :visible="true" :width="100"
                :format="{ type: 'fixedPoint', precision: 2, thousandsSeparator: ',', }" />

              <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always" />
            </DxDataGrid>
            <h5 class="text-h5 mt-2">
              Toplam: <span class="toplam">{{ formatNumber(toplamSatisFis, 1) }}</span>
            </h5>

          </div>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>


  <VRow>
    <VCol cols="6" class="mt-0 pt-0">
      <VCard class="overflow-visible">
        <VCardText>
          <div class="w-100 sticky-header overflow-hidden rounded-t">
            <h5 class="text-h5 mb-2">
              Yıllık Özet Tablo
            </h5>

            <DxDataGrid :data-source="hareketOzet" :show-borders="true" :column-auto-width="true"
              :word-wrap-enabled="true" height="250px">
              <DxColumn data-field="hareket" caption="" width="50" />

              <DxColumn :data-field="String(new Date().getFullYear())" data-type="number"
                :caption="`Toplam ${new Date().getFullYear()}`" :format="{ type: 'fixedPoint', precision: 0 }" />
              <DxColumn :data-field="String(new Date().getFullYear() - 1)"
                :caption="`Toplam ${new Date().getFullYear() - 1}`" data-type="number"
                :format="{ type: 'fixedPoint', precision: 0 }" />

              <DxColumn v-for="(ay, i) in ayKolonlari.slice().reverse()" :key="i" :data-field="ay" :caption="ay"
                data-type="number" alignment="right" :format="{ type: 'fixedPoint', precision: 0 }" />
            </DxDataGrid>

          </div>
        </VCardText>
      </VCard>
    </VCol>
    <VCol cols="6" class="ps-0 mt-0 pt-0">
      <VCard class="overflow-visible">
        <VCardText>
          <div class="w-100 sticky-header overflow-hidden rounded-t">
            <h5 class="text-h5 mb-2">
              Yıllık Hareket Grafiği
            </h5>

            <DxChart id="chart" :data-source="tersDataSource" palette="Soft" height="300"
              style="margin-block-start: -50px;">
              <DxCommonSeriesSettings argument-field="ay" />

              <DxValueAxis :tick-interval="300" name="frequency" position="left" sort-order="asc" />

              <DxSeries name="Giriş" value-field="girisDeger" axis="frequency" type="bar" color="#fac29a" />
              <DxSeries name="Çıkış" value-field="cikisDeger" axis="frequency" type="bar" color="#6b71c3" />

              <DxArgumentAxis>
                <DxLabel overlapping-behavior="stagger" />
              </DxArgumentAxis>


              <DxTooltip :enabled="true" :shared="true" :customize-tooltip="customizeTooltip" />

              <DxLegend horizontal-alignment="center" />
            </DxChart>
          </div>

        </VCardText>
      </VCard>
    </VCol>
  </VRow>



  <VRow>
    <VCol cols="12">
      <VCard>
        <VCardText>
          <h5 class="text-h5 mb-2">
            Ürün Fotoğrafları
          </h5>

          <VDivider class="my-2" />

          <v-row>
            <v-col v-for="photo in photos" :key="photo.id" cols="12" sm="4" md="3">
              <v-img :src="photo.url" max-height="200" style=" cursor: pointer;margin-inline-start: -18px;"
                class="rounded-lg" @click="previewPhoto(photo.url)" />
              <VBtn icon="tabler-trash" color="error" rounded @click="deletePhoto(photo.id)" class="mt-1"></VBtn>

            </v-col>
          </v-row>

        </VCardText>
      </VCard>
    </VCol>
  </VRow>

  <v-dialog v-model="previewDialog" max-width="1200">
    <v-card>
      <v-img :src="selectedPhoto" class="ma-4" aspect-ratio="1.5" />
      <v-card-actions>
        <v-spacer />
        <v-btn text @click="previewDialog = false">Kapat</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <DxPopup v-model:visible="popupKategoriVisible" :width="400" :height="330" :hide-on-outside-click="false"
    :show-close-button="true" title="Kategori Güncelleme ">
    <template #content>
      <span class="text-h6 mt-4">Seçili Kayıt Sayısı: {{ selectedRows.length }}</span>
      <VCol cols="12">
        <DxSelectBox :data-source="alanlar" v-model:value="alan" label="Kategori Alanı" label-mode="floating"
          display-expr="kategori_baslik" value-expr="kategori_alan" style="inline-size: 100%;"
          @value-changed="getKategoriler()" />
      </VCol>
      <VCol cols="12">
        <DxSelectBox :data-source="kategoriler" v-model:value="kategori" label="Kategori Değeri" label-mode="floating"
          display-expr="description" value-expr="categories_id" style="inline-size: 100%;" />
        <span style="font-size: 12px; font-style: italic;">* Silmek için boş bırakınız</span>
      </VCol>
    </template>
    <DxToolbarItem widget="dxButton" toolbar="bottom" location="center" :options="kaydetOptions"
      @click="KategoriKaydet()" />
    <DxToolbarItem widget="dxButton" toolbar="bottom" location="center" :options="vazgecOptions" />
  </DxPopup>

  <!-- Global overlay fallback for clearer loading message -->
  <VOverlay :model-value="loadingVisible" class="d-flex align-center justify-center" persistent scrim>
    <VCard class="pa-6 text-center" elevation="12" width="300">
      <VProgressCircular indeterminate color="primary" size="48" />
      <div class="mt-4 text-h6">{{ loadingMessage || 'Yükleniyor...' }}</div>
    </VCard>
  </VOverlay>

</template>


<script setup lang="ts">
import { usePageTitleStore } from "@/stores/pageTitle";
import axios from "axios";
import { DxButton } from "devextreme-vue/button";
import DxChart, {
  DxArgumentAxis,
  DxCommonSeriesSettings,
  DxLabel,
  DxLegend,
  DxSeries,
  DxTooltip,
  DxValueAxis
} from 'devextreme-vue/chart';
import type { DxContextMenuTypes } from 'devextreme-vue/context-menu';
import DxContextMenu from 'devextreme-vue/context-menu';
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
  DxButton as DxGridButton,
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
import { DxPopup, DxToolbarItem } from 'devextreme-vue/popup';
import DxSelectBox from 'devextreme-vue/select-box';
import { exportDataGrid } from "devextreme/excel_exporter";
import notify from 'devextreme/ui/notify';
import { Workbook } from "exceljs";
import { saveAs } from "file-saver-es";
import { computed, onMounted, ref } from 'vue';

// const pageSizes: (number | PagerPageSize)[] = [5, 10, 15, 'all'];
document.title = 'OFT - Stok Listesi';
const pageTitleStore = usePageTitleStore();
const userData = useCookie<any>("userData");
const gridKey = ref('item_id');
const focusedRowKey = ref<number | null>(null);
const expandAll = ref(false);
const dataGridRef = ref();
const position = { of: 'window' };
const gridData = ref([])
const gridDataDepo = ref([])
const gridDataSayim = ref([])
const gridDataSatinAlma = ref([])
const gridDataSiparis = ref([])
const gridDataUretimSarf = ref([])
const gridDataPlanSarf = ref([])
const gridDataFisler = ref([])
const gridDataSatisFisler = ref([])
const gridDataTalepler = ref([])
const kategori1 = ref([])
const ktg1 = ref('')
const loadingVisible = ref<boolean>(false);
const loadingMessage = ref('');
const loadingStartAt = ref<number>(0);

const showLoading = (msg: string) => {
  loadingMessage.value = msg;
  loadingStartAt.value = Date.now();
  loadingVisible.value = true;
};

const hideLoading = async () => {
  const elapsed = Date.now() - loadingStartAt.value;
  const min = 300; // ms minimum visible
  if (elapsed < min) await new Promise(r => setTimeout(r, min - elapsed));
  loadingVisible.value = false;
  loadingMessage.value = '';
};
const totalRecord = ref(0);
const toplamAlim = ref(0);
const toplamSiparis = ref(0);
const toplamSarf = ref(0);
const toplamPlan = ref(0);
const toplamFis = ref(0);
const toplamSatisFis = ref(0);
const toplamTalep = ref(0);
const selectedRows = ref([])
const selectedRow = ref<any>(null)
const selectedRowKeys = ref<number[]>([])
const popupKategoriVisible = ref(false)
const kategori = ref('')
const alan = ref('')
const kategoriler = ref([])
const hareketOzet = ref([])
const hareketGrafik = ref([])

const ayKolonlari = ref(
  Array.from({ length: 12 }, (_, i) => {
    const d = new Date()
    d.setMonth(d.getMonth() - (11 - i))

    const yy = String(d.getFullYear()).slice(-2)
    const mm = String(d.getMonth() + 1).padStart(2, '0')

    return `${yy}-${mm}`
  })
)

const customizeTooltip = (info: { seriesName: string; value: number }) => ({
  text: `${info.seriesName}: ${info.value}`,
})

const alanlar = ref([
  { kategori_alan: '1', kategori_baslik: 'Kategori Adı 1' },
  { kategori_alan: '2', kategori_baslik: 'Kategori Adı 2' },
  { kategori_alan: '3', kategori_baslik: 'Kategori Adı 3' },
  { kategori_alan: '4', kategori_baslik: 'Kategori Adı 4' },
  { kategori_alan: '5', kategori_baslik: 'Kategori Adı 5' },
  { kategori_alan: '6', kategori_baslik: 'Kategori Adı 6' },
  { kategori_alan: '7', kategori_baslik: 'Kategori Adı 7' },
  { kategori_alan: '8', kategori_baslik: 'Kategori Adı 8' },
  { kategori_alan: '9', kategori_baslik: 'Kategori Adı 9' },
  { kategori_alan: '10', kategori_baslik: 'Kategori Adı 10' },
  { kategori_alan: '11', kategori_baslik: 'Kategori Adı 11' },
  { kategori_alan: '12', kategori_baslik: 'Kategori Adı 12' },
  { kategori_alan: '13', kategori_baslik: 'Kategori Adı 13' },
  { kategori_alan: '14', kategori_baslik: 'Kategori Adı 14' },
  { kategori_alan: '15', kategori_baslik: 'Kategori Adı 15' },
  { kategori_alan: '16', kategori_baslik: 'Kategori Adı 16' },
  { kategori_alan: '17', kategori_baslik: 'Kategori Adı 17' },
  { kategori_alan: '18', kategori_baslik: 'Kategori Adı 18' },
  { kategori_alan: '19', kategori_baslik: 'Kategori Adı 19' },
  { kategori_alan: '20', kategori_baslik: 'Kategori Adı 20' },
])

// Ürün bilgilerini tutacak form verisi (artık bir obje!)
const formDatam = ref({
  barkod: '',
  urunID: 0,
  urunKodu: '',
  urunAdi: '',
  urunAdi2: '',
  toplama: 0,
  toplami: 0,
  toplamSayim: 0,
})

interface PhotoItem { id: number; url: string }
const photos = ref<PhotoItem[]>([])
const preview = ref('')
const previewDialog = ref(false)
const selectedPhoto = ref('')

const onSelectionChanged = (e: any) => {
  selectedRows.value = e.selectedRowsData
  selectedRowKeys.value = e.selectedRowKeys
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
    popupKategoriVisible.value = false
  },
}

function formatSummaryText(itemInfo: { value: string | number | Date; valueText: string }) {
  const numeric = typeof itemInfo.value === 'number' ? itemInfo.value : Number(itemInfo.value) || 0
  return new Intl.NumberFormat('tr-TR', { maximumFractionDigits: 0 }).format(numeric);
}

const formatNumber = (numara: number, digit: number = 0) => {
  return new Intl.NumberFormat('tr-TR', {
    minimumFractionDigits: digit,
    maximumFractionDigits: digit,
  }).format(numara)
}

const getData = async () => {
  showLoading('Liste yükleniyor...');
  try {
    const response = await axios.get('/api/stok-listele', {
      params: {
        kategoriad1: ktg1.value,
      }
    })
    gridData.value = response.data.data
  } catch (error) {
    console.error('Veri çekilirken hata oluştu: ', error)
  } finally {
    hideLoading();
  }
}

const getVeriler = async () => {
  loadingVisible.value = true;
  try {
    const response = await axios.get('/api/stok-param-al')

    kategori1.value = response.data.ktg1
  } catch (error) {
    console.error('Veri çekilirken hata oluştu: ', error)
  }
  loadingVisible.value = false;
}

const getKategoriler = async () => {
  loadingVisible.value = true
  try {
    const response = await axios.get('/api/kategorial', {
      params: {
        kategoriad: alan.value,
      }
    })

    kategoriler.value = response.data.kategoriler
  }
  catch (error) {
    console.error('Veri çekilirken hata oluştu: ', error)
  }
  finally {
    loadingVisible.value = false
  }
}

const OluStok = async (deger: string) => {
  loadingVisible.value = true
  if (!selectedRows.value) {
    console.warn('Seçili satır yok!')
    return
  }

  const updateData = selectedRows.value.map((row: any) => {
    return {
      itemID: row.item_id,
      alan: 'add_string08',
      deger: deger,
    }
  })

  const userID = userData.value.id

  try {
    const response = await axios.post('/api/kategori-guncelle', { updateData, userID })
    selectedRows.value = []
    clearSelection()
    notify('Kayıt başarıyla güncellendi...', 'success', 1500)

    // if (response.data.success) {
    popupKategoriVisible.value = false
    getData()
    // }
  } catch (error) {
    console.error('Veri çekilirken hata oluştu: ', error)
  } finally {
    loadingVisible.value = false
  }
}

const KategoriKaydet = async () => {
  if (!alan.value) {
    notify('Seçili kategori yok!', 'success', 2000)
    return
  }
  loadingVisible.value = true

  const updateData = selectedRows.value.map((row: any) => {
    return {
      itemID: row.item_id,
      alan: 'categories' + alan.value + '_id',
      deger: kategori.value,
    }
  })

  const userID = userData.value.id

  try {
    const response = await axios.post('/api/kategori-guncelle', { updateData, userID })
    selectedRows.value = []
    clearSelection()
    notify('Kayıt başarıyla güncellendi...', 'success', 2000)
    popupKategoriVisible.value = false
    getData()
  } catch (error) {
    console.error('Veri çekilirken hata oluştu: ', error)
  } finally {
    loadingVisible.value = false
  }
}

const clearSelection = () => {
  const dataGrid = dataGridRef.value!.instance!

  dataGrid.clearSelection()
}

onMounted(async () => {
  pageTitleStore.setTitle("Stoklar");
  getVeriler()
});

const onContentReady = (e: DxDataGridTypes.ContentReadyEvent) => {
  const gridInstance = dataGridRef.value?.instance;
  totalRecord.value = gridInstance?.getDataSource()?.totalCount();
};

const onCellPreparedD = (e: DxDataGridTypes.CellPreparedEvent) => {
  if (e.rowType === 'data' && (
    e.column.dataField === 'qty_prm' && e.value > 0)) {
    e.cellElement.style.fontWeight = 'bold'
    e.cellElement.style.color = 'green'
  }
  if (e.rowType === 'data' && (
    e.column.dataField === 'qty_prm' && e.value < 0)) {
    e.cellElement.style.fontWeight = 'bold'
    e.cellElement.style.color = 'red'
  }
}

const onRowPrepared = (e: DxDataGridTypes.RowPreparedEvent) => {
  if (e.rowType !== 'data')
    return

  if (e.data?.add_string08 === 'ÖLÜ STOK') {
    e.rowElement.style.backgroundColor = 'rgba(250,160,8,0.12)'
    e.rowElement.style.fontWeight = 'bold'
  }
}

const onFocusedRowChanged = async (e: DxDataGridTypes.FocusedRowChangedEvent) => {
  selectedRow.value = e.row!.data!;
  // try {
  //   const response = await axios.get('/api/urunsorgula', {
  //     params: {
  //       itemID: data.item_code,
  //     },
  //   })

  //   formDatam.value.urunID = response.data.urun.item_id
  //   formDatam.value.urunKodu = response.data.urun.item_code
  //   formDatam.value.urunAdi = response.data.urun.item_name
  //   formDatam.value.urunAdi2 = response.data.urun.item_name2
  //   formDatam.value.toplam = response.data.toplam
  //   formDatam.value.toplamSayim = response.data.toplamSayim
  //   gridDataDepo.value = response.data.stoklar || []
  //   gridDataSayim.value = response.data.detay || []
  //   preview.value = ''

  //   fetchPhotos()
  // } catch (error) {
  //   console.error('Veri çekilirken hata oluştu: ', error)
  //   throw error
  // }
};

const UrunSorgula = async () => {
  return;
  if (!selectedRow.value) {
    console.warn('Seçili satır yok!')
    return
  }
  showLoading('Ürün sorgulanıyor...');
  Sifirla()
  pageTitleStore.setToplam(' -> ' + selectedRow.value.item_code + ' -> ' + selectedRow.value.item_name + '')
  try {
    const response = await axios.get('/api/urunsorgula', {
      params: {
        itemID: selectedRow.value?.item_code,
      },
    })

    formDatam.value.urunID = response.data.urun.item_id
    formDatam.value.urunKodu = response.data.urun.item_code
    formDatam.value.urunAdi = response.data.urun.item_name
    formDatam.value.urunAdi2 = response.data.urun.item_name2
    formDatam.value.toplama = response.data.toplama
    formDatam.value.toplami = response.data.toplami
    formDatam.value.toplamSayim = response.data.toplamSayim
    gridDataDepo.value = response.data.stoklar || []
    gridDataSayim.value = response.data.detay || []
    preview.value = ''
    DetayAl()
    fetchPhotos()
  } catch (error) {
    console.error('Veri çekilirken hata oluştu: ', error)
    hideLoading();
    throw error
  } finally {
    // Detay sorgusu
    showLoading('Detaylar yükleniyor...');
  }
}

const tersDataSource = computed(() => [...hareketGrafik.value].reverse())

const DetayAl = async () => {
  if (!selectedRow.value || !selectedRow.value.item_id) {
    console.warn('DetayAl iptal: item_id yok');
    loadingVisible.value = false;
    return;
  }
  showLoading('Detaylar yükleniyor...');
  try {
    const response = await axios.get('/api/stok-detay-al', {
      params: {
        itemID: selectedRow.value?.item_id,
      },
    })
    gridDataSiparis.value = response.data.siparis || []
    gridDataSatinAlma.value = response.data.satinalma || []
    gridDataUretimSarf.value = response.data.sarflar || []
    gridDataPlanSarf.value = response.data.plan || []
    gridDataFisler.value = response.data.fisler || []
    gridDataTalepler.value = response.data.talepler || []
    hareketOzet.value = response.data.veri || []
    gridDataSatisFisler.value = response.data.acikSiparisler || []
    hareketGrafik.value = response.data.aylikHareket || []
    toplamAlim.value = response.data.toplamAlim || 0
    toplamSiparis.value = response.data.toplamSiparis || 0
    toplamSarf.value = response.data.toplamSarf || 0
    toplamPlan.value = response.data.toplamPlan || 0
    toplamFis.value = response.data.toplamFis || 0
    toplamSatisFis.value = response.data.toplamSatisFis || 0
    toplamTalep.value = response.data.toplamTalep || 0

  } catch (error) {
    console.error('Veri çekilirken hata oluştu: ', error)
    throw error
  } finally {
    hideLoading();
  }
}

const fetchPhotos = async () => {
  try {
    const response = await axios.get('/api/photos', {
      params: { itemID: Number(formDatam.value.urunID) }
    })
    photos.value = Array.isArray(response.data) ? response.data : []
  } catch (err) {
    console.error('Fotoğraflar alınamadı', err)
  }
}

const deletePhoto = async (id: number) => { // foto sil
  try {
    await axios.delete(`/api/photos/${id}`)
    photos.value = photos.value.filter(p => p.id !== id)
    notify('Fotoğraf silindi', 'success', 1500)
  } catch (err) {
    console.error('Fotoğraf silme hatası', err)
    notify('Fotoğraf silinemedi', 'error', 2000)
  }
}

const previewPhoto = (url: string) => {
  selectedPhoto.value = url
  previewDialog.value = true
}

const Sifirla = async () => {
  formDatam.value = {
    barkod: '',
    urunID: 0,
    urunKodu: '',
    urunAdi: '',
    urunAdi2: '',
    toplama: 0,
    toplami: 0,
    toplamSayim: 0,
  }
  gridDataDepo.value = []
  gridDataSayim.value = []
  gridDataSatinAlma.value = []
  gridDataSiparis.value = []
  gridDataUretimSarf.value = []
  gridDataPlanSarf.value = []
  toplamAlim.value = 0
  toplamSiparis.value = 0
  toplamSarf.value = 0
  toplamPlan.value = 0
}

const Yenile = async () => {
  pageTitleStore.setToplam('')
  gridData.value = []
  selectedRow.value = []
  selectedRows.value = []

  Sifirla()

  await getData();
};

const toggleExpandAll = () => {
  expandAll.value = !expandAll.value;
};


const menuItems = [
  { text: 'Yenile' },
  { text: 'Detay Göster' },
  { text: 'Kategori Güncelle' },
  { text: 'Ölü Stok' },
  { text: 'Ölü Stok İptal' },
]

function itemClick({ itemData }: DxContextMenuTypes.ItemClickEvent) {
  if (!itemData?.items) {
    switch (itemData?.text) {
      case 'Yenile':
        getData()
        break;
      case 'Detay Göster':
        UrunSorgula()
        break;
      case 'Kategori Güncelle':
        if (!Array.isArray(selectedRows.value) || selectedRows.value.length === 0) {
          notify('Lütfen en az bir satır seçin.', 'error', 2000)
          return;
        }
        // if (!selectedRows.value[0]?.item_code) return;
        kategori.value = ''
        alan.value = ''
        popupKategoriVisible.value = true
        break;
      case 'Ölü Stok':
        if (!Array.isArray(selectedRows.value) || selectedRows.value.length === 0) {
          notify('Lütfen en az bir satır seçin.', 'error', 2000)
          return;
        }
        if (!Array.isArray(selectedRows.value) || selectedRows.value.length === 0) return;
        OluStok('ÖLÜ STOK')
        break;
      case 'Ölü Stok İptal':
        if (!Array.isArray(selectedRows.value) || selectedRows.value.length === 0) {
          notify('Lütfen en az bir satır seçin.', 'error', 2000)
          return;
        }
        OluStok('')
        break;
      default:
        break;
    }
  }
}


const onExporting = (e: DxDataGridTypes.ExportingEvent) => {
  const workbook = new Workbook();
  const worksheet = workbook.addWorksheet("StokListesi");

  exportDataGrid({
    component: e.component,
    worksheet,
    autoFilterEnabled: true,
  }).then(() => {
    workbook.xlsx.writeBuffer().then((buffer: ArrayBuffer) => {
      saveAs(
        new Blob([buffer], { type: "application/octet-stream" }),
        "StokListesi.xlsx"
      );
    });
  });

  e.cancel = true;
};
</script>



<style>
#grid {
  display: flex;
  flex-direction: column;
  block-size: 45vh;
  margin-block: -20px 0;
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

.options {
  position: relative;
  background-color: rgba(191 191 191 / 15%);
  margin-block-start: 20px;
  padding-block: 20px;
  padding-inline: 20px;
}

.toplam {
  border: 1px solid rgb(250 174 119);
  border-radius: 5px;
  background-color: rgb(255 249 216);
  color: #000;
  font-weight: bold;
  padding-block: 0;
  padding-inline: 5px;
}

.caption {
  font-size: 18px;
  font-weight: 500;
}

.option-container {
  display: flex;
  justify-content: space-between;
  margin-block: 0;
  margin-inline: auto;
}

.option {
  display: flex;
  align-items: center;
  margin-block-start: 10px;
}

.option-caption {
  margin-block: 0;
  margin-inline: 8px;
  white-space: nowrap;
}

#chart {
  block-size: 440px;
}

.tooltip-header {
  border-block-end: 1px solid #c5c5c5;
  font-size: 16px;
  font-weight: 500;
  margin-block-end: 5px;
  padding-block-end: 5px;
}

.tooltip-body {
  inline-size: 170px;
}

.tooltip-body .series-name {
  display: inline-block;
  font-weight: normal;
  inline-size: 126px;
  line-height: 1.5;
  opacity: 0.6;
  padding-inline-end: 10px;
}

.tooltip-body .value-text {
  display: inline-block;
  inline-size: 30px;
  line-height: 1.5;
}
</style>

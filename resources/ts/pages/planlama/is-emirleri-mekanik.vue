<template>
  <VCard class="mt-0 pa-0 pt-2">
    <VCardText class="mt-0 pa-0 ms-2 me-1">
      <VCol cols="12" class="mt-0 pa-1 pe-2">
        <div id="liste" style="margin-block-end: -10px;">
          <DxContextMenu :data-source="menuItems" :width="200" target="#grid" @item-click="itemClick" />

          <DxDataGrid id="grid" ref="dataGridRef" :key="gridKey" :data-source="gridData" key-expr="satir_id"
            :show-borders="true" :focused-row-enabled="true" :row-alternation-enabled="false" :min-width="200"
            @exporting="onExporting" :allow-column-reordering="true" :column-auto-width="false"
            @content-ready="onContentReady" @focused-row-changed="onFocusedRowChanged" :allow-column-resizing="true"
            column-resizing-mode="widget" @cell-prepared="onCellPrepared" @selection-changed="onSelectionChanged"
            :auto-navigate-to-focused-row="true" @row-prepared="onRowPrepared" :on-option-changed="handleOptionChanged"
            @contextMenuPreparing="onContextMenuPreparing" v-model:focused-row-key="focusedRowKey"
            :selected-rows-keys="selectedRows">

            <!-- <DxColumn type="selection" :fixed="true" fixedPosition="left" /> -->
            <DxColumn data-field="id" caption="ID" :visible="false" :min-width="90" />
            <DxColumn data-field="aktif" caption="AKTIF" :visible="true" :min-width="40"
              cell-template="aktifTemplate" />
            <DxColumn data-field="hafta" caption="HAFTA" :fixed="true" :width="120" :visible="true" alignment="left"
              :cell-template="weekCellTemplate" />
            <DxColumn data-field="haftax" caption="HFT" data-type="string" :visible="false" :width="20" />
            <DxColumn data-field="aksesuar" caption="AKSESUAR" :visible="true" :width="60"
              cell-template="aksesuarTemplate" alignment="center" :allow-sorting="false" />
            <DxColumn data-field="grup_id" caption="GRUP ID" data-type="string" :visible="false" :width="40" />
            <DxColumn data-field="IS_ISTASYONU" caption="İST. ADI" :visible="true" :width="130" />
            <DxColumn data-field="IS_ISTASYONU_ID" caption="İSTASYON ID" :visible="false" :width="90" />
            <DxColumn data-field="IS_MERKEZI_ID" caption="İŞ MERKEZİ ID" :visible="false" :width="90" />
            <DxColumn data-field="IS_MERKEZI_ADI" caption="İŞ MERKEZİ ADI" :visible="false" :width="90" />
            <DxColumn data-field="IS_ISTASYONU_KODU" caption="İSTASYON KODU" :visible="false" :width="150" />
            <DxColumn data-field="IS_ISTASYONU_ADI" caption="İSTASYON ADIx" :visible="false" :width="150" />
            <DxColumn data-field="OPERASYON_ID" caption="OPRSYN ID" :visible="false" :width="120" />
            <DxColumn data-field="OPERASYON" caption="OPRSYN" :visible="true" :width="120" />
            <DxColumn data-field="Operasyon_no" caption="OPRSYN NO" :visible="false" :width="120" />
            <DxColumn data-field="siparis_belge_no" caption="SİPARİŞ NO" :width="90" :visible="true"
              :allow-sorting="false" />
            <DxColumn data-field="siparis_miktari" caption="SİPARİŞ MİKTARI" data-type="number" :width="90"
              :visible="true" :allow-sorting="false" />
            <DxColumn data-field="kalan_siparis_miktari" caption="KALAN SİPARİŞ MİKTARI" data-type="number" :width="90"
              :visible="true" :allow-sorting="false" />
            <DxColumn data-field="cari_ad" caption="MÜŞTERİ" :visible="true" :min-width="140" :allow-sorting="false" />
            <DxColumn data-field="renk_id" caption="RENK ID" :visible="false" :min-width="90" :allow-sorting="false" />
            <DxColumn data-field="renk_kodu" caption="RENK KODU" :visible="true" :min-width="110" :allow-sorting="false"
              :cell-template="renkleriGoster" />
            <DxColumn data-field="renk_adi" caption="RENK ADI" :visible="false" :min-width="90"
              :allow-sorting="false" />
            <DxColumn data-field="item_id" caption="ITEM ID" :visible="false" :min-width="90" :allow-sorting="false" />
            <DxColumn data-field="stok_kodu" caption="STOK KODU" :visible="true" :width="120" :allow-sorting="false" />
            <DxColumn data-field="stok_adi" caption="STOK ADI" :min-width="200" :allow-sorting="false" />
            <DxColumn data-field="isemri_id" caption="İŞ EMRİ ID" :width="100" :visible="false"
              :allow-sorting="false" />
            <DxColumn data-field="isemri_no" caption="İŞ EMRİ NO" :width="120" :allow-sorting="false" />
            <DxColumn data-field="teslim_tarihi" caption="TESLİM TARİHİ" data-type="date" :width="140" :visible="true"
              :format="{
                formatter: (date: Date | string): string => {
                  const formattedDate: string = new Intl.DateTimeFormat('tr-TR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                  }).format(new Date(date));
                  return formattedDate.replace(/\//g, '.');
                },
              }" :cell-template="getIconType" :allow-sorting="false" />
            <DxColumn data-field="planlanan_baslangic" caption="PLN BŞL" data-type="date" :width="130" :visible="true"
              :format="{
                formatter: (date: Date | string): string => {
                  const formattedDate: string = new Intl.DateTimeFormat('tr-TR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                  }).format(new Date(date as string | number | Date));

                  return formattedDate.replace(/\//g, '.');
                },
              }" />
            <DxColumn data-field="planlanan_bitis_tarihi" caption="PLN BTŞ" data-type="date" :width="110"
              :visible="true" :format="{
                formatter: (date: Date | string): string => {
                  const formattedDate: string = new Intl.DateTimeFormat('tr-TR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                  }).format(new Date(date as string | number | Date));

                  return formattedDate.replace(/\//g, '.');
                },
              }" alignment="left" />
            <DxColumn data-field="kalan_miktar" caption="KALAN MİKTAR" data-type="number" :width="80" :visible="true"
              :allow-sorting="false" />

            <DxColumn data-field="surec" caption="SÜREÇ" data-type="number" :width="150" :visible="true"
              cell-template="surecCellTemplate" alignment="center" :allow-sorting="false" />

            <DxColumn data-field="isemri_miktari" caption="İŞ EMRİ MİKTARI" data-type="number" :width="110"
              :visible="true" />
            <DxColumn data-field="uretilen_toplam_miktar" caption="TOPLAM URETİLEN" data-type="number" :width="60"
              :visible="false" :allow-sorting="false" />
            <DxColumn data-field="uretilen_net_miktar" caption="NET URETILEN" data-type="number" :width="60"
              :visible="true" />
            <DxColumn data-field="toplam_hurda_miktari" caption="HURDA MİKTARI" data-type="number" :width="60"
              :visible="true" :allow-sorting="false" />
            <DxColumn data-field="operasyon_hazirlik_suresi" caption="HAZIRLIK SÜRESİ" data-type="number" :width="110"
              :visible="true" :format="{
                type: 'fixedPoint',
                precision: 1,
                thousandsSeparator: ',',
              }" />
            <DxColumn data-field="operasyon_suresi" caption="OPERASYON SÜRESİ" data-type="number" :width="110"
              :visible="true" :format="{
                type: 'fixedPoint',
                precision: 1,
                thousandsSeparator: ',',
              }" :allow-sorting="false" />
            <DxColumn data-field="sip_detay_id" :min-width="120" :width="140" :allow-sorting="false" />
            <DxColumn data-field="eksikler" caption="EKSİKLER" :visible="true" :width="60"
              cell-template="eksiklerTemplate" alignment="center" :allow-sorting="false" />

            <DxColumn data-field="isemri_tipi" caption="İŞ EMRİ TİPİ" :min-width="120" :width="140"
              :allow-sorting="false" />

            <DxColumn data-field="teknik_not1" caption="PLN NOTU" :width="60" :visible="false" :allow-sorting="false" />
            <DxColumn data-field="teknik_not2" caption="OPR NOTU" :width="90" :visible="false" alignment="center"
              :allow-sorting="false" />
            <DxColumn data-field="kaydi_giren_kullanici" caption="KAYIT YAPAN" :min-width="120" :width="140"
              :allow-sorting="false" />
            <DxColumn data-field="satir_id" caption="SATIR ID" :visible="false" :min-width="90"
              :allow-sorting="false" />


            <DxColumn data-field="sip_not1" caption="SİP NOT 1" :min-width="120" :allow-sorting="false" />
            <DxColumn data-field="sip_not2" caption="SİP NOT 2" :min-width="120" :allow-sorting="false" />
            <DxColumn data-field="sip_not3" caption="SİP NOT 3" :min-width="120" :allow-sorting="false" />
            <DxColumn data-field="sip_not4" caption="SİP NOT 4" :min-width="120" :allow-sorting="false" />
            <DxColumn data-field="CIKIS_DEPO" caption="ÇIKIŞ DEPO" :min-width="80" :allow-sorting="false" />
            <DxColumn data-field="kayit_tarihi" caption="OLUŞTURMA TARİHİ" data-type="date" :width="130" :visible="true"
              :format="{
                formatter: (date: Date | string): string => {
                  const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                  }).format(new Date(date as string | number | Date));

                  return formattedDate.replace(/\//g, '.');
                },
}" />
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

        </div>

        <!-- Global Load Panel (grid dışında) -->
        <DxLoadPanel v-model:visible="loadingVisible" :show-indicator="true" :show-pane="true" :shading="true"
          :message="loadingMessage || 'Yükleniyor...'" :position="position" shading-color="rgba(0,0,0,0.35)" />
      </VCol>
    </VCardText>
  </VCard>

  <DxPopup v-model:visible="popupTeslimTarihVisible" :width="500" :height="560" :hide-on-outside-click="true"
    :show-close-button="true" title="Teslim Tarihini Giriniz">
    <template #content>
      <VCol cols="12">
        <div class="popup-center">
          <DxCalendar ref="calendarRef" v-model:value="baslangicGunu" :show-week-numbers="true"
            :select-week-on-click="true" selection-mode="single" :show-today-button="true" />
        </div>
      </VCol>
      <VCol cols="12">
        <div class="popup-center">
          <h2 class="mr-4">Üretimi Etkileme</h2>
          <DxSwitch v-model:value="birlesik" />
          <h2 class="ml-4">Üretimi Etkile</h2>
        </div>
      </VCol>
      <VCol cols="12">
        <div class="popup-center">
          <h2>Geri çekilecek gün sayısı: </h2>
          <DxNumberBox :min="0" :show-spin-buttons="true" :read-only="!birlesik" v-model:value="cekilecekGun"
            :input-attr="{ 'aria-label': 'Gun' }" style="inline-size: 100px; margin-inline-start: 10px;" />
        </div>
      </VCol>
    </template>
    <DxToolbarItem widget="dxButton" toolbar="bottom" location="center" :options="kaydetOptions"
      @click="TeslimTarihKaydet()" />
    <DxToolbarItem widget="dxButton" toolbar="bottom" location="center" :options="vazgecOptions" />
  </DxPopup>

  <DxPopup v-model:visible="popupTarihVisible" :width="350" :height="470" :hide-on-outside-click="true"
    :show-close-button="true" title="Üretim Tarihini Giriniz">
    <template #content>
      <VCol cols="12">
        <DxCalendar ref="calendarRef" v-model:value="initialValue" :show-week-numbers="true"
          :select-week-on-click="true" selection-mode="range" :show-today-button="true" />
      </VCol>
    </template>
    <DxToolbarItem widget="dxButton" toolbar="bottom" location="after" :options="kaydetOptions"
      @click="TarihKaydet()" />
    <DxToolbarItem widget="dxButton" toolbar="bottom" location="after" :options="vazgecOptions" />
  </DxPopup>

  <DxPopup v-model:visible="popupIstasyonVisible" :width="400" :height="330" :hide-on-outside-click="false"
    :show-close-button="true" title="İş Merkezi / İstasyonu seçiniz">
    <template #content>
      <VCol cols="12">
        <!-- 👉 İş Merkezi -->
        <DxSelectBox :data-source="merkezler" v-model:value="merkez" label="İş Merkezi" label-mode="floating"
          display-expr="mrk_adi" value-expr="is_merkezi_id" @value-changed="getIstasyonlar()"
          style="inline-size: 100%;" />
        <!-- <DxSelectBox v-model="isMerkeziKodu" :rules="[requiredValidator]" label="İş Merkezi" display-expr="ismerkezi"
                     value-expr="ismerkezi_kodu" :items="gridIsMerkezleri" @value-changed="onValueChanged"/> -->
      </VCol>
      <!-- 👉 İş İstasyonu -->
      <VCol cols="12">
        <DxSelectBox :data-source="istasyonlar" v-model:value="istasyon" label="İstasyon" label-mode="floating"
          display-expr="ist_adi" value-expr="istasyon_id" style="inline-size: 100%;" />
        <!-- <DxSelectBox v-model="isIstasyonuKodu" :rules="[requiredValidator]" label="İş İstasyonu" display-expr="istasyon"
                     value-expr="istasyon_kodu" :items="gridIsIstasyonlari"/> -->
      </VCol>
      <VCol cols="12">
        <div class="popup-center">
          <DxSwitch v-model:value="rotaGuncelle" />
          <div v-if="rotaGuncelle">
            <h2 class="ms-2"> Rota Güncellensin</h2>
          </div>
          <div v-else>
            <h2 class="ms-2"> Rota Güncellenmesin</h2>
          </div>
        </div>
      </VCol>
    </template>
    <DxToolbarItem widget="dxButton" toolbar="bottom" location="center" :options="kaydetOptions"
      @click="IstasyonKaydet()" />
    <DxToolbarItem widget="dxButton" toolbar="bottom" location="center" :options="vazgecOptions" />
  </DxPopup>

  <DxPopup v-model:visible="popupAksesuarVisible" :width="400" :height="220" :hide-on-outside-click="true"
    :show-close-button="true" title="Aksesuar olacak mı?">
    <template #content>
      <div class="popup-center">
        <h2 class="mr-4">Aksesuarsız</h2>
        <DxSwitch v-model:value="aksesuar" />
        <h2 class="ml-4">Aksesuarlı</h2>
      </div>
    </template>
    <DxToolbarItem widget="dxButton" toolbar="bottom" location="after" :options="kaydetOptions"
      @click="AksesuarKaydet()" />
    <DxToolbarItem widget="dxButton" toolbar="bottom" location="after" :options="vazgecOptions" />
  </DxPopup>

  <DxPopup v-model:visible="popupMesajGosterVisible" :width="400" :height="220" :hide-on-outside-click="true"
    :show-close-button="true" :title="notBaslik">
    <template #content>
      <DxScrollView width="100%" height="100%">
        <div class="caption">{{ selectedRow.isemri_no }}</div>
        {{ planlamaNotu }}
      </DxScrollView>
    </template>
  </DxPopup>

  <DxPopup v-model:visible="popupDepolarGosterVisible" :hide-on-outside-click="true" title='Diğer Depo Bakiyeleri'
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

          <DxColumn data-field="whouse_id" caption="DEPO ID" :width="80" />
          <DxColumn data-field="whouse_code" caption="DEPO KODU" :width="100" />
          <DxColumn data-field="description" caption="DEPO ADI" />
          <DxColumn data-field="qty_prm" caption="STOK" :width="80" data-type="number" :format="{
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

  <DxPopup v-model:visible="popupDetayGosterVisible" :width="1320" :height="800" :hide-on-outside-click="true"
    :show-close-button="true" :title=notBaslik>
    <template #content>
      <VCard>
        <VCardText class="mb-0 pb-0">
          <!-- 👉 Stepper -->
          <AppStepper v-model:current-step="currentStep" :items="numberedSteps" class="stepper-icon-step-bg" />
        </VCardText>

        <VDivider />

        <VCardText>
          <!-- 👉 stepper content -->
          <VForm>
            <VWindow v-model="currentStep" class="disable-tab-transition" style="block-size: 170px;">
              <VWindowItem>
                <VRow>
                  <VCol cols="4" class="mt-0 pa-0">
                    <VRow>
                      <VCol cols="7">
                        <DxTextBox class="kalin" styling-mode="underlined" label-mode="outside"
                          :value="selectedRow.istasyon" label="İş İstasyonu" :read-only="true" :tab-index="-1" />
                      </VCol>
                      <VCol cols="5">
                        <DxTextBox class="kalin" styling-mode="underlined" label-mode="outside"
                          :value="selectedRow.siparis_belge_no" label="Sipariş Nosu" :read-only="true"
                          :tab-index="-1" />
                      </VCol>
                    </VRow>

                    <VRow class="mt-0">
                      <VCol cols="7">
                        <DxTextBox class="kalin" styling-mode="underlined" label-mode="outside"
                          :value="selectedRow.kaydi_giren_kullanici" label="Kayıt Yapan Personel" :read-only="true"
                          :tab-index="-1" />
                      </VCol>
                      <VCol cols="5">
                        <DxTextBox class="kalin" styling-mode="underlined" label-mode="outside"
                          :value="selectedRow.isemri_no" label="İş Emri No" :read-only="true" :tab-index="-1" />
                      </VCol>
                    </VRow>

                    <VRow class="mt-0">
                      <VCol cols="7">
                        <DxTextBox class="kalin" styling-mode="underlined" label-mode="outside"
                          :value="selectedRow.teslim_tarihi" label="Teslim Tarihi" :read-only="true" :tab-index="-1" />
                      </VCol>
                      <VCol cols="5">
                        <DxTextBox class="kalin" styling-mode="underlined" label-mode="outside"
                          :value="formatNumber(selectedRow.siparis_miktari)" label="Sipariş Miktarı" :read-only="true"
                          :tab-index="-1" />
                      </VCol>
                    </VRow>

                    <VRow class="mt-0">
                      <VCol cols="6">
                        <DxTextBox class="kalin" styling-mode="underlined" label-mode="outside"
                          :value="selectedRow.sip_not1" label="Sipariş Notu 1" :read-only="true" :tab-index="-1" />
                      </VCol>
                      <VCol cols="6">
                        <DxTextBox class="kalin" styling-mode="underlined" label-mode="outside"
                          :value="selectedRow.sip_not2" label="Sipariş Notu 2" :read-only="true" :tab-index="-1" />
                      </VCol>
                    </VRow>

                    <VRow class="mt-0">
                      <VCol cols="6">
                        <DxTextBox class="kalin" styling-mode="underlined" label-mode="outside"
                          :value="selectedRow.sip_not3" label="Sipariş Notu 3" :read-only="true" :tab-index="-1" />
                      </VCol>
                      <VCol cols="6">
                        <DxTextBox class="kalin" styling-mode="underlined" label-mode="outside"
                          :value="selectedRow.sip_not4" label="Sipariş Notu 4" :read-only="true" :tab-index="-1" />
                      </VCol>
                    </VRow>

                    <VRow>
                      <VCol cols="7" class="pa-0 ps-2 pe-3">
                        <DxSelectBox v-model="selectedNot" :items="notlar" display-expr="not" value-expr="not"
                          :disabled="!canManagePlanlama" label="Planlama Notu Eklemeleri" class="kalin" />
                      </VCol>
                      <VCol cols="5" class="mt-4 pa-0 ps-0  pe-0">
                        <VRow>
                          <VCol cols="2" class="pe-0 ps-1">
                            <VBtn text="" icon="tabler-arrow-down" rounded color="warning" block
                              :disabled="!canManagePlanlama" v-model="selectedRow.teknik_not1" @click="addToTextArea" />
                          </VCol>
                          <VCol cols="7" class="pa-0 ps-1 pt-3 pe-1">
                            <VBtn color="error" block @click="NotKaydet" :disabled="!canManagePlanlama">
                              <VIcon start icon="tabler-device-floppy" />
                              Kaydet
                            </VBtn>
                          </VCol>
                          <VCol cols="3" class="pe-6 ps-0 ">
                            <VBtn text="" icon="tabler-copy" rounded color="success" block
                              v-model="selectedRow.teknik_not1" @click="copyToClipboard" id="kopyala" />
                          </VCol>
                          <DxTooltip :hide-on-outside-click="false" target="#kopyala" show-event="mouseenter"
                            hide-event="mouseleave" position="right">
                            <b>Malzeme listesindeki 0'dan küçük satırları kopyalar</b><br> * satırları seçmenize gerek
                            yoktur
                          </DxTooltip>
                        </VRow>
                      </VCol>

                      <VCol cols="12" class="mt-0 pa-0 ps-2 pe-3  pt-3">
                        <DxTextArea v-model="textAreaValue" " label=" Planlamanın Notu"
                          style="font-weight: bold !important;" styling-mode="outlined" label-mode="static" :height="98"
                          :max-length="950" />
                      </VCol>
                      <VCol cols="12" class="mt-0 pa-0 ps-2 pe-3 pt-3">
                        <DxTextArea v-model="selectedRow.teknik_not2" label="Üretimin Notu"
                          style="font-weight: bold !important;" styling-mode="outlined" label-mode="static"
                          :height="98" />
                      </VCol>

                      <!-- Butonlar -->
                      <!-- <div class="actions pa-2">
                        <VBtn type="button" @click="copyToClipboard">Seçilmiş Eksikleri Kopyala</VBtn>
                      </div> -->
                    </VRow>
                  </Vcol>

                  <VCol cols="8" class="mt-0 pa-0 ps-2">

                    <!-- ********** Eksik Parçalar ***************************************************** -->
                    <VRow>
                      <VCol cols="12" class="mt-2 pa-0 ps-2 pe-3 text-center">
                        <h4>Malzeme Listesi ({{ gridDataMalzemeler.length }} parça) (Depo ID: {{ selectedRow.CIKIS_DEPO
                          }})</h4>
                        <div style="block-size: 613px;">
                          <DxDataGrid id="gridMalzemeler" ref="dataGridRefM" :data-source="gridDataMalzemeler"
                            key-expr="item_id" :show-borders="true" :min-width="400" :column-auto-width="false"
                            :allow-column-resizing="true" column-resizing-mode="widget" @cell-prepared="onCellPreparedM"
                            height="100%" :row-alternation-enabled="true" @row-click="onRowClick">

                            <DxColumn data-field="tipi" caption="TİPİ" data-type="string" :visible="true" :width="65"
                              :cell-template="tipCellTemplate" />
                            <DxColumn data-field="user_line_no" caption="LINE NO" :width="60" :visible="false" />
                            <DxColumn data-field="item_id" caption="ITEM ID" :width="180" :visible="false" />
                            <DxColumn data-field="stok_kodu" caption="STOK KODU" :min-width="120" />
                            <DxColumn data-field="stok_adi" caption="STOK ADI" :min-width="180" />
                            <DxColumn data-field="worder_m_id" caption="İŞ EMRİ ID" :width="150" :visible="false" />
                            <DxColumn data-field="qty_base_bom" caption="BOM" :width="80" data-type=number :format="{
                              type: 'fixedPoint',
                              precision: 2,
                              thousandsSeparator: ',',
                            }" />
                            <DxColumn data-field="qty_net" caption="İHTİYAÇ" :width="80" data-type="number" :format="{
                              type: 'fixedPoint',
                              precision: 2,
                              thousandsSeparator: ',',
                            }" />
                            <DxColumn data-field="qty_prm" caption="STOK" :width="80" data-type="number" :format="{
                              type: 'fixedPoint',
                              precision: 2,
                              thousandsSeparator: ',',
                            }" />
                            <DxColumn data-field="bakiye" caption="BAKİYE" :width="100" data-type="number"
                              sort-order="asc" :format="{
                                type: 'fixedPoint',
                                precision: 2,
                                thousandsSeparator: ',',
                              }" />
                            <DxColumn data-field="qty_min_inv" :visible="false" caption="MİN STOK" :width="80"
                              data-type="number" :format="{
                                type: 'fixedPoint',
                                precision: 2,
                                thousandsSeparator: ',',
                              }" />
                            <DxColumn data-field="qty_max_inv" :visible="false" caption="MAX STOK" :width="80"
                              data-type="number" :format="{
                                type: 'fixedPoint',
                                precision: 2,
                                thousandsSeparator: ',',
                              }" />

                            <DxGroupPanel :visible="false" />
                            <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always" />


                          </DxDataGrid>
                        </div>
                      </VCol>
                    </VRow>
                  </VCol>

                </VRow>


              </VWindowItem>


              <VWindowItem>
                <VRow>
                  <DxDataGrid id="gridDetay" ref="dataGridRefD" :data-source="gridDataDetay" key-expr="satir_id"
                    :show-borders="true" :min-width="200" :column-auto-width="false" :allow-column-resizing="true"
                    column-resizing-mode="widget" @cell-prepared="onCellPrepared" height="195">

                    <DxColumn data-field="IS_ISTASYONU" caption="İSTASYON ADI" :width="180" />
                    <DxColumn data-field="OPERASYON" caption="OPERASYON" :width="180" />
                    <DxColumn data-field="stok_adi" caption="STOK ADI" :min-width="200" />
                    <DxColumn data-field="isemri_id" caption="İŞ EMRİ ID" :width="150" :visible="false" />
                    <DxColumn data-field="planlanan_baslangic" caption="PLN BŞL" data-type="date" :width="100"
                      :visible="true" :format="{
                        formatter: (date: Date | string): string => {
                          const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                            year: 'numeric',
                            month: '2-digit',
                            day: '2-digit',
                          }).format(new Date(date as string | number | Date));

                          return formattedDate.replace(/\//g, '.');
                        },
                      }" />
                    <DxColumn data-field="planlanan_bitis_tarihi" caption="PLN BTŞ" data-type="date" :width="100"
                      :visible="true" :format="{
                        formatter: (date: Date | string): string => {
                          const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                            year: 'numeric',
                            month: '2-digit',
                            day: '2-digit',
                          }).format(new Date(date as string | number | Date));

                          return formattedDate.replace(/\//g, '.');
                        },
                      }" />
                    <DxColumn data-field="kalan_miktar" caption="KALAN MİKTAR" data-type="number" :width="60"
                      :visible="true" />
                    <DxColumn data-field="surec" caption="SÜREÇ" data-type="number" :width="150" :visible="true"
                      cell-template="surecCellTemplate" alignment="center" />
                    <DxColumn data-field="aksesuar" caption="AKSESUAR" :visible="props.operasyon" :width="60"
                      cell-template="aksesuarTemplate" alignment="center" />
                    <DxColumn data-field="operasyon_hazirlik_suresi" caption="HZRL DK" data-type="number"
                      :min-width="90" :width="90" />
                    <DxColumn data-field="operasyon_suresi" caption="OPRS DK" data-type="number" :min-width="90"
                      :width="90" />
                    <DxColumn data-field="isemri_tipi" caption="İŞ EMRİ TİPİ" :min-width="120" :width="140" />

                    <DxGroupPanel :visible="false" />
                    <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always" />

                    <template #surecCellTemplate="{ data: cellData }">
                      <SurecCell :cell-data="cellData" :max-deger="100" />
                    </template>

                    <template #aksesuarTemplate="{ data }">
                      <template v-if="data.value === 'Aksesuarlı'">
                        <i :class="['dx-icon', 'dx-icon-gift']"
                          :style="{ fontSize: '16px', color: staticPrimaryColor }"></i>
                      </template>
                    </template>

                  </DxDataGrid>
                </VRow>
              </VWindowItem>

              <VWindowItem>
                <VRow>

                </VRow>
              </VWindowItem>
            </VWindow>

          </VForm>
        </VCardText>
      </VCard>
    </template>
  </DxPopup>

</template>

<script setup lang="ts">
// *********** İzinler *****************************************************
// import type { Rule } from "./ability";

import { useAbility } from "@casl/vue";
import { computed, nextTick, onMounted, ref } from "vue";

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

definePage({
  meta: { action: ['read'], subject: ['planlama', 'mekanik'] }
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
const popupTarihVisible = ref(false)
const popupTeslimTarihVisible = ref(false)
const popupAksesuarVisible = ref(false)
const popupMesajGosterVisible = ref(false)
const popupDetayGosterVisible = ref(false)
const popupIstasyonVisible = ref(false)
const popupDepolarGosterVisible = ref(false)
const initialValue = ref<Date[]>([])
const disabledDates = ref<null | ((data: { view: string; date: Date }) => boolean)>(null)
const calendarRef = ref<any>(null)
const pageTitleStore = usePageTitleStore()
const pageName = 'Mekanik İş Emirleri'
const pageAlias = 'PLN-MEKANIK'

pageTitleStore.setTitle(`${pageName} (${pageAlias})`)
document.title = `OFT - ${pageName} | ${pageAlias}`

const positionOf = ref<any>(null)
const currentEmployee = ref<Record<string, unknown>>({})

const expandAll = ref(false)
const aksesuar = ref(false)
const birlesik = ref(false)
const rotaGuncelle = ref(true)
const notlar = ref<any[]>([])
interface GridDataItem {
  teknik_not1?: string
  isemri_no?: string
  [key: string]: any
}
const gridData = ref<GridDataItem[]>([])
const gridDataS = ref<any[]>([])
const gridDataDetay = ref<any[]>([])
const gridDataMalzemeler = ref<any[]>([])
const gridIsMerkezleri = ref<any[]>([])
const gridIsIstasyonlari = ref<any[]>([])
const gridBakiyeler = ref<any[]>([])
const dataGridRef = ref<DxDataGrid | null>(null)
const dataGridRefD = ref<DxDataGrid | null>(null)
const dataGridRefM = ref<DxDataGrid | null>(null)
const goster = ref(true)
const loadingVisible = ref<boolean>(false)
const loadingMessage = ref<string>("")

async function beginLoading(message: string = "Veriler yükleniyor…") {
  loadingMessage.value = message
  loadingVisible.value = true
  await nextTick()
  await new Promise(requestAnimationFrame)
}

function endLoading() {
  loadingVisible.value = false
  loadingMessage.value = ""
}
const position = { of: 'window' }
const totalGroupCount = ref(0)
const totalRecord = ref(0)
const activeGroupField = ref<string | null>(null)
const grupVisible = ref(false)

// const selectedRow = ref<any | null>(null);
const selectedRows = ref<any[]>([])
const baslangicGunu = ref(new Date())
const bitisGunu = ref(new Date())
const planlamaNotu = ref<string | null>(null)
const notBaslik = ref<string>('')
const tarihAlani = ref<string>('')
const UretimNotu = ref<string | null>(null)
const selectedNot = ref<string | null>(null)
const textAreaValue = ref<string>('')
const gridKey = ref(Date.now())
const focusedRowKey = ref(0)
const isMerkeziKodu = ref<number | number[]>(0)
const isIstasyonuKodu = ref<number | number[]>(0)
const cekilecekGun = ref(2)
const eksikStokKodu = ref<number | string>(0)
const eksikStokAdi = ref<string>('')
const istasyonlar = ref<any[]>([])
const istasyon = ref<number>(0)
const merkezler = ref<any[]>([])
const merkez = ref<number>(0)
const mekanikVerileri = ref<any[]>([])
const selectedRowKeys = ref<any[]>([])

function onValueChanged(e: any) {
  isIstasyonuKodu.value = []
  gridIsIstasyonlari.value = []
  getIstasyonlar()
}

function isWeekend(date: Date): boolean {
  const day = date.getDay()

  return day === 0 || day === 6
}

function disableWeekend({ value }: { value: boolean }) {
  disabledDates.value = value
    ? data => data.view === 'month' && isWeekend(data.date)
    : null
}

const onRangeSelect = (value: Date[]) => {
  initialValue.value = [value[0], value[1]]

  // console.log(initialValue);
  // if (value.length === 2) {
  //   console.log("Başlangıç tarihi:", value[0]);
  //   console.log("Bitiş tarihi:", value[1]);
  // } else {
  //   console.log("Henüz tam bir aralık seçilmedi.");
  // }
}

const copyToClipboard = () => {
  // Alan başlıklarını tanımlayın (Excel'de ilk satıra eklenir)
  const headers = ['ITEM ID', 'STOK KODU', 'BAKİYE'] // Alan başlıklarınızı burada tanımlayın.

  // Bakiye değeri 0'dan küçük olanları filtrele ve formatla
  const filteredData = gridDataMalzemeler.value.filter(item => {
    if (!item.bakiye)
      return false // Bakiye boşsa hata almamak için filtre dışına at

    return item.bakiye < 0
  })

  if (filteredData.length === 0) {
    const tempTextArea = document.createElement('textarea')

    tempTextArea.value = ' '
    tempTextArea.style.position = 'absolute'
    tempTextArea.style.left = '-9999px' // Görünmez hale getir
    document.body.appendChild(tempTextArea)
    tempTextArea.select()
    document.execCommand('copy')
    document.body.removeChild(tempTextArea)

    notify('Eksik bulunamadı.', 'warning', 1500)

    return
  }

  const clipboardData = filteredData.map(row => {
    const numericBakiye = Number.parseFloat(
      row.bakiye.toString(), // .replace(/\./g, ",")
    ) || 0

    return `${row.item_id}\t${row.stok_kodu}\t${row.stok_adi}\t${numericBakiye}`
  })

  // Başlıkları ve verileri birleştirin
  const clipboardString = [headers.join('\t'), ...clipboardData].join('\n')

  // Geçici bir <textarea> oluştur
  const tempTextArea = document.createElement('textarea')

  tempTextArea.value = clipboardString
  tempTextArea.style.position = 'absolute'
  tempTextArea.style.left = '-9999px' // Görünmez hale getir
  document.body.appendChild(tempTextArea)

  // İçeriği seç ve kopyala
  tempTextArea.select()
  try {
    document.execCommand('copy')
    notify('Eksikler kopyalandı.', 'success', 1500)
  }
  catch (err) {
    console.error('Kopyalama sırasında bir hata oluştu:', err)
  }

  // Temizle
  document.body.removeChild(tempTextArea)
}

const saveGridState = (): void => {
  let state: unknown = null
  if (dataGridRef.value?.instance) {
    state = dataGridRef.value.instance.state()
    const serialized = JSON.stringify(state)
    localStorage.setItem(props.tab, serialized)
  }
}

const loadGridState = (): void => {
  const savedState = localStorage.getItem(props.tab);
  if (savedState && dataGridRef.value && dataGridRef.value.instance)
    dataGridRef.value.instance.state(JSON.parse(savedState))
}

const onStateResetClick = (): void => {
  localStorage.removeItem(props.tab)
  if (dataGridRef.value?.instance)
    (dataGridRef.value.instance as any).state(null)
}

const onSelectionChanged = (e: any): void => {
  selectedRows.value = e.selectedRowsData // Seçilen satırların tüm verileri
}

const renkleriGoster = (cellElement: HTMLElement, cellInfo: any): void => {
  const renkID = cellInfo.data.renk_kodu

  cellElement.innerText = cellInfo.text

  const createIcon = (color: string) => {
    const icon = document.createElement('span')

    icon.className = 'dx-icon dx-icon-square'
    icon.style.marginLeft = '8px' // İkon ile metin arasına boşluk ekle
    icon.style.color = color // İkon rengi
    icon.style.fontSize = '20' // Ana span'ı etkisizleştir
    icon.style.display = 'inline-flex'

    return icon
  }

  if (renkID) {
    let renk = ''
    switch (renkID) {
      case 'RAL7035':
        renk = '#D7D7D7'
        break;
      case 'RAL7016':
        renk = '#293133'
        break;
      case 'RAL7031':
        renk = '#474B4E'
        break;
      case 'RAL7032':
        renk = '#721422'
        break;
      case 'RAL5026':
        renk = '#102C54'
        break;
      case 'RAL7037':
        renk = '#7D7F7D'
        break;
      case 'RAL9003':
        renk = '#F4F4F4'
        break;
      case 'RAL9005':
        renk = '#0A0A0A'
        break;
      case 'RAL9006':
        renk = '#A5A5A5'
        break;
      case 'RAL9007':
        renk = '#8F8F8F'
        break;
      case 'RAL9010':
        renk = '#FFFFFF'
        break;
      case 'RAL9016':
        renk = '#F6F6F6'
        break;
      default:
        renk = 'white'
        break;
    }

    // Kalite onayı bekleyenler mavi, Beklemede olanlar kademeli kırmızı, Müşteri iptali koyu gri işaretli olarak yapar mısınız.
    const renkIcon = createIcon(renk)

    cellElement.insertBefore(renkIcon, cellElement.firstChild)
  }
}

const weekCellTemplate = (cellElement: HTMLElement, cellInfo: any): void => {
  const hasNoteP = cellInfo.data.teknik_not1 && cellInfo.data.teknik_not1.trim() !== ''
  const hasNoteU = cellInfo.data.teknik_not2 && cellInfo.data.teknik_not2.trim() !== ''

  // Ana metni ekle (hafta değeri)
  cellElement.innerText = cellInfo.text

  const createIcon = (color: string, not: string) => {
    const icon = document.createElement('span')

    if (not === 'Planlamanın Notu')
      icon.className = 'dx-icon dx-icon-square'
    else
      icon.className = 'dx-icon dx-icon-message'
    icon.style.marginLeft = '8px' // İkon ile metin arasına boşluk ekle
    icon.style.color = color // İkon rengi
    icon.style.fontSize = '16' // Ana span'ı etkisizleştir
    icon.style.display = 'inline-flex'

    // Tıklama olayı ekle
    icon.addEventListener('click', () => {
      planlamaNotu.value = not === 'Planlamanın Notu' ? cellInfo.data.teknik_not1 : cellInfo.data.teknik_not2
      notBaslik.value = not
      popupMesajGosterVisible.value = !popupMesajGosterVisible.value
    })

    return icon
  }

  if (hasNoteP) {
    let renk = ''
    switch (cellInfo.data.teknik_not1) {
      case 'Mekanik eksik':
        renk = 'orange'
        break;
      case 'Satınalma eksik':
        renk = 'red'
        break;
      case 'Revizyon yapıldı. Müşteri / Arge':
        renk = 'gray'
        break;
      case 'Ürün ağacı yok':
        renk = 'gray'
        break;
      case 'Beklemede - Yönetim - Fiyat':
        renk = '#f79191'
        break;
      case 'İptal - Müşteri':
        renk = 'darkgray'
        break;
      default:
        renk = '#a29bef'
        break;
    }

    // Kalite onayı bekleyenler mavi, Beklemede olanlar kademeli kırmızı, Müşteri iptali koyu gri işaretli olarak yapar mısınız.
    const plnIcon = createIcon(renk, 'Planlamanın Notu')

    cellElement.appendChild(plnIcon)
  }

  if (hasNoteU) {
    const greenIcon = createIcon('green', 'Üretimin Notu')

    cellElement.appendChild(greenIcon)
  }
}

// Tarih formatını 'DD.MM.YYYY' -> 'YYYY-MM-DD' olarak dönüştüren fonksiyon
const convertToISODate = (dateString: string | null | undefined) => {
  if (!dateString || typeof dateString !== 'string')
    return null

  const [day, month, year] = dateString.split('.')

  return `${year}-${month}-${day}`
}

// Hücreye ikon tipi döndüren fonksiyon
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
  }
  else if (diffInDays === 0) {
    const blueIcon = createIcon('blue', true)
    if (cellElement.firstChild)
      cellElement.insertBefore(blueIcon, cellElement.firstChild)
    else
      cellElement.appendChild(blueIcon) // İlk çocuk yoksa sona ekle
  }
  else if (diffInDays <= 3) {
    const orangeIcon = createIcon('orange', true)
    if (cellElement.firstChild)
      cellElement.insertBefore(orangeIcon, cellElement.firstChild)
    else
      cellElement.appendChild(orangeIcon) // İlk çocuk yoksa sona ekle
  }
  else {
    const whiteIcon = createIcon('white', false)
    if (cellElement.firstChild)
      cellElement.insertBefore(whiteIcon, cellElement.firstChild)
    else
      cellElement.appendChild(whiteIcon) // İlk çocuk yoksa sona ekle
  }
}

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
  planlanan_bitis_tarihi: '',
  aksesuar: '',
  sip_detay_id: 0,
  istasyon: '',
  stok_kodu: '',
  stok_adi: '',
  CIKIS_DEPO: '', // Added property to fix the error
  siparis_miktari: 0, // Added to fix compile error
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
])

const currentStep = ref(0)

const addToTextArea = () => {
  if (selectedRow.value === null || selectedRow.value.isemri_no === '') {
    notify('Önce iş emrini seçmelisiniz...', 'error', 1500)

    return
  }
  if (selectedNot.value) {
    if (textAreaValue.value === '')
      textAreaValue.value = selectedNot.value
    else
      textAreaValue.value += (textAreaValue.value ? '\n' : '') + selectedNot.value
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
    popupTarihVisible.value = false
    popupTeslimTarihVisible.value = false
    popupAksesuarVisible.value = false
    popupDetayGosterVisible.value = false
    popupIstasyonVisible.value = false
  },
}

const IstasyonKaydet = async () => {
  const updateData = selectedRows.value.map((row: any) => {
    return {
      item_id: row.item_id,
      isemriID: row.isemri_id,
      operasyonID: row.OPERASYON_ID,
      istasyonID: row.IS_ISTASYONU_ID,
      isMerkeziID: row.IS_MERKEZI_ID,
      satir_id: row.satir_id,
      ismerkezi_kodu: merkez.value,
      istasyon_kodu: istasyon.value,
      rotaGuncelle: rotaGuncelle.value,
    }
  })

  // console.log('updateData:', updateData);

  const userID = userData.value.id
  // console.log('userID:', userID);
  try {
    const response = await axios.post('/api/istasyonKaydet', { updateData, userID })

    getData()
    selectedRows.value = []
    clearSelection()
    notify('Kayıt başarıyla güncellendi...', 'success', 1500)
  }
  catch (error) {
    console.error('Güncelleme sırasında hata oluştu:', error)
  }

  popupIstasyonVisible.value = false
}

const TarihKaydet = () => {
  if (tarihAlani.value === 'planlanan_baslangic')
    TarihAksesuarKaydet('t')
  popupTarihVisible.value = false
}

const TeslimTarihKaydet = () => {
  TeslimTarihiKaydet()
  popupTeslimTarihVisible.value = false
}

const AksesuarKaydet = async () => {
  popupAksesuarVisible.value = false;

  const updateData = selectedRows.value.map((row: any) => {
    return {
      isemriNo: row.isemri_no,
      aksesuar: aksesuar.value === true ? 1 : 0,
      orderDId: row.sip_detay_id,
    }
  })
  const userID = userData.value.id
  console.log('Aksesuar güncelleme verisi:', updateData);
  try {
    const response = await axios.post('/api/aksesuarKaydet', { updateData, userID })
    getData()
    selectedRows.value = []
    clearSelection()
    notify('Kayıt başarıyla güncellendi...', 'success', 1500)
  }
  catch (error) {
    console.error('Güncelleme sırasında hata oluştu:', error)
  }
}

const TarihAksesuarKaydet = async (tur: string) => {
  const dailyWorkMinutes = 540 // Günlük çalışma süresi (dakika)
  const workStartHour = 8 // Çalışma başlangıç saati (08:00)
  if (initialValue.value[1] === null) initialValue.value[1] = initialValue.value[0]
  const toplamSure = 0
  const currentTime = new Date(baslangicGunu.value) // Başlangıç zamanı
  const startTime = new Date(baslangicGunu.value) // Başlangıç zamanı

  const updateData = selectedRows.value.map((row: any) => {
    return {
      tur,
      satir_id: row.satir_id,
      isEmriID: row.isemri_id,
      isemriNo: row.isemri_no,
      sure: 0, // sure,
      // sip_detay_id: row.sip_detay_id,
      aksesuar: aksesuar.value === true ? 1 : 0,
      teslimTarih: `${initialValue.value[0].getFullYear()}-${(initialValue.value[0].getMonth() + 1)
        .toString()
        .padStart(2, '0')}-${initialValue.value[0].getDate().toString().padStart(2, '0')} ${initialValue.value[0]
          .getHours()
          .toString()
          .padStart(2, '0')}:${initialValue.value[0].getMinutes().toString().padStart(2, '0')}:${initialValue.value[0]
            .getSeconds()
            .toString()
            .padStart(2, '0')}`,
      planlanan_baslangic: `${initialValue.value[0].getFullYear()}-${(initialValue.value[0].getMonth() + 1)
        .toString()
        .padStart(2, '0')}-${initialValue.value[0].getDate().toString().padStart(2, '0')} ${initialValue.value[0]
          .getHours()
          .toString()
          .padStart(2, '0')}:${initialValue.value[0].getMinutes().toString().padStart(2, '0')}:${initialValue.value[0]
            .getSeconds()
            .toString()
            .padStart(2, '0')}`,
      planlanan_bitis: `${initialValue.value[1].getFullYear()}-${(initialValue.value[1].getMonth() + 1)
        .toString()
        .padStart(2, '0')}-${initialValue.value[1].getDate().toString().padStart(2, '0')} ${initialValue.value[1]
          .getHours()
          .toString()
          .padStart(2, '0')}:${initialValue.value[1].getMinutes().toString().padStart(2, '0')}:${initialValue.value[1]
            .getSeconds()
            .toString()
            .padStart(2, '0')}`,
    }
  })
  const userID = userData.value.id
  try {
    const response = await axios.post('/api/updatePlanBaslangic', { updateData, userID })

    getData()
    selectedRows.value = []
    clearSelection()
    notify('Kayıt başarıyla güncellendi...', 'success', 1500)
  }
  catch (error) {
    console.error('Güncelleme sırasında hata oluştu:', error)
  }
}

const TeslimTarihiKaydet = async () => {
  const updateData = selectedRows.value.map((row: any) => {
    return {
      tur: 's',
      birlesik: birlesik.value === true ? 1 : 0,
      gun: cekilecekGun.value,
      satir_id: row.satir_id,
      isEmriID: row.isemri_id,
      isemriNo: row.isemri_no,
      sure: 0, // sure,
      sip_detay_id: row.sip_detay_id,
      aksesuar: aksesuar.value === true ? 1 : 0,
      teslimTarih: `${baslangicGunu.value.getFullYear()}-${(baslangicGunu.value.getMonth() + 1)
        .toString()
        .padStart(2, '0')}-${baslangicGunu.value.getDate().toString().padStart(2, '0')} ${baslangicGunu.value
          .getHours()
          .toString()
          .padStart(2, '0')}:${baslangicGunu.value.getMinutes().toString().padStart(2, '0')}:${baslangicGunu.value
            .getSeconds()
            .toString()
            .padStart(2, '0')}`,
    }
  })

  const userID = userData.value.id
  try {
    const response = await axios.post('/api/updatePlanBaslangic', { updateData, userID })

    getData()
    selectedRows.value = []
    clearSelection()
    notify('Kayıt başarıyla güncellendi...', 'success', 1500)
  }
  catch (error) {
    console.error('Güncelleme sırasında hata oluştu:', error)
  }
}

const clearSelection = (): void => {
  const dataGrid = dataGridRef.value?.instance
  dataGrid?.clearSelection()
}

const NotKaydet = async () => {
  try {
    const selectedItemIndex = gridData.value.findIndex(
      item => item.isemri_no === selectedRow.value.isemri_no,
    )

    if (selectedItemIndex === -1) {
      notify('Güncellenecek kayıt bulunamadı.', 'success', 1500)

      return
    }

    const response = await axios.post('/api/planNotKaydet', {
      satir_id: selectedRow.value.isemri_no,
      not: textAreaValue.value,
    })

    if (response.status === 200) {
      // Hafızadaki veriyi güncelle
      const updatedData = [...gridData.value]

      updatedData[selectedItemIndex].teknik_not1 = textAreaValue.value
      gridData.value = updatedData

      // Grid'i zorla yeniden render et
      // gridKey.value = Date.now();
      notify('Notlar başarıyla güncellendi...', 'success', 1500)
    }
  }
  catch (error) {
    console.error('Kaydetme sırasında bir hata oluştu:', error)
  }
}

const onCellPrepared = (e: any): void => {
  if (
    e.rowType === 'data'
    && ((e.column.dataField === 'isemri_miktari' || e.column.dataField === 'uretilen_net_miktar' || e.column.dataField === 'kalan_miktar') && e.value > 0)
  )
    e.cellElement.style.fontWeight = 'bold'

  if (
    e.rowType === 'data'
    && (e.column.dataField === 'toplam_hurda_miktari' && e.value > 0)
  ) {
    e.cellElement.style.color = 'white'
    e.cellElement.style.fontWeight = 'bold'
    e.cellElement.style.backgroundColor = '#EC2407'
  }
  if (
    e.rowType === 'data'
    && (e.column.dataField === 'kalan_siparis_miktari' && e.value > 0)
  ) {
    e.cellElement.style.color = 'black'
    e.cellElement.style.fontWeight = 'bold'
    e.cellElement.style.backgroundColor = '#b7d0e8'
  }

  if (e.rowType === 'data' && e.column.dataField === 'hafta')
    e.cellElement.style.fontWeight = 'bold'
}

const onRowPrepared = (e: any): void => {
  if (e.rowType !== 'data')
    return // Sadece veri satırlarını işle

  const today = new Date()
  const yesterday = new Date()

  yesterday.setDate(today.getDate() - 1) // Dün

  const formatDate = (date: Date) => date.toISOString().split('T')[0] // YYYY-MM-DD formatına çevir
  const todayStr = formatDate(today)
  const yesterdayStr = formatDate(yesterday)

  const rawDate = e.data?.kayit_tarihi
  if (rawDate) {
    const createDate = formatDate(new Date(rawDate)) // Güvenli dönüşüm
    if (createDate === todayStr || createDate === yesterdayStr) {
      e.rowElement.style.backgroundColor = 'rgba(250,160,8,0.12)'
      e.rowElement.style.fontWeight = 'bold'
    }
  }
}

const onCellPreparedB = (e: any): void => {
  if (
    e.rowType === 'data'
    && e.column.dataField === 'qty_prm')
    e.cellElement.style.fontWeight = 'bold'
}

const onCellPreparedM = (e: any): void => {
  if (
    e.rowType === 'data'
    && e.column.dataField === 'bakiye' && e.value < 0) {
    e.cellElement.style.color = 'white'
    e.cellElement.style.fontWeight = 'bold'
    e.cellElement.style.backgroundColor = '#EC2407'
  }
  if (
    e.rowType === 'data'
    && (e.column.dataField === 'qty_net' || e.column.dataField === 'qty_prm'))
    e.cellElement.style.fontWeight = 'bold'
}

const onRowClick = (e: any): void => {
  if (e.rowType === 'data') {
    gridBakiyeler.value = []
    eksikStokKodu.value = e.data.stok_kodu
    eksikStokAdi.value = e.data.stok_adi
    getBakiyeler(e.data.item_id)
    popupDepolarGosterVisible.value = true
  }
}

const props = defineProps<{
  tab: string
  mesaj: string
  tablo: string
  istasyon: boolean
  operasyon: boolean
  grup: boolean
  isMerkezi: Array<string>
  indx: string
}>()

const FiltreTemizle = (): void => {
  dataGridRef.value?.instance?.clearFilter()
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

const onFocusedRowChanged = (e: any): void => {
  if (e.rowIndex === -1)
    return
  const data = e.row!.data!

  numberedSteps.value[0].subtitle = data.siparis_belge_no
  selectedRow.value = data
  focusedRowKey.value = data.satir_id
  selectedRow.value.siparis_belge_no = data.siparis_belge_no != null ? data.siparis_belge_no : ''
  selectedRow.value.istasyon = data.IS_ISTASYONU
  textAreaValue.value = data.teknik_not1 != null ? data.teknik_not1 : ''

  // console.log(selectedRow.value);
  if (props.grup)
    grupVisible.value = true
}

const onContextMenuPreparing = (e: any): void => {
  const data = e.row!.data!

  aksesuar.value = data.aksesuar !== null
}

const onContentReady = (e: any): void => {
  const gridInstance = dataGridRef.value?.instance
  if (!gridInstance)
    return

  totalRecord.value = gridInstance.getDataSource()?.totalCount() || 0

  const groupDescriptor = gridInstance.getDataSource()?.group() as any
  if (Array.isArray(groupDescriptor) && groupDescriptor.length > 0) {
    activeGroupField.value = groupDescriptor[0]?.selector || null
    totalGroupCount.value = activeGroupField.value ? getGroupCount(activeGroupField.value as string) : 0
  }
  else if (groupDescriptor?.selector) {
    activeGroupField.value = groupDescriptor.selector as string
    totalGroupCount.value = activeGroupField.value ? getGroupCount(activeGroupField.value) : 0
  }
  else {
    activeGroupField.value = null
    totalGroupCount.value = 0
  }
}

const handleOptionChanged = (e: any): void => {
  if (e.fullName === 'dataSource') {
    // e.component.option('loadPanel.enabled', false)
  }
}

const getGroupCount = (groupField: string) => query(gridData.value)
  .groupBy(groupField)
  .toArray().length

const toggleGoster = () => {
  goster.value = !goster.value
}

const TarihGoster = (alan: string): void => {
  if (selectedRows.value.length === 0) {
    notify('Önce iş emrini seçmelisiniz...', 'error', 1500)

    return
  }
  tarihAlani.value = alan
  baslangicGunu.value = new Date(alan === 'planlanan_baslangic' ? selectedRows.value[0].planlanan_baslangic : selectedRows.value[0].teslim_tarihi)
  bitisGunu.value = new Date(alan === 'planlanan_baslangic' ? selectedRows.value[0].planlanan_bitis_tarihi : selectedRows.value[0].teslim_tarihi)
  initialValue.value = [baslangicGunu.value, bitisGunu.value]
  popupTarihVisible.value = true
}

const TeslimTarihGoster = (alan: string): void => {
  if (selectedRows.value.length === 0) {
    notify('Önce iş emrini seçmelisiniz...', 'error', 1500)
    return
  }
  tarihAlani.value = alan
  birlesik.value = false;
  baslangicGunu.value = new Date(selectedRows.value[0].teslim_tarihi)
  popupTeslimTarihVisible.value = true
}

const IstasyonaGonder = (): void => {
  if (selectedRows.value.length === 0) {
    notify('Önce iş emrini seçmelisiniz...', 'error', 1500)

    return
  }
  merkez.value = 0
  istasyon.value = 0
  merkezler.value = []
  istasyonlar.value = []
  getMerkezler()
  rotaGuncelle.value = true
  popupIstasyonVisible.value = true
}

const DetayGoster = (): void => {
  if (selectedRow.value === null) {
    notify('Önce iş emrini seçmelisiniz...', 'error', 1500)

    return
  }
  gridDataMalzemeler.value = []
  notBaslik.value = `${selectedRow.value.isemri_no} nolu iş emri detayı` + ` ( ${selectedRow.value.stok_adi} )`
  getDetay()
  popupDetayGosterVisible.value = true
}

const AksesuarGoster = (): void => {
  if (selectedRows.value.length === 0) {
    notify('Önce iş emrini seçmelisiniz...', 'error', 1500)

    return
  }
  aksesuar.value = !!(selectedRows.value[0].aksesuar !== '' && selectedRow.value.aksesuar !== null)
  popupAksesuarVisible.value = true
}

const getData = async () => {
  try {
    await beginLoading('Veriler yükleniyor…')
    const response = await axios.get('/api/data', {
      params: {
        tablo: 'DETAY',
        isMerkezi: ['1100', '1150', '1200', '1500'],
      },
    })

    gridData.value = response.data.emirler
    gridDataS.value = response.data.siparisler
    notlar.value = response.data.notlar
  }
  catch (error) {
    console.error('Veri çekilirken hata oluştu: ', error)
  }
  finally {
    endLoading()
  }
}
const fetchMekanikVerileri = async () => {
  try {
    const response = await axios.get('/api/aktifleri-al', { params: { istasyon: userData.value.istasyon_id, planlama: 1, } })
    mekanikVerileri.value = response.data.data.map((veri: any, index: number) => ({
      id: index, // Eğer benzersiz bir ID varsa kullan
      isEmriNo: veri.IS_EMRI_NO,
      urunAdi: veri.URUN_ADI,
      isEmriId: Number(veri.IS_EMRI_ID),
      urunKodu: veri.URUN_KODU,

    }));
  } catch (error) {
    console.error('Veri çekme hatası:', error)
  }
}
const getDetay = async () => {
  try {
    const response = await axios.get('/api/isEmriDetay', {
      params: {
        tablo: 'DETAY',
        depo: selectedRow.value.CIKIS_DEPO || 0,
        isemri_id: selectedRow.value.isemri_id,
      },
    })

    gridDataDetay.value = response.data.data
    numberedSteps.value[1].subtitle = `${response.data.toplamIsEmri} ad / ${response.data.toplamSure} dk`
    gridDataMalzemeler.value = response.data.malzemeler
  }
  catch (error) {
    console.error('Veri çekilirken hata oluştu: ', error)
  }
}

const getMerkezler = async () => {
  await beginLoading('İş merkezleri yükleniyor…')
  try {
    const response = await axios.get('/api/merkezal')

    merkezler.value = response.data.merkezler
  }
  catch (error) {
    console.error('Veri çekilirken hata oluştu: ', error)
  }
  finally {
    endLoading()
  }
}

const getIstasyonlar = async () => {
  istasyon.value = 0
  await beginLoading('İstasyonlar yükleniyor…')
  try {
    const response = await axios.get('/api/istasyonal', {
      params: {
        ismerkezi: merkez.value,
      },
    })

    istasyonlar.value = response.data.istasyonlar
  }
  catch (error) {
    console.error('Veri çekilirken hata oluştu: ', error)
  }
  finally {
    endLoading()
  }
}

const getBakiyeler = async (itemID: number) => {
  try {
    const response = await axios.get('/api/digerdepobakiyeleri', {
      params: {
        item_id: itemID,
        depo: selectedRow.value.CIKIS_DEPO || 0,
      },
    })

    gridBakiyeler.value = response.data.bakiyeler
  }
  catch (error) {
    console.error('Veri çekilirken hata oluştu: ', error)
  }
}

const refreshGrid = (): void => {
  const gridInstance = dataGridRef.value?.instance // DataGrid bileşeninin referansı
  if (gridInstance)
    gridInstance.refresh()
}

onMounted(async () => {
  await getData()
  loadGridState()
  nextTick(() => {
    if (dataGridRef.value && dataGridRef.value.instance) {
      dataGridRef.value.instance.clearSelection()
    }
  })
  console.log('Kullanıcı bilgileri:', userData.value)
})

const Yenile = async (): Promise<void> => {
  await getData() // Veri çekme işlemi
}

const toggleExpandAll = (): void => {
  expandAll.value = !expandAll.value
}

const formatNumber = (num: number): string => {
  return new Intl.NumberFormat('tr-TR', { maximumFractionDigits: 0 }).format(num)
}

const userData = useCookie<any>('userData')

const onExporting = (e: DxDataGridTypes.ExportingEvent) => {
  const workbook = new Workbook()
  const worksheet = workbook.addWorksheet('KompleIsEmirleri')

  exportDataGrid({
    component: e.component,
    worksheet,
    autoFilterEnabled: true,
  }).then(() => {
    workbook.xlsx.writeBuffer().then(buffer => {
      saveAs(
        new Blob([buffer], { type: 'application/octet-stream' }),
        'KompleIsEmirleri.xlsx',
      )
    })
  })

  e.cancel = true
}

const ability = useAbility()
const canManagePlanlama = computed(() =>
  ability.can('manage', 'planlama') || ability.can('update', 'mekanik')
)

const baseMenuItems = [
  { text: 'Yenile' },
  { text: 'Haftaya Göre Grupla' },
  { text: 'Detay Göster' },
  { text: 'İstasyona Gönder', requiresManage: true },
  { text: 'Üretim Tarihini Değiştir', requiresManage: true },
  { text: 'Teslim Tarihini Değiştir', requiresManage: true },
  { text: 'Aksesuar', requiresManage: true },
  { text: 'Teknik Resim Göster' },
  { text: 'Düzen Yükle' },
  { text: 'Düzen Kaydet' },
  { text: 'Düzen Sıfırla' },
]

const menuItems = computed(() =>
  baseMenuItems.filter(item => canManagePlanlama.value || !('requiresManage' in item))
)

function itemClick({ itemData }: DxContextMenuTypes.ItemClickEvent) {
  if (!itemData?.items) {
    switch (itemData?.text) {
      case 'Yenile':
        getData()
        break;
      case 'Detay Göster':
        DetayGoster()
        break;
      case 'Haftaya Göre Grupla':
        groupByWeek();
        break;
      case 'İstasyona Gönder':
        IstasyonaGonder()
        break;
      case 'Üretim Tarihini Değiştir':
        notBaslik.value = 'Üretim tarihini seçiniz'
        TarihGoster('planlanan_baslangic')
        break;
      case 'Teslim Tarihini Değiştir':
        notBaslik.value = 'Teslim tarihini seçiniz'
        TeslimTarihGoster('teslim_tarihi')
        break;
      case 'Aksesuar':
        AksesuarGoster()
        break;
      case 'Düzen Yükle':
        loadGridState()
        break;
      case 'Teknik Resim Göster':
        ResimGoster()
        break;
      case 'Düzen Kaydet':
        saveGridState()
        break;
      case 'Düzen Sıfırla':
        onStateResetClick()
        break;
      case 'RAL Kodlarını Güncelle':
        RalKodlariGuncelle()
        break;
      default:
        break;
    }
  }
}

const ResimGoster = () => {
  const filename = `${selectedRow.value.stok_kodu}.pdf`
  const pdfUrl = `${import.meta.env.VITE_API_URL}/api/get-pdf/${filename}`

  window.open(pdfUrl, '_blank')
}

function groupByWeek(): void {
  if (dataGridRef.value?.instance) {
    dataGridRef.value.instance.clearGrouping(); // Önceki gruplamayı temizle
    dataGridRef.value.instance.columnOption('haftax', 'groupIndex', 0);
  }
}

const RalKodlariGuncelle = async () => {
  await beginLoading('RAL kodları güncelleniyor…')
  const userID = userData.value.id
  try {
    const response = await axios.post('/api/ralguncelle', { userID })

    // await getData()
    notify('Renk Kodları başarıyla güncellendi...', 'success', 2000)
  }
  catch (error) {
    console.error('Güncelleme sırasında hata oluştu:', error)
  }
  endLoading()
}


</script>

<style>
.v-progress-linear {
  inline-size: 100% !important;
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

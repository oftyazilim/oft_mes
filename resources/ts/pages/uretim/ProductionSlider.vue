<template>
  <!-- Sol detay kartı -->
  <div class="ust-section pa-0 ma-0">
    <VRow align="stretch">
      <VCol cols="12" md="8" class="d-flex pt-0">
        <!-- template starts above -->
        <div class="carousel-scroll pa-2 mb-2 mt-0" ref="carouselScroll" @mouseenter="pauseAutoScroll"
          @mouseleave="resumeAutoScroll">
          <div v-for="(item, index) in items" :key="index" class="carousel-card" @click="detaylariGoster(item)">
            <ProductionCard :isemriNo="item.isemriNo" :isemriId="item.isemriId" :runTime="item.runTime"
              :totalDown="item.totalDown" :totalTime="item.totalTime" :sonDurumSuresi="item.sonDurumSuresi"
              :target="item.target" :actual="item.actual" :efficiency="item.efficiency" :quality="item.quality"
              :partId="item.partId" :partCode="item.partCode" :partName="item.partName" :sebep="item.sebep"
              :baslikArkarenk="item.baslikArkarenk" :barData="item.barData" :barTotal="item.barTotal" :guid="item.guid"
              :isSelected="selectedIsemriNo === item.isemriNo" v-bind="item" @show-details="onShowDetails"
              :kontrolGerekli="item.is_use_quality" :kontrolcuCagrildi="item.is_check_quality_opr"
              :plnNote="item.plnNote" :prdNote="item.prdNote" :personelName="item.personelName"
              :personelId="item.personelId" :aksesuarli="item.aksesuarli" @ekipGuncellendi="onCardAction"
              @panelKapatildi="panelKapat(item)" :baslangicZamani="item.baslangicZamani" @durusGirildi="onDurusGirildi"
              :ekipSayisi="item.ekipSayisi" />
          </div>
        </div>
      </VCol>

      <VCol cols="12" md="2" class="d-flex ps-0  pt-0">
        <VCard class="gosterge pa-0 ma-0 flex-grow-1" height="318">
          <VCardTitle class="text-h5 mb-4" style="border-block-end: 2px solid #ccc;">
            <div class="d-flex justify-space-between align-center mt-0">
              Verimlilik (OEE)
            </div>
          </VCardTitle>
          <div class="ps-4 pe-2 text-center mb-1">
            <Grafik :barData="barVeri" />
          </div>
        </VCard>
      </VCol>

      <VCol cols="12" md="2" class="d-flex ps-0  pt-0">
        <VCard class="gosterge pa-0 ma-0 flex-grow-1" height="318">
          <VCardTitle class="text-h5 mb-4" style="border-block-end: 2px solid #ccc;">
            <div class="d-flex justify-space-between align-center mt-0">
              <div>
                {{ currentDate }}
              </div>
              <div>
                {{ currentTime }}
              </div>
            </div>
          </VCardTitle>
          <div class="px-2 py-0">
            <div class=" text-center pa-0 ma-0">
              <DxLinearGauge id="gauge" :value="uretimHizi">
                <DxScale :start-value="-100" :end-value="100" :tick-interval="100" :minor-tick-interval="0.1">
                  <DxMinorTick :visible="true" />
                  <DxLabel :use-range-colors="false">
                    <DxFont :color="gaugeLabelColor" />
                  </DxLabel>
                </DxScale>
                <DxExport :enabled="false" />
                <DxTitle :text="'Başarı Oranımız: %' + uretimHizi">
                  <DxFont :size="18" />
                </DxTitle>
              </DxLinearGauge>

            </div>
            <div class="d-flex flex-column justify-center mt-10" style="gap: 18px;">
              <div class="d-flex justify-space-between uretim">
                <strong>Anlık Üretim:</strong>
                <span class="miktar">{{ anlikUretim }}</span>
              </div>
              <div class="d-flex justify-space-between uretim">
                <strong>Anlık Hedef:</strong>
                <span class="miktar">{{ anlikHedef }}</span>
              </div>
              <div class="d-flex justify-space-between uretim">
                <strong>Günlük hedef:</strong>
                <span class="miktar">{{ gunlukHedef }}</span>
              </div>
            </div>
          </div>
        </VCard>
      </VCol>


      <!-- 
      <VCol cols="12" md="4" class="d-flex">
        <VCard class="gosterge pa-0 flex-grow-1" height="250">
          <VCardTitle class="text-h5 mb-4" style="border-block-end: 2px solid #ccc;">
            <VRow>
              <VCol cols="6" class="text-start">
                <div class="d-flex align-center">
                  <span>Haftalık Üretimlər</span>
                </div>
              </VCol>
              <VCol cols="6" class="text-end pa-1 pe-0">
                <VSelect v-model="seciliHafta" :items="[
                  { title: 'Bu Hafta', value: 'bu' },
                  { title: 'Geçen Hafta', value: 'gecen' },
                ]" class="mt-1" />
              </VCol>
            </VRow>
          </VCardTitle>
          <VCardText class="pa-2 pt-0 ma-0" height="100">
            <GunlukToplamGrafik :veri="grafikVerisi" />
          </VCardText>
        </VCard>
      </VCol> -->
    </VRow>
  </div>

  <!-- Detay ve sekmeli alan -->
  <div class="detail-section pa-0 ma-0">

    <VCard class="pa-0">
      <VTabs v-model="currentTab" fixed-tabs class="baslik">
        <VTab class="text-h5 baslik">İş Emirleri</VTab>
        <VTab class="text-h5 baslik">Malzeme Listesi</VTab>
        <VTab class="text-h5 baslik">Duruşlar</VTab>
        <VTab class="text-h5 baslik">Hurdalar</VTab>
      </VTabs>
      <VWindow v-model="currentTab">
        <VWindowItem value="tab-1">
          <VCardText class="pa-2">
            <DxContextMenu :data-source="menuItems" :width="200" target="#grid" @item-click="itemClick" />
            <DxDataGrid id="grid" class="grid" ref="dataGridRef" :key="gridKey" :data-source="gridData"
              key-expr="satir_id" :show-borders="true" :focused-row-enabled="true" :row-alternation-enabled="true"
              :min-width="200" @exporting="onExporting" :allow-column-reordering="true" :column-auto-width="false"
              @content-ready="onContentReady" @focused-row-changed="onFocusedRowChanged" :allow-column-resizing="true"
              column-resizing-mode="widget" @cell-prepared="onCellPrepared" @selection-changed="onSelectionChanged"
              :auto-navigate-to-focused-row="true" v-model:focused-row-key="focusedRowKey" height="670">
              <!-- <DxColumn type="selection" :fixed="true" fixedPosition="left" /> -->
              <DxColumn data-field="id" caption="ID" :visible="false" :min-width="90" />
              <DxColumn data-field="aktif" caption="AKTIF" :visible="true" :min-width="50"
                cell-template="aktifTemplate" />
              <DxColumn data-field="hafta" caption="HAFTA" :fixed="true" :width="130" :visible="true" alignment="left"
                :cell-template="weekCellTemplate" />
              <DxColumn data-field="aksesuar" caption="AKSESUAR" :visible="true" :width="60"
                cell-template="aksesuarTemplate" alignment="center" :allow-sorting="false" />
              <DxColumn data-field="eksikler" caption="EKSİKLER" :visible="false" :width="60" alignment="center"
                :allow-sorting="false" />
              <DxColumn data-field="grup_id" caption="GRUP ID" data-type="string" :visible="false" :width="40" />
              <DxColumn data-field="IS_ISTASYONU" caption="İST. ADI" :visible="false" :width="130" />
              <DxColumn data-field="IS_ISTASYONU_ID" caption="İSTASYON ID" data-type="number" :visible="false"
                :width="150" />
              <DxColumn data-field="IS_ISTASYONU_KODU" caption="İSTASYON KODU" :visible="false" :width="150" />
              <DxColumn data-field="IS_ISTASYONU_ADI" caption="İSTASYON ADIx" :visible="false" :width="150" />
              <DxColumn data-field="OPERASYON" caption="OPRSYN" :visible="true" :width="120" />
              <DxColumn data-field="siparis_belge_no" caption="SİPARİŞ NO" :width="90" :visible="true"
                :allow-sorting="false" />
              <DxColumn data-field="marka" caption="MARKA" :visible="true" :min-width="120" :allow-sorting="false" />
              <DxColumn data-field="cari_ad" caption="MÜŞTERİ" :visible="true" :min-width="140"
                :allow-sorting="false" />

              <DxColumn data-field="renk_kodu" caption="RENK KODU" :visible="true" :min-width="110"
                :allow-sorting="false" :cell-template="renkleriGoster" />
              <DxColumn data-field="stok_kodu" caption="STOK KODU" :visible="true" :width="120"
                :allow-sorting="false" />
              <DxColumn data-field="stok_adi" caption="STOK ADI" :min-width="200" :allow-sorting="false" />
              <DxColumn data-field="isemri_id" caption="İŞ EMRİ ID" :width="150" :visible="false"
                :allow-sorting="false" />
              <DxColumn data-field="isemri_no" caption="İŞ EMRİ NO" :width="120" :allow-sorting="false" />
              <DxColumn data-field="teslim_tarihi" caption="TESLİM TARİHİ" data-type="date" :width="140"
                :visible="false" :format="{
                  formatter: (date: any) => {
                    const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                      year: 'numeric',
                      month: '2-digit',
                      day: '2-digit',
                    }).format(new Date(date));
                    return formattedDate.replace(/\//g, '.');
                  },
                }" :allow-sorting="false" />
              <DxColumn data-field="planlanan_baslangic" caption="PLN BŞL" data-type="date" :width="130" :visible="true"
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
                }" />
              <DxColumn data-field="planlanan_bitis_tarihi" caption="PLN BTŞ" data-type="date" :width="110"
                :visible="true" :format="{
                  formatter: (date: any) => {
                    const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                      year: 'numeric',
                      month: '2-digit',
                      day: '2-digit',
                    }).format(new Date(date));

                    return formattedDate.replace(/\//g, '.');
                  },
                }" alignment="left" :cell-template="getIconType" />
              <DxColumn data-field="kalan_miktar" caption="KALAN MİKTAR" data-type="number" :width="60" :visible="true"
                :allow-sorting="false" />
              <!-- <DxColumn data-field="surec" caption="SÜREÇ" data-type="number" :width="150" :visible="true"
                  cell-template="surecCellTemplate" alignment="center" :allow-sorting="false" /> -->
              <DxColumn data-field="siparis_miktari" caption="SİPARİŞ MİKTARI" data-type="number" :width="60"
                :visible="true" :allow-sorting="false" />
              <DxColumn data-field="isemri_miktari" caption="İŞ EMRİ MİKTARI" data-type="number" :width="60"
                :visible="true" />
              <DxColumn data-field="uretilen_toplam_miktar" caption="TOPLAM URETİLEN" data-type="number" :width="60"
                :visible="false" :allow-sorting="false" />
              <DxColumn data-field="uretilen_net_miktar" caption="NET URETILEN" data-type="number" :width="60"
                :visible="true" />
              <DxColumn data-field="toplam_hurda_miktari" caption="HURDA MİKTARI" data-type="number" :width="60"
                :visible="true" :allow-sorting="false" />
              <DxColumn data-field="operasyon_hazirlik_suresi" caption="HAZIRLIK SÜRESİ" data-type="number" :width="60"
                :visible="true" :format="{
                  type: 'fixedPoint',
                  precision: 1,
                  thousandsSeparator: ',',
                }" />
              <DxColumn data-field="operasyon_suresi" caption="OPERASYON SÜRESİ" data-type="number" :width="60"
                :visible="true" :format="{
                  type: 'fixedPoint',
                  precision: 1,
                  thousandsSeparator: ',',
                }" :allow-sorting="false" />
              <!-- <DxColumn data-field="sip_detay_id" :min-width="120" :width="140" :allow-sorting="false" /> -->
              <DxColumn data-field="isemri_tipi" caption="İŞ EMRİ TİPİ" :min-width="120" :width="140"
                :allow-sorting="false" />
              <DxColumn data-field="teknik_not1" caption="PLN NOTU" :width="60" :allow-sorting="false" />
              <DxColumn data-field="teknik_not2" caption="OPR NOTU" :width="90" :visible="true" alignment="center"
                :allow-sorting="false" />
              <DxColumn data-field="kaydi_giren_kullanici" caption="KAYIT YAPAN" :min-width="120" :width="140"
                :allow-sorting="false" />
              <DxColumn data-field="item_id" caption="ITEM ID" :visible="false" :min-width="90"
                :allow-sorting="false" />
              <DxColumn data-field="satir_id" caption="SATIR ID" :visible="false" :min-width="90"
                :allow-sorting="false" />
              <DxColumn data-field="sip_not1" caption="SİP NOT 1" :min-width="120" :allow-sorting="false" />
              <DxColumn data-field="sip_not2" caption="SİP NOT 2" :min-width="120" :allow-sorting="false" />
              <DxColumn data-field="sip_not3" caption="SİP NOT 3" :min-width="120" :allow-sorting="false" />
              <DxColumn data-field="sip_not4" caption="SİP NOT 4" :min-width="120" :allow-sorting="false" />
              <DxColumn data-field="CIKIS_DEPO" caption="ÇIKIŞ DEPO" :min-width="80" :allow-sorting="false" />

              <DxLoadPanel key="grid-main-load" v-model:visible="loadingVisible" :show-indicator="true"
                :show-pane="true" :shading="true" />
              <DxHeaderFilter :visible="true" />
              <DxFilterPanel :visible="true" />
              <DxFilterRow :visible="goster" />
              <DxSearchPanel :visible="true" :width="240" />
              <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always" />
              <DxSorting mode="none" />
              <DxExport :enabled="true" :allow-export-selected-data="false" />

              <DxColumnChooser height="540px" :enabled="true" mode="select">
                <DxColumnChooserSearch :enabled="true" />
                <DxColumnChooserSelection :allow-select-all="true" :select-by-click="true" :recursive="true" />
              </DxColumnChooser>

              <DxToolbar>
                <DxItem location="before" template="totalRecordTemplate" />
                <DxItem location="before" template="eksikKontroluTemplate" @click="getEksikler" />
                <DxItem location="before" template="depoTemplate" />
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

              <template #depoTemplate>
                <span style="display: flex;">Koltukaltı&nbsp;&nbsp;
                  <DxSwitch v-model:value="depolar" text="Tüm Depolar" />&nbsp;&nbsp;Tüm Depolar
                </span>
              </template>

              <template #eksikKontroluTemplate>
                <DxButton icon="taskhelpneeded" styling-mode="text" hint="Eksikleri Kontrol Et" />
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
              <!-- 
                <template #surecCellTemplate="{ data: cellData }">
                  <SurecCell :cell-data="cellData" :max-deger="100" />
                </template> -->

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
              <template #aktifTemplate="{ data }">
                <template v-if="data.value === 1">
                  <i :class="['dx-icon', 'dx-icon-runner']"
                    :style="{ fontSize: '20px', color: staticPrimaryColor }"></i>
                </template>
              </template>
            </DxDataGrid>
          </VCardText>
        </VWindowItem>
        <VWindowItem value="tab-2">
          <VCardText class="pa-2">
            <div class="d-flex justify-space-between align-center mb-2">
              <div class="text-subtitle-2">
                Gereksiz beklemeleri engellemek için otomatik yükleme
                kaldırılmıştır...
              </div>
              <VBtn color="primary" variant="outlined" size="small" @click="getMalzemeListesi">
                Listeyi Yükle
              </VBtn>
            </div>
            <DxContextMenu :data-source="menuItemsM" :width="200" target="#gridMalzemeler" @item-click="itemClickM" />
            <DxDataGrid id="gridMalzemeler" ref="dataGridRefM" :data-source="gridDataEksikListesi" key-expr="item_id"
              :show-borders="true" :min-width="400" :column-auto-width="false" :allow-column-resizing="true"
              column-resizing-mode="widget" height="625" @cell-prepared="onCellPreparedM" :focused-row-enabled="true"
              :row-alternation-enabled="true" @contextMenuPreparing="onContextMenuPreparing">
              <DxColumn data-field="tipi" caption="TİPİ" data-type="string" :visible="true" :width="65"
                :cell-template="tipCellTemplate" />
              <DxColumn data-field="user_line_no" caption="LINE NO" :width="60" :visible="false" />
              <DxColumn data-field="item_id" caption="ITEM ID" :width="180" :visible="false" />
              <DxColumn data-field="stok_kodu" caption="STOK KODU" :width="140" />
              <DxColumn data-field="stok_adi" caption="STOK ADI" :min-width="180" />
              <DxColumn data-field="isemri_id" caption="İŞ EMRİ ID" :width="150" :visible="false" />
              <DxColumn data-field="qty_base_bom" caption="BOM" :width="120" data-type="number" :format="{
                type: 'fixedPoint',
                precision: 2,
                thousandsSeparator: ',',
              }" />
              <DxColumn data-field="qty_net" caption="İHTİYAÇ" :width="120" data-type="number" :format="{
                type: 'fixedPoint',
                precision: 2,
                thousandsSeparator: ',',
              }" />
              <DxColumn data-field="qty_prm" caption="STOK" :width="120" data-type="number" :format="{
                type: 'fixedPoint',
                precision: 2,
                thousandsSeparator: ',',
              }" />
              <DxColumn data-field="bakiye" caption="BAKİYE" :width="120" data-type="number" sort-order="asc" :format="{
                type: 'fixedPoint',
                precision: 2,
                thousandsSeparator: ',',
              }" />
              -->

              <DxGroupPanel :visible="false" />
              <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always" />
              <DxLoadPanel key="grid-malzeme-load" v-model:visible="loadingVisible" :show-indicator="true"
                :show-pane="true" :shading="true" />
            </DxDataGrid>
          </VCardText>
        </VWindowItem>
        <VWindowItem value="tab-3">
          <VCardText class="pa-2">
            <div class="d-flex justify-space-between align-center mb-2">
              <div class="text-subtitle-2">
                Gereksiz beklemeleri engellemek için otomatik yükleme
                kaldırılmıştır...
              </div>
              <VBtn color="primary" variant="outlined" size="small" @click="duruslariAl">
                Listeyi Yükle
              </VBtn>
            </div>
            <DxDataGrid id="gridDuruslar" ref="dataGridRefD" :data-source="gridDataDuruslar" key-expr="id"
              :show-borders="true" :focused-row-enabled="true" :row-alternation-enabled="true" :min-width="200"
              :allow-column-reordering="true" :column-auto-width="false" height="625">
              <DxColumn data-field="id" caption="ID" :visible="false" :min-width="90" />
              <DxColumn data-field="is_emri_no" caption="İŞ EMRİ NO" :visible="true" width="120" />
              <DxColumn data-field="durus_sebebi" caption="SEBEP" :visible="true" :min-width="250" />
              <DxColumn data-field="durum_bas_tarihi" caption="BAŞLANGIÇ" data-type="date" width="130" :visible="true"
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
                }" />
              <DxColumn data-field="durum_bit_tarihi" caption="BİTİŞ" data-type="date" width="130" :visible="true"
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
                }" />
              <DxColumn data-field="dakika" caption="SÜRE(dk)" data-type="number" :visible="true" :width="120" :format="{
                type: 'fixedPoint',
                precision: 1,
                thousandsSeparator: ',',
              }" />

              <DxLoadPanel key="grid-durus-load" v-model:visible="loadingVisible" :show-indicator="true"
                :show-pane="true" :shading="true" />
              <DxHeaderFilter :visible="true" />
              <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always" />
              <DxSorting mode="none" />

              <DxSummary>
                <DxTotalItem :align-by-column="true" column="durus_sebebi" summary-type="count"
                  display-format="{0} duruş" :alignment="right" />
                <DxTotalItem :align-by-column="true" column="dakika" summary-type="sum" display-format="{0} dk"
                  :alignment="right" :customize-text="formatSummaryText" />
              </DxSummary>
            </DxDataGrid>
          </VCardText>
        </VWindowItem>
        <VWindowItem value="tab-4">
          <VCardText class="pa-2">
            <VDataTable :headers="tableHeaders" :items="tabloVerisi" item-value="tarih" />
            <br />
            <!-- <VContainer fluid>
              <VRow>
                <VCol cols="12" md="8">
                  <VCard class="pa-4" elevation="2">
                    <HaftalikCubukGrafik />
                  </VCard>
                </VCol>
              </VRow>
            </VContainer> -->
            <div>
              <VSelect v-model="seciliHafta" :items="[
                { title: 'Bu Hafta', value: 'bu' },
                { title: 'Geçen Hafta', value: 'gecen' },
              ]" label="Hafta Seçin" class="mb-4" />

              <GunlukToplamGrafik :veri="grafikVerisi" />
            </div>

            <!-- <DxDataGrid
              id="gridHurdalar"
              ref="dataGridRefH"
              :key="gridKeyH"
              :data-source="gridDataHurdalar"
              key-expr="ID"
              :show-borders="true"
              :focused-row-enabled="true"
              :row-alternation-enabled="true"
              :min-width="200"
              :allow-column-reordering="true"
              :column-auto-width="false"
              height="670"
            >
              <DxColumn
                data-field="ID"
                caption="ID"
                :visible="false"
                :min-width="90"
              />
              <DxColumn
                data-field="HURDA_SEBEBI"
                caption="SEBEP"
                :visible="true"
                :min-width="190"
              />
              <DxColumn
                data-field="TARIH"
                caption="TARİH"
                data-type="date"
                :width="120"
                :visible="true"
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
              }"
              />
              <DxColumn
                data-field="MIKTAR"
                caption="MİKTAR"
                data-type="number"
                :visible="true"
                :min-width="60"
                :format="{
                  type: 'fixedPoint',
                  precision: 1,
                  thousandsSeparator: ',',
                }"
              />

              <DxLoadPanel
                :key="loadingVisible"
                v-model:visible="loadingVisible"
                :show-indicator="true"
                :show-pane="true"
                :shading="true"
              />
              <DxHeaderFilter :visible="true" />
              <DxScrolling
                mode="virtual"
                row-rendering-mode="virtual"
                show-scrollbar="always"
              />
              <DxSorting mode="none" />

              <DxSummary>
                <DxTotalItem
                  :align-by-column="true"
                  column="HURDA_SEBEBI"
                  summary-type="count"
                  display-format="{0} hurda"
                  :alignment="right"
                />
                <DxTotalItem
                  :align-by-column="true"
                  column="MIKTAR"
                  summary-type="sum"
                  display-format="{0} ad"
                  :alignment="right"
                />
              </DxSummary>
            </DxDataGrid> -->
          </VCardText>
        </VWindowItem>
      </VWindow>
    </VCard>
    <!-- </div> -->
  </div>

  <PersonelSecDialog v-model="ekipSecDialog" :isemriID="Number(selectedRow.isemri_id)"
    :istasyon-id="Number(selectedRow.IS_ISTASYONU_ID)" :guid="guid" @kaydedildi="onEkipKaydedildi"
    @iptal="onEkipIptal" />

  <DxPopup v-model:visible="popupNotGirVisible" :width="600" :height="300" :hide-on-outside-click="false"
    :show-close-button="true">
    <template #title>
      <p class="popup-title">Notunuzu Giriniz</p>
    </template>
    <template #content>
      <VCol cols="12" class="pa-0">
        <div class="popup-center mt-0 pa-0">
          <DxTextArea :height="155" :max-length="999" v-model="uretimNotu" :auto-resize-enabled="false"
            aria-required="true" />
        </div>
      </VCol>
    </template>
    <DxToolbarItem widget="dxButton" toolbar="bottom" location="center" :options="kaydetOptions" @click="NotKaydet()" />
    <DxToolbarItem widget="dxButton" toolbar="bottom" location="center" :options="vazgecOptions" />
  </DxPopup>

  <DxPopup v-model:visible="popupDepolarGosterVisible" :hide-on-outside-click="true" title="Diğer Depo Bakiyeleri"
    :show-close-button="false" :show-title="true" :width="800" :height="620">
    <template #title>
      <p class="popup-title">Diğer Depo Bakiyeleri</p>
    </template>
    <VCol class="text-center py-0 my-0">
      <h2 class="pa-0 ma-0">{{ eksikStokKodu }}</h2>
      <h2 class="pa-0 ma-0">{{ eksikStokAdi }}</h2>
    </VCol>
    <br />
    <VRow class="text-center py-0">
      <VCol class="text-center py-0">
        <DxDataGrid id="gridBakiyeler" ref="dataGridRefB" :data-source="gridBakiyeler" :show-borders="true"
          :column-auto-width="true" :allow-column-resizing="true" column-resizing-mode="widget"
          @cell-prepared="onCellPreparedB" :row-alternation-enabled="true" height="400" show-scrollbar="always">
          <DxColumn data-field="whouse_id" caption="DEPO ID" :width="80" alignment="left" />
          <DxColumn data-field="whouse_code" caption="DEPO KODU" :width="100" alignment="left" />
          <DxColumn data-field="description" caption="DEPO ADI" />
          <DxColumn data-field="qty_prm" caption="STOK" :width="100" data-type="number" :format="{
            type: 'fixedPoint',
            precision: 2,
            thousandsSeparator: ',',
          }" />

          <DxGroupPanel :visible="false" />
          <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always" />
          <DxLoadPanel key="grid-bakiye-load" v-model:visible="loadingVisible" :show-indicator="true" :show-pane="true"
            :shading="true" />
        </DxDataGrid>
      </VCol>
    </VRow>
    <DxToolbarItem widget="dxButton" toolbar="bottom" location="center" :options="kapatOptions"
      @click="popupDepolarGosterVisible = false" />
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
                        <DxTextArea v-model="textAreaValue" label="Planlamanın Notu"
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
                  </VCol>

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
                            height="100%" :row-alternation-enabled="true" @row-click="onRowClickDetay">

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
                    <DxColumn data-field="aksesuar" caption="AKSESUAR" :visible="true" :width="60"
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

  <DxPopup v-model:visible="popupMesajGosterVisible" :width="400" :height="220" :hide-on-outside-click="true"
    :show-close-button="true" :title="notBaslik">
    <template #content>
      <DxScrollView width="100%" height="100%">
        <div class="caption">{{ selectedRow.isemri_no }}</div>
        {{ planlamaNotu }}
      </DxScrollView>
    </template>
  </DxPopup>

  <VOverlay :model-value="loadingVisible" class="align-center justify-center" persistent scrim="rgba(0,0,0,0.35)">
    <VCard class="pa-6 d-flex flex-column align-center" elevation="8" width="320">
      <VProgressCircular indeterminate color="primary" size="48" width="5" class="mb-4" />
      <div class="text-subtitle-1 font-weight-medium">{{ loadingMessage }}</div>
    </VCard>
  </VOverlay>
</template>

<script setup lang="ts">
import { usePageTitleStore } from "@/stores/pageTitle";
import axios from "axios";
import dayjs from "dayjs";
import DxButton from "devextreme-vue/button";
import DxSwitch from "devextreme-vue/cjs/switch";
import DxContextMenu, { DxContextMenuTypes } from "devextreme-vue/context-menu";
import {
  DxColumn,
  DxColumnChooser,
  DxColumnChooserSearch,
  DxColumnChooserSelection,
  DxDataGrid,
  DxExport,
  DxFilterPanel,
  DxFilterRow,
  DxHeaderFilter,
  DxScrolling,
  DxSearchPanel,
  DxSorting,
  DxSummary,
  DxToolbar,
  DxTotalItem,
} from "devextreme-vue/data-grid";
import { DxLoadPanel } from "devextreme-vue/load-panel";
import { DxPopup, DxToolbarItem } from "devextreme-vue/popup";
import { DxItem } from "devextreme-vue/tabs";
import DxTextArea from "devextreme-vue/text-area";
import notify from "devextreme/ui/notify";
import { v4 as uuidv4 } from "uuid";
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { VSelect } from "vuetify/components";
import Grafik from "./Grafik.vue";
// @ts-ignore: SFC tipleri shims.d.ts üzerinden çözülüyor
import {
  DxFont, DxLabel,
  DxLinearGauge,
  DxMinorTick,
  DxScale,
  DxTitle,
} from 'devextreme-vue/linear-gauge';
// @ts-ignore: SFC tipi shims.d.ts ile sağlanır
import DxSelectBox from "devextreme-vue/select-box";
import DxTextBox from "devextreme-vue/text-box";
import DxTooltip from "devextreme-vue/tooltip";
// @ts-ignore: SFC tipi shims.d.ts ile çözümlenir
import GunlukToplamGrafik from "./GunlukToplamGrafik.vue";
import PersonelSecDialog from "./PersonelSecDialog.vue";
import ProductionCard from "./ProductionCard.vue";

const carouselScroll = ref<HTMLElement | null>(null);
let autoScrollInterval: ReturnType<typeof setInterval> | null = null;
interface SeciliType {
  isEmriId: number;
  isEmriNo: string;
  urunID: number;
  urunAdi: string;
  urunKodu: string;
  belgeNo: string;
  cariAdi: string;
  calSure: number;
  durSure: number;
  personelSayisi: number;
  sure: number;
  durum: string;
  sebep: any;
  calOran: number;
  durOran: number;
  istasyonID: number;
  personelID: number;
  planlamaNotu: string;
  UretimNotu: string;
  urunAgaciText: string;
  urunAgaciCheck: boolean;
  gorunumText: string;
  gorunumCheck: boolean;
  olcuselText: string;
  olcuselCheck: boolean;
  paketlemeText: string;
  paketlemeCheck: boolean;
  etiketText: string;
  etiketCheck: boolean;
  dokumanText: string;
  dokumanCheck: boolean;
  kontrolGerekli: boolean;
  kontrolcuCagir: boolean;
  guid: string;
  sahip: boolean;
}

const secili = ref<SeciliType>({
  isEmriId: 0,
  isEmriNo: "",
  urunID: 0,
  urunAdi: "",
  urunKodu: "",
  belgeNo: "",
  cariAdi: "",
  calSure: 0,
  durSure: 0,
  personelSayisi: 0,
  sure: 0,
  durum: "",
  sebep: null,
  calOran: 0,
  durOran: 0,
  istasyonID: 0,
  personelID: 0,
  planlamaNotu: "",
  UretimNotu: "",
  urunAgaciText: "",
  urunAgaciCheck: false,
  gorunumText: "",
  gorunumCheck: false,
  olcuselText: "",
  olcuselCheck: false,
  paketlemeText: "",
  paketlemeCheck: false,
  etiketText: "",
  etiketCheck: false,
  dokumanText: "",
  dokumanCheck: false,
  kontrolGerekli: false,
  kontrolcuCagir: false,
  guid: "", // Added guid property
  sahip: false, // Added property to fix the error
});
const selectedIsemriNo = ref<string | null>(null);
const items = ref<any[]>([]);
// Mola öncesi durumu geri yüklemek için referans alanı
// Otomatik mola kullanımı kaldırıldı
const userData = useCookie<any>("userData");
const pageTitleStore = usePageTitleStore();
const currentTab = ref("tab-1");
const loadingVisible = ref<boolean>(false);
const loadingMessage = ref<string>("Yükleniyor...");
let loadingStartedAt: number | null = null;
const MIN_LOADING_MS = 350;
function showLoading(msg: string = "Yükleniyor...") {
  loadingMessage.value = msg;
  if (!loadingVisible.value) {
    // loadingVisible.value = true;
    loadingStartedAt = Date.now();
  }
}
function hideLoading() {
  if (!loadingVisible.value) return;
  const elapsed = loadingStartedAt
    ? Date.now() - loadingStartedAt
    : MIN_LOADING_MS;
  const remaining = MIN_LOADING_MS - elapsed;
  const finalize = () => {
    loadingVisible.value = false;
    loadingStartedAt = null;
  };
  if (remaining > 0) {
    setTimeout(finalize, remaining);
  } else {
    finalize();
  }
}
const currentDate = ref("");
const currentTime = ref("");
const uretimHizi = ref(0);
const anlikUretim = ref(0);
const anlikHedef = ref(0);
const gunlukHedef = ref(0);
const cal = ref(0);
const dur = ref(0);
const kul = ref(0);
const urt = ref(0);
const kal = ref(0);
const oee = ref(0);
// const hftVeri = ref<number[]>([10, 20, 30, 20, 10, 50, 60]);
interface GridDataItem {
  isemri_no: string;
  [key: string]: any;
}
interface MontajVeri {
  personelID: number;
  isEmriNo: string;
  sure: number;
  durum: string;
  tplSureAktif?: string;
  durSureAktif?: string;
  calSureAktif?: string;
  plnSureAktif?: string;
  calSure: number;
  durSure: number;
  tplSure: number;
  plnSure: number;
  calOran: number;
  durOran: number;
  hdfSure: number;
  hdfOran: number;
  istasyonID: number;
  isEmriID: number;
  isEmriTipi: string;
  durumBasTarih: string;
}

const onShowDetails = (detail: any) => {
  if (detail && detail.isemriNo) {
    selectedIsemriNo.value = detail.isemriNo;
    // İleride detay paneli doldurmak istersen burada state ataması yapabilirsin.
    // console.log('Detay:', detail);
  }
};

// --- Yardımcı & grid event handlerları ---
const onExporting = (_e: any) => {
  /* export ayarları gerekirse */
};
const onSelectionChanged = (_e: any) => {
  /* seçim değişti */
};
// Filtre satırı görünürlüğü
const goster = ref<boolean>(false);
const FiltreTemizle = () => {
  const inst = dataGridRef.value?.instance;
  inst?.clearFilter();
};
const toggleGoster = () => {
  goster.value = !goster.value;
};
// Özet metin formatlayıcı
function formatSummaryText(e: any) {
  return (
    new Intl.NumberFormat("tr-TR", { maximumFractionDigits: 0 }).format(
      e.value
    ) + " dk"
  );
}
// function formatSummaryText(itemInfo: { value: string | number | Date; valueText: string }) {
//   if (itemInfo.value instanceof Date) return itemInfo.valueText;
//   return itemInfo.value?.toString();
// }
const right: "right" = "right";
// Hafta hücresi not popup state
const planlamaNotu = ref("");
const notBaslik = ref("");
const popupMesajGosterVisible = ref(false);
const popupDetayGosterVisible = ref(false)

// Placeholder ikon şablonu kullanılan kolon için
const getIconType = (cellElement: HTMLElement, cellInfo: any) => {
  cellElement.innerText = cellInfo.text;
};
const selectedRow = ref({
  hafta: "",
  istasyon: "",
  isemri_no: "",
  teslim_tarihi: "",
  siparis_belge_no: "",
  isemri_id: "",
  sip_not1: "",
  sip_not2: "",
  sip_not3: "",
  sip_not4: "",
  teknik_not1: "",
  teknik_not2: "",
  cari_ad: "",
  eksikler: "",
  kaydi_giren_kullanici: "",
  planlanan_baslangic: "",
  aksesuar: "",
  sip_detay_id: 0,
  siparis_miktari: 0,
  CIKIS_DEPO: 0,
  IS_ISTASYONU_ID: 0,
  item_id: 0,
  stok_kodu: "", // Added property
  stok_adi: "", // Added property
});
const gridData = ref<GridDataItem[]>([]);
const gridDataEksikListesi = ref([]);
const gridDataMalzemeler = ref<any[]>([])
const gridKey = ref(Date.now());
const gridBakiyeler = ref([]);

const dataGridRef = ref<DxDataGrid | null>(null);
const dataGridRefM = ref<DxDataGrid | null>(null);

const totalRecord = ref(0);
const malzemeId = ref(0);
const montajVerileri = ref<MontajVeri[]>([]);
const guid = ref("");
const ekipSecDialog = ref(false);
const focusedRowKey = ref(0);
const selectedSebep = ref<{
  break_reason_code: string;
  description: string;
} | null>(null);

const barVeri = ref<number[]>([]);
const aktifEkip = ref<any[]>([]);
const gridDataDuruslar = ref([]);
const depolar = ref(false);
// const MolaOncekiDurum = ref<string | null>(null);

const popupNotGirVisible = ref(false);
const popupDepolarGosterVisible = ref(false);

const uretimNotu = ref("");
const eksikStokId = ref(0);
const eksikStokKodu = ref("");
const eksikStokAdi = ref("");

// Tema rengi ve yetki bayrakları / yardımcı alanlar
const staticPrimaryColor = '#7367F0';
const gaugeLabelColor = '#7367F0';
const canManagePlanlama = computed(() => true);

// Planlama notu seçimi ve alanı
const selectedNot = ref<any>(null);
const notlar = ref<Array<{ not: string }>>([
  { not: 'Mekanik eksik' },
  { not: 'Satınalma eksik' },
  { not: 'Revizyon yapıldı. Müşteri / Arge' },
  { not: 'Ürün ağacı yok' },
  { not: 'Beklemede - Yönetim - Fiyat' },
  { not: 'İptal - Müşteri' },
]);
const textAreaValue = ref<string>("");
const addToTextArea = () => {
  const val = (selectedNot.value && (selectedNot.value.not || selectedNot.value)) || '';
  if (!val) return;
  textAreaValue.value = textAreaValue.value ? `${textAreaValue.value}\n${val}` : String(val);
};
const copyToClipboard = async () => {
  try {
    await navigator.clipboard.writeText(textAreaValue.value || "");
    notify("Notlar panoya kopyalandı.", "success", 1200);
  } catch (e) {
    notify("Kopyalama başarısız.", "error", 1500);
  }
};

// Malzeme satırına tıklamada eksik stok bilgisi güncelle
const onRowClick = (e: any) => {
  const data = e?.data || e?.row?.data;
  if (!data) return;
  eksikStokId.value = Number(data.item_id || 0);
  eksikStokKodu.value = String(data.stok_kodu || "");
  eksikStokAdi.value = String(data.stok_adi || "");
};

const onRowClickDetay = (e: any): void => {
  if (e.rowType === 'data') {
    gridBakiyeler.value = []
    eksikStokKodu.value = e.data.stok_kodu
    eksikStokAdi.value = e.data.stok_adi
    getBakiyeler(e.data.item_id)
    popupDepolarGosterVisible.value = true
  }
}

const updateTime = () => {
  const now = new Date();
  const gunler = [
    "Pazar",
    "Pazartesi",
    "Salı",
    "Çarşamba",
    "Perşembe",
    "Cuma",
    "Cumartesi",
  ];
  const gun = String(now.getDate()).padStart(2, "0");
  const ay = String(now.getMonth() + 1).padStart(2, "0");
  const yil = now.getFullYear();
  const gunAdi = gunler[now.getDay()];

  currentDate.value = `${gun}.${ay}.${yil} ${gunAdi}`;
  currentTime.value = now.toLocaleTimeString("tr-TR", {
    hour: "2-digit",
    minute: "2-digit",
    // second: "2-digit",
  });
};

function vardiyadaKacinciDakika(now: Date = new Date()): number {
  function createTime(hour: number, minute: number) {
    const d = new Date(now);
    d.setHours(hour, minute, 0, 0);
    return d;
  }

  // Vardiya saatleri
  const baslangic = createTime(8, 0);
  const bitis = createTime(18, 0);

  // Eğer 18:00'den sonra ise sabit 540 döndür
  if (now >= bitis) return 540;
  if (now < baslangic) return 0;

  const Molalar = [
    [createTime(10, 0), createTime(10, 15)], // 15 dk çay
    [createTime(12, 45), createTime(13, 15)], // 30 dk yemek
    [createTime(15, 0), createTime(15, 15)], // 15 dk çay
  ];

  // Geçen süre (dakika)
  const gecenDakika = Math.floor((now.getTime() - baslangic.getTime()) / 60000);

  // Mola süresi toplamı
  let MolaDakika = 0;
  for (const [MolaBasla, MolaBitis] of Molalar) {
    if (now > MolaBasla) {
      const efektifBitis = now < MolaBitis ? now : MolaBitis;
      MolaDakika += Math.floor(
        (efektifBitis.getTime() - MolaBasla.getTime()) / 60000
      );
    }
  }

  return gecenDakika - MolaDakika;
}

const formatTarih = (tarihStr?: string) => {
  if (!tarihStr) return "-";
  return dayjs(tarihStr).format("DD MMMM YYYY dddd HH:mm");
};
const Yenile = async () => {
  await getData();
};
const kaydetOptions = {
  width: 100,
  type: "success",
  text: "Kaydet",
  stylingMode: "contained",
};
const vazgecOptions = {
  width: 100,
  type: "normal",
  text: "Vazgeç",
  stylingMode: "contained",
  onClick: () => {
    popupNotGirVisible.value = false;
    popupDetayGosterVisible.value = false
  },
};
const kapatOptions = {
  width: 100,
  type: "success",
  text: "Kapat",
  stylingMode: "contained",
};

const aktifEkipleriAl = async () => {
  const { data } = await axios.get("/api/aktif-ekipler", {
    params: {
      isemriID: secili.value.isEmriId,
      guid: secili.value.guid,
    },
  });
  aktifEkip.value = data;
};

const AktifIsaretle = async () => {
  gridData.value.forEach((grid) => {
    grid.aktif = 0;
  });
  items.value.forEach((montaj) => {
    gridData.value.forEach((grid) => {
      if (grid.isemri_id === Number(montaj.isemriId)) {
        grid.aktif = 1;
      }
    });
  });
  // montajVerileri.value.forEach(montaj => {
  //   gridData.value.forEach(grid => {
  //     if (grid.isemri_id === Number(montaj.isEmriId)) {
  //       grid.aktif = 1;
  //     }
  //   });
  // });
};

const onContentReady = async (e: any) => {
  const gridInstance = dataGridRef.value?.instance;
  totalRecord.value = gridInstance?.getDataSource()?.totalCount() ?? 0;
};

const saveGridState = () => {
  let state = null;
  if (dataGridRef.value && dataGridRef.value.instance) {
    state = dataGridRef.value.instance.state();
    localStorage.setItem("isemirleri_uretim", JSON.stringify(state));
  }
};
const loadGridState = () => {
  const savedState = localStorage.getItem("isemirleri_uretim");
  if (savedState && dataGridRef.value && dataGridRef.value.instance)
    dataGridRef.value.instance.state(JSON.parse(savedState));
};
const onStateResetClick = () => {
  localStorage.removeItem("isemirleri_uretim");
  dataGridRef.value!.instance!.state(null);
};

const renkleriGoster = (cellElement: HTMLElement, cellInfo: any): void => {
  const renkID = cellInfo.data.renk_kodu;

  cellElement.innerText = cellInfo.text;

  const createIcon = (color: string) => {
    const icon = document.createElement("span");

    icon.className = "dx-icon dx-icon-square";
    icon.style.marginLeft = "8px"; // İkon ile metin arasına boşluk ekle
    icon.style.color = color; // İkon rengi
    icon.style.fontSize = "16"; // Ana span'ı etkisizleştir
    icon.style.display = "inline-flex";

    return icon;
  };

  if (renkID) {
    let renk = "";
    switch (renkID) {
      case "RAL7035":
        renk = "#D7D7D7";
        break;
      case "RAL7016":
        renk = "#293133";
        break;
      case "RAL7031":
        renk = "#474B4E";
        break;
      case "RAL7032":
        renk = "#721422";
        break;
      case "RAL5026":
        renk = "#102C54";
        break;
      case "RAL7037":
        renk = "#7D7F7D";
        break;
      case "RAL9003":
        renk = "#F4F4F4";
        break;
      case "RAL9005":
        renk = "#0A0A0A";
        break;
      case "RAL9006":
        renk = "#A5A5A5";
        break;
      case "RAL9007":
        renk = "#8F8F8F";
        break;
      case "RAL9010":
        renk = "#FFFFFF";
        break;
      case "RAL9016":
        renk = "#F6F6F6";
        break;
      default:
        renk = "white";
        break;
    }

    // Kalite onayı bekleyenler mavi, Beklemede olanlar kademeli kırmızı, Müşteri iptali koyu gri işaretli olarak yapar mısınız.
    const renkIcon = createIcon(renk);

    cellElement.insertBefore(renkIcon, cellElement.firstChild);
  }
};
const panelKapat = async (item: any) => {
  secili.value = item;
  // await topluBitir();
  if (item.isemriId) {
    const index = montajVerileri.value.findIndex(
      (item) =>
        item.isEmriNo === secili.value.isEmriNo &&
        item.personelID === userData.value.id
    );
    if (index !== -1) {
      montajVerileri.value.splice(index, 1);
    }
  }

  await durumuGuncelle("KAPANDI");
  // await nextTick(); // Vue reaktif güncellemeleri tamamlansın
  // await Pasif();
  // await isEmriKapat();
  // await fetchKartlar();
  gridDataDuruslar.value = [];
  resetSeciliIsEmri();
};
const resetSeciliIsEmri = () => {
  secili.value = {
    isEmriId: 0,
    isEmriNo: "...",
    urunID: 0,
    urunAdi: "...",
    urunKodu: "...",
    belgeNo: "...",
    cariAdi: "...",
    calSure: 0,
    durSure: 0,
    personelSayisi: 0,
    sure: 0,
    durum: "",
    sebep: null,
    calOran: 0,
    durOran: 0,
    istasyonID: 0,
    personelID: 0,
    planlamaNotu: "...",
    UretimNotu: "...",
    urunAgaciText: "...",
    urunAgaciCheck: false,
    gorunumText: "",
    gorunumCheck: false,
    olcuselText: "",
    olcuselCheck: false,
    paketlemeText: "",
    paketlemeCheck: false,
    etiketText: "",
    etiketCheck: false,
    dokumanText: "",
    dokumanCheck: false,
    kontrolGerekli: false,
    kontrolcuCagir: false,
    guid: "...",
    sahip: false, // Added property to fix the error
  };
};
// const topluBitir = async () => {
//   const idList = aktifEkip.value.map((item) => item.id);
//   console.log("Toplu bitirilecek ekipler:", idList);
//   try {
//     await axios.post("/api/ekip-bitir-toplu", { ids: idList });

//     aktifEkip.value = []; // listeyi boşalt
//     notify("Ekibin görevleri bitirildi.", "success");
//   } catch (error) {
//     notify("Toplu bitirme işlemi sırasında hata oluştu.", "error");
//     console.error(error);
//   }
// };

const weekCellTemplate = (cellElement: HTMLElement, cellInfo: any): void => {
  const hasNoteP =
    cellInfo.data.teknik_not1 && cellInfo.data.teknik_not1.trim() !== "";
  const hasNoteU =
    cellInfo.data.teknik_not2 && cellInfo.data.teknik_not2.trim() !== "";
  const eksik = cellInfo.data.eksikler;

  // Ana metni ekle (hafta değeri)
  cellElement.innerText = cellInfo.text;

  const createIcon = (color: string, not: string) => {
    const icon = document.createElement("span");
    // icon.className = not === "Eksikler Var" ? "dx-icon dx-icon-warning" : "dx-icon dx-icon-square";

    if (not === "Planlamanın Notu") icon.className = "dx-icon dx-icon-square";
    else if (not === "Üretimin Notu")
      icon.className = "dx-icon dx-icon-message";
    else icon.className = "dx-icon dx-icon-warning";

    icon.style.marginLeft = "4px"; // İkon ile metin arasına boşluk ekle
    icon.style.color = color; // İkon rengi
    icon.style.fontSize = "16"; // Ana span'ı etkisizleştir
    icon.style.display = "inline-flex";

    // Tıklama olayı ekle
    icon.addEventListener("click", () => {
      planlamaNotu.value =
        not === "Planlamanın Notu"
          ? cellInfo.data.teknik_not1
          : cellInfo.data.teknik_not2;
      notBaslik.value = not;
      popupMesajGosterVisible.value = !popupMesajGosterVisible.value;
    });

    return icon;
  };

  if (hasNoteP) {
    let renk = "";
    switch (cellInfo.data.teknik_not1) {
      case "Mekanik eksik":
        renk = "orange";
        break;
      case "Satınalma eksik":
        renk = "red";
        break;
      case "Revizyon yapıldı. Müşteri / Arge":
        renk = "gray";
        break;
      case "Ürün ağacı yok":
        renk = "gray";
        break;
      case "Beklemede - Yönetim - Fiyat":
        renk = "#f79191";
        break;
      case "İptal - Müşteri":
        renk = "darkgray";
        break;
      default:
        renk = "#a29bef";
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

  if (eksik === "1") {
    const redIcon = createIcon("red", "Eksikler Var");
    cellElement.appendChild(redIcon);
  }
};
const detaylariGoster = async (item: any) => {
  // Aynı kart tekrar tıklandıysa hiçbir şey yapma
  if (secili.value.guid === item.guid) return;
  // Yeni karta geçerken malzeme gridini temizle, otomatik yükleme yapma
  gridDataEksikListesi.value = [];
  secili.value.guid = item.guid;
  secili.value.isEmriId = item.isemriId;
  secili.value.isEmriNo = item.isemriNo;
  secili.value.urunID = item.urunID;
  secili.value.urunAdi = item.urunAdi;
  secili.value.urunKodu = item.urunKodu;
  secili.value.istasyonID = item.istasyonID;
  secili.value.personelID = item.personelId;
  selectedIsemriNo.value = item.isemriNo;
  // İstenirse butonla yüklenecek
};
const duruslariAl = async () => {
  try {
    showLoading("Duruş Listesi getiriliyor...");
    const response = await axios.get("/api/duruslar-aktif", {
      params: {
        istasyon: userData.value.istasyon_id,
        // guid: secili.value.guid,
      },
    });
    gridDataDuruslar.value = response.data.duruslar;
  } catch (error) {
    console.error("Veri çekilirken hata oluştu: ", error);
  }
  hideLoading();
};
const kontrolGerekKaydet = async () => {
  if (!secili.value) return;

  try {
    await axios.post("/api/kontrolGerekKaydet", {
      isEmriID: Number(secili.value.isEmriId),
      itemID: Number(secili.value.urunID),
      istasyonID: secili.value.istasyonID,
      isEmriNo: secili.value.isEmriNo,
      urunKodu: secili.value.urunKodu,
      urunAdi: secili.value.urunAdi,
      userID: userData.value.id,
      belgeNo: secili.value.belgeNo,
      cariAd: secili.value.cariAdi,
    });
  } catch (error) {
    // console.error('Kaydetme sırasında bir hata oluştu:', error);
  }
};
const getData = async () => {
  try {
    showLoading("İş Emri listesi yükleniyor...");
    const response = await axios.get("/api/dataUretimEmirler", {
      params: {
        istasyon: userData.value.istasyon_id,
      },
    });
    gridData.value = response.data.emirler;
    await AktifIsaretle();
  } catch (error) {
    console.error("Veri çekilirken hata oluştu: ", error);
  } finally {
    hideLoading();
  }
};
const getEksikler = async () => {
  showLoading("Eksikler alınıyor...");
  try {
    const response = await axios.get("/api/eksik-kontrolu", {
      params: {
        istasyon: userData.value.istasyon_id,
        depo: selectedRow.value.CIKIS_DEPO,
        hafta: getCurrentWeek(),
        depolar: depolar.value,
      },
    });
    gridData.value = response.data.emirler;
  } catch (error) {
    console.error("Veri çekilirken hata oluştu: ", error);
  } finally {
    hideLoading();
  }
};
const getMalzemeListesi = async () => {
  try {
    showLoading("Malzeme Listesi yükleniyor...");
    const response = await axios.get("/api/isEmriDetay", {
      params: {
        tablo: "DETAY",
        isemri_id: secili.value.isEmriId,
      },
    });
    gridDataEksikListesi.value = response.data.malzemeler;
  } catch (error) {
    console.error("Veri çekilirken hata oluştu: ", error);
  } finally {
    hideLoading();
  }
};
const getBakiyeler = async (itemID: number) => {
  try {
    const response = await axios.get("/api/digerdepobakiyeleri", {
      params: {
        item_id: itemID,
        depo: selectedRow.value.CIKIS_DEPO,
      },
    });
    gridBakiyeler.value = response.data.bakiyeler;
  } catch (error) {
    console.error("Veri çekilirken hata oluştu: ", error);
  }
};
const getCurrentWeek = (): string => {
  const today = new Date();
  const year = today.getFullYear() % 100;

  // Haftanın ilk günü pazartesi kabul edilsin ve ISO 8601 standartlarına uygun hafta numarası hesaplansın
  const firstThursday = new Date(today.getFullYear(), 0, 4); // Yılın ilk perşembesi
  const firstMonday = new Date(firstThursday);
  firstMonday.setDate(
    firstThursday.getDate() - ((firstThursday.getDay() + 6) % 7)
  ); // İlk pazartesiyi bul

  // Gün farkını hesapla ve hafta numarasını bul
  const diff = Math.floor((today.getTime() - firstMonday.getTime()) / 86400000);
  const week = Math.floor(diff / 7) + 1;

  return `${year}-${week.toString().padStart(2, "0")}`;
};
const tipCellTemplate = (cellElement: HTMLElement, cellInfo: any): void => {
  cellElement.innerText = cellInfo.text;
  const tip = cellInfo.data.tipi;

  const createIcon = (color: string, dolu: boolean) => {
    const icon = document.createElement("span");
    icon.className = dolu
      ? "dx-icon dx-icon-isnotblank"
      : "dx-icon dx-icon-isblank";
    icon.style.marginRight = "8px"; // İkon ile metin arasına boşluk ekle
    icon.style.color = color; // İkon rengi
    icon.style.fontSize = "16"; // Ana span'ı etkisizleştir
    icon.style.display = "inline-flex";
    return icon;
  };

  let renk: string | undefined;

  switch (tip) {
    case "Hammadde":
      renk = "gold";
      break;
    case "YarıMamul":
      renk = "limegreen";
      break;
    case "Mamul":
      renk = "green";
      break;
    case "Ticari":
      renk = "gray";
      break;
  }
  const plnIcon = createIcon(renk || "gray", true);
  cellElement.insertBefore(plnIcon, cellElement.firstChild);
};


const aktifEt = async () => {
  secili.value = {
    isEmriId: Number(selectedRow.value.isemri_id),
    isEmriNo: selectedRow.value.isemri_no,
    urunID: selectedRow.value.item_id,
    urunAdi: selectedRow.value.stok_adi,
    urunKodu: selectedRow.value.stok_kodu,
    belgeNo: selectedRow.value.siparis_belge_no,
    cariAdi: selectedRow.value.cari_ad,
    istasyonID: selectedRow.value.IS_ISTASYONU_ID,
    personelID: userData.value.id,
    calSure: 0,
    durSure: 0,
    personelSayisi: 0,
    sure: 0,
    durum: "ÇALIŞIYOR",
    sebep: null,
    calOran: 0,
    durOran: 0,
    planlamaNotu: "",
    UretimNotu: "",
    urunAgaciText: "",
    urunAgaciCheck: false,
    gorunumText: "",
    gorunumCheck: false,
    olcuselText: "",
    olcuselCheck: false,
    paketlemeText: "",
    paketlemeCheck: false,
    etiketText: "",
    etiketCheck: false,
    dokumanText: "",
    dokumanCheck: false,
    kontrolGerekli: false,
    kontrolcuCagir: false,
    guid: guid.value,
    sahip: true, // Kullanıcı kendisi olduğu için sahiplik true
  };
  await durumuGuncelle("ÇALIŞIYOR");

  await kontrolGerekKaydet();
  const selectedItemIndex1 = gridData.value.findIndex(
    (item: { isemri_no: string }) =>
      item.isemri_no === selectedRow.value.isemri_no
  );
  const updatedData = [...gridData.value];

  if (selectedItemIndex1 !== -1) {
    updatedData[selectedItemIndex1].aktif = 1;
  }
  gridData.value = updatedData;

  // await nextTick(); // Vue reaktif güncellemeleri tamamlansın
  // Kart ve malzeme listesi yenilemesini ekipSecDialog kapandıktan sonra yapacağız
};

const durumuGuncelle = async (durum: any) => {
  selectedSebep.value = null;
  try {
    await axios.post("/api/durumKaydet", {
      isEmriId: secili.value.isEmriId,
      isEmriNo: secili.value.isEmriNo,
      urunID: secili.value.urunID,
      urunKodu: secili.value.urunKodu,
      urunAdi: secili.value.urunAdi,
      durum: durum,
      vardiya: 2,
      istasyonKodu: userData.value.istasyon_id,
      userId: userData.value.id,
      personelSayisi: 0,
      selectedDurus: selectedSebep.value,
      guid: secili.value.guid,
    });
  } catch (error) {
    console.error("Kaydetme sırasında bir hata oluştu:", error);
  }
};

const NotKaydet = async () => {
  try {
    const selectedItemIndex = gridData.value.findIndex(
      (item) => item.isemri_no === selectedRow.value.isemri_no
    );

    if (selectedItemIndex === -1) {
      notify("Güncellenecek kayıt bulunamadı.", "success", 1500);
      return;
    }

    const response = await axios.post("/api/planNotKaydet", {
      satir_id: selectedRow.value.isemri_no,
      not: uretimNotu.value,
      alan: "technical_note2",
    });

    if (response.status === 200) {
      // Hafızadaki veriyi güncelle
      const updatedData = [...gridData.value];

      updatedData[selectedItemIndex].teknik_not2 = uretimNotu.value;
      gridData.value = updatedData;

      // Grid'i zorla yeniden render et
      // gridKey.value = Date.now();
      notify("Notlar başarıyla güncellendi...", "success", 1500);
    }
  } catch (error) {
    console.error("Kaydetme sırasında bir hata oluştu:", error);
  }
  popupNotGirVisible.value = false;
};

const scrollNext = () => {
  const el = carouselScroll.value;
  if (!el) return;
  const cardWidth = 30;
  const maxScroll = el.scrollWidth - el.clientWidth;
  if (el.scrollLeft + cardWidth >= maxScroll - 10)
    el.scrollTo({ left: 0, behavior: "smooth" });
  else el.scrollBy({ left: cardWidth, behavior: "smooth" });
};

const startAutoScroll = () => {
  stopAutoScroll();
  autoScrollInterval = setInterval(scrollNext, 10000);
};

const stopAutoScroll = () => {
  if (autoScrollInterval) clearInterval(autoScrollInterval as any);
};

const pauseAutoScroll = () => stopAutoScroll();
const resumeAutoScroll = () => startAutoScroll();

let interval: ReturnType<typeof setInterval>;
let zaman: ReturnType<typeof setInterval>;

const verileriAl = async () => {
  await fetchKartlar();
  //await veriHaftalikUretimler();
};

// Haftalık veri ürün ID -> miktar eşleşmeleri içerir; her gün için dinamik alanlar olabilir.
const haftalikVeri = ref<Record<string, Record<string, number>>>({});
const tarihAraligi = ref([]);
const seciliHafta = ref("bu");
const grafikVerisi = ref([]);

const getHaftaTarihleri = (haftaTuru: "bu" | "gecen") => {
  const bugun = new Date();
  const gun = bugun.getDay() === 0 ? 7 : bugun.getDay(); // 0 (Pazar) yerine 7 olsun

  const pazartesi = new Date();
  pazartesi.setDate(bugun.getDate() - gun + 1); // Pazartesi

  if (haftaTuru === "gecen") {
    pazartesi.setDate(pazartesi.getDate() - 7);
  }

  const pazar = new Date(pazartesi);
  pazar.setDate(pazartesi.getDate() + 6);

  return {
    baslangic: pazartesi.toISOString().slice(0, 10),
    bitis: pazar.toISOString().slice(0, 10),
  };
};

const veriGetir = async () => {
  const tarihAralik = getHaftaTarihleri(seciliHafta.value as "bu" | "gecen");

  const { data } = await axios.get("/api/haftalik-gunluk-paket-toplam", {
    params: {
      ...tarihAralik,
      istasyon: userData.value.istasyon_id,
    },
  });

  grafikVerisi.value = data.data;
  // console.log("Grafik Verisi:", grafikVerisi.value);
};

watch(seciliHafta, veriGetir, { immediate: true });

const veriHaftalikUretimler = async () => {
  const { data } = await axios.get("/api/haftalik-paket-miktarlari");
  haftalikVeri.value = data.data;
  tarihAraligi.value = data.hafta;
};

interface TabloSatir {
  tarih: string;
  [key: string]: string | number;
}
const tabloVerisi = computed<TabloSatir[]>(() => {
  const sonuc: TabloSatir[] = [];
  for (const gun of Object.keys(haftalikVeri.value)) {
    const items = haftalikVeri.value[gun] || {};
    sonuc.push({ tarih: gun, ...items });
  }
  return sonuc;
});

interface TableHeader {
  title: string;
  key: string;
}
const tableHeaders = computed<TableHeader[]>(() => {
  const itemIds = new Set<string>();
  Object.values(haftalikVeri.value).forEach((gunVerisi) => {
    if (gunVerisi && typeof gunVerisi === "object") {
      Object.keys(gunVerisi).forEach((id) => itemIds.add(id));
    }
  });
  return [
    { title: "Tarih", key: "tarih" },
    ...Array.from(itemIds).map((id) => ({ title: `Ürün ${id}`, key: id })),
  ];
});

const fetchKartlar = async () => {
  try {
    const { data } = await axios.get("/api/uretim-kartlari", {
      params: {
        istasyon: userData.value.istasyon_id,
      },
    });
    items.value = data;
  } catch (e) {
    console.error("Veri hatası:", e);
  }

  try {
    const response = await axios.get("/api/dataUretimPerformans", {
      params: {
        istasyon: userData.value.istasyon_id,
      },
    });
    gunlukHedef.value = Number(
      Number(response.data.bugunKalanToplam).toFixed(0)
    );
    const paketler = response.data.paketler;
    cal.value = response.data.calisma || 0;
    dur.value = response.data.durus || 0;

    // console.log("Paketler:", paketler);
    // console.log("Cal:", cal.value);
    // console.log("Dur:", dur.value);

    anlikUretim.value = (paketler as any[]).reduce(
      (toplam: number, item: any) => {
        const val = parseFloat(item?.paket_miktari ?? 0);
        return toplam + (isNaN(val) ? 0 : val);
      },
      0
    );
  } catch (error) {
    console.error("Performans verisi çekilirken hata oluştu: ", error);
  }

  anlikHedef.value = Number(
    ((vardiyadaKacinciDakika() / 540) * gunlukHedef.value).toFixed(0)
  );
  // console.log("Anlık Hedef:", anlikHedef.value);
  // console.log("Anlık Üretim:", anlikUretim.value);
  uretimHizi.value =
    gunlukHedef.value > 0
      ? Number(((anlikUretim.value / anlikHedef.value) * 100 - 100).toFixed(1))
      : 0;
  // console.log("Üretim Hızı:", uretimHizi.value);

  kul.value = cal.value / (cal.value + dur.value);
  urt.value = anlikUretim.value / anlikHedef.value;
  if (urt.value < 0) urt.value = 0;
  if (urt.value > 1) urt.value = 1;
  kal.value = 1;
  oee.value = kul.value * urt.value * kal.value * 100;
  // console.log("OEE1:", oee.value);
  kul.value = isNaN(kul.value) ? 0 : kul.value > 1 ? 100 : kul.value * 100;
  urt.value = isNaN(urt.value) ? 0 : urt.value > 1 ? 100 : urt.value * 100;
  kal.value = isNaN(kal.value) ? 0 : kal.value > 1 ? 100 : kal.value * 100;

  barVeri.value = [
    Math.round(kul.value),
    Math.round(urt.value),
    Math.round(kal.value),
  ];
  // console.log("Bar Veri:", barVeri.value);
};

// ProductionCard içindeki herhangi bir aksiyon sonrası tetiklenen genel yenileme
const onCardAction = async () => {
  await fetchKartlar();
  await aktifEkipleriAl?.(); // varsa mevcut fonksiyon
  // Duruş listesi manuel butonla yüklenecek
};

const onDurusGirildi = async () => {
  // Duruş popup kapandıktan sonra yalnızca kartları güncelle
  await fetchKartlar();
};

const emit = defineEmits(["ilk-veri-hazir"]);

onMounted(async () => {
  document.title = "OFT - Montaj";
  pageTitleStore.setTitle("İstasyon: " + userData.value.proses);
  loadGridState();
  zaman = setInterval(updateTime, 10000);
  await getData();
  await fetchKartlar();
  // İlk temel veri seti + kartlar yüklendi sinyali
  try {
    emit("ilk-veri-hazir");
  } catch (e) {
    /* emit başarısız olsa bile akışı bozma */
  }
  // await duruslariAl();
  startAutoScroll();
  interval = setInterval(verileriAl, 15000);
  // Otomatik mola kontrolü kaldırıldı
});

onBeforeUnmount(() => {
  clearInterval(interval);
  clearInterval(zaman);
  stopAutoScroll();
});

// Personel seçim dialogu eventleri
const onEkipKaydedildi = async () => {
  try {
    showLoading("Aktif ediliyor...");
    await aktifEt();
    ekipSecDialog.value = false;
    await fetchKartlar();
    // Malzeme listesi manuel butonla yüklenecek; otomatik çağrılmıyor
    gridDataEksikListesi.value = [];
  } finally {
    hideLoading();
  }
};

const onEkipIptal = () => {
  ekipSecDialog.value = false;
};

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
const gridDataDetay = ref<any[]>([])
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


const formatNumber = (num: number): string => {
  return new Intl.NumberFormat('tr-TR', { maximumFractionDigits: 0 }).format(num)
}

const menuItems = [
  { text: "Yenile" },
  { text: "Aktif Yap" },
  { text: "Detay Göster" },
  // { text: "Teknik Resim Göster" },
  { text: "Not Gir" },
  { text: "Düzen Yükle" },
  { text: "Düzen Kaydet" },
  { text: "Düzen Sıfırla" },
];

const menuItemsM = [{ text: "Yenile" }, { text: "Depoları Göster" }];

function itemClick({ itemData }: DxContextMenuTypes.ItemClickEvent) {
  if (!itemData?.items) {
    switch (itemData?.text) {
      case "Yenile":
        getData();
        break;
      case "Aktif Yap":
        guid.value = uuidv4();
        // Slider kart seçim bilgisi güncelle
        selectedIsemriNo.value = selectedRow.value.isemri_no;
        const alreadyActive = items.value.some(
          (it: any) => it?.isemriNo === selectedRow.value.isemri_no
        );
        if (alreadyActive) {
          notify("Bu iş emri zaten aktif.", "success", 2000);
          return;
        }
        ekipSecDialog.value = true;
        if (secili) secili.value.guid = guid.value;
        // Aktivasyon, personel seçimi kaydedildikten sonra yapılacak
        // console.log("Aktivasyon işlemi gerçekleştiriliyor...");
        break;
      case "Detay Göster":
        DetayGoster();
        break;
      case "Teknik Resim Göster":
        // ResimGoster();
        break;
      case "Not Gir":
        if (selectedRow.value.isemri_id === null) {
          notify("Önce iş emrini seçmelisiniz...", "error", 1500);
          return;
        }
        uretimNotu.value = selectedRow.value.teknik_not2;
        popupNotGirVisible.value = true;
        break;
      case "Düzen Yükle":
        loadGridState();
        break;
      case "Düzen Kaydet":
        saveGridState();
        break;
      case "Düzen Sıfırla":
        onStateResetClick();
        break;
      default:
        break;
    }
  }
}

function itemClickM({ itemData }: DxContextMenuTypes.ItemClickEvent) {
  if (!itemData?.items) {
    switch (itemData?.text) {
      case "Yenile":
        getMalzemeListesi();
        break;
      case "Depoları Göster":
        gridBakiyeler.value = [];
        getBakiyeler(eksikStokId.value);
        popupDepolarGosterVisible.value = true;
        break;
      default:
        break;
    }
  }
}

const onFocusedRowChanged = (e: any) => {
  if (e.rowIndex === -1) return;
  const data = e.row!.data!;
  selectedRow.value = data;
  focusedRowKey.value = data.satir_id;
  selectedRow.value.siparis_belge_no =
    data.siparis_belge_no != null ? data.siparis_belge_no : "";
  // textAreaValue.value = data.teknik_not1 != null ? data.teknik_not1 : '';
  // uretimNotu.value = data.teknik_not2 != null ? data.teknik_not2 : '';
};

const onCellPrepared = (e: any) => {
  if (
    e.rowType === "data" &&
    (e.column.dataField === "isemri_miktari" ||
      e.column.dataField === "uretilen_net_miktar" ||
      e.column.dataField === "kalan_miktar") &&
    e.value > 0
  ) {
    e.cellElement.style.fontWeight = "bold";
  }
  if (
    e.rowType === "data" &&
    e.column.dataField === "toplam_hurda_miktari" &&
    e.value > 0
  ) {
    e.cellElement.style.color = "white";
    e.cellElement.style.fontWeight = "bold";
    e.cellElement.style.backgroundColor = "#EC2407";
  }
  if (e.rowType === "data" && e.column.dataField === "hafta") {
    e.cellElement.style.fontWeight = "bold";
  }
};

// (getIconType tanımı yukarıda mevcut)

const onCellPreparedM = (e: any) => {
  if (
    e.rowType === "data" &&
    (e.column.dataField === "talepler" ||
      e.column.dataField === "satinalma" ||
      e.column.dataField === "stok" ||
      e.column.dataField === "ana_depo" ||
      e.column.dataField === "diger_depo") &&
    e.value > 0
  ) {
    e.cellElement.style.fontWeight = "bold";
  }
  if (
    e.rowType === "data" &&
    e.column.dataField === "stok" &&
    Number(e.data.stok) < Number(e.data.ihtiyac)
  ) {
    e.cellElement.style.backgroundColor = "orange";
  }
  if (
    e.rowType === "data" &&
    e.column.dataField === "ana_depo" &&
    Number(e.data.ana_depo) + Number(e.data.stok) < Number(e.data.ihtiyac)
  ) {
    e.cellElement.style.backgroundColor = "red";
    e.cellElement.style.color = "white";
  }
  if (
    e.rowType === "data" &&
    e.column.dataField === "diger_depo" &&
    Number(e.data.diger_depo) + Number(e.data.ana_depo) + Number(e.data.stok) <
    Number(e.data.ihtiyac)
  ) {
    e.cellElement.style.backgroundColor = "red";
    e.cellElement.style.color = "white";
  }

  if (e.rowType === "data" && e.column.dataField === "bakiye") {
    e.cellElement.style.fontWeight = "bold";
    if (e.value < 0) {
      e.cellElement.style.backgroundColor = "red";
      e.cellElement.style.color = "white";
    }
  }
};

const onCellPreparedB = (e: any) => {
  if (e.rowType === "data" && e.column.dataField === "qty_prm") {
    e.cellElement.style.fontWeight = "bold";
    if (e.value < 0) {
      e.cellElement.style.backgroundColor = "red";
      e.cellElement.style.color = "white";
    }
  }
};

const onContextMenuPreparing = (e: any) => {
  const data = e.row!.data!;

  if (e.row!.rowType! === "data") {
    eksikStokId.value = data.item_id;
    eksikStokKodu.value = data.stok_kodu;
    eksikStokAdi.value = data.stok_adi;
  }
};
</script>

<style scoped>
.grid {
  display: flex;
  flex-direction: column;
  block-size: 60vh;
}

.gosterge {
  display: flex;
  flex-direction: column;
  inline-size: 100%;
}

.carousel-scroll {
  display: flex;
  box-sizing: border-box;
  border: 2px solid gray;
  border-radius: 10px;
  background-color: #349c2f;
  background-image: url("https://www.transparenttextures.com/patterns/dark-mosaic.png");
  background-repeat: repeat;
  background-size: auto;
  block-size: 318px;
  gap: 16px;
  inline-size: 100%;
  overflow-x: auto;
  scroll-snap-type: x mandatory;
}

.carousel-card {
  flex: 0 0 auto;
  cursor: pointer;
  inline-size: 550px;
  scroll-snap-align: start;
}

.detail-section {
  display: flex;
  flex-direction: row;
  gap: 10px;
  inline-size: 100%;
  margin-block-start: 0;
}

.ust-section {
  inline-size: 100%;
  margin-block-start: -10px;
}

.detail-card {
  flex: 0 0 400px;
}

.tab-card {
  flex: 1;
  overflow-x: auto;
}

.d-flex {
  display: flex;
}

.justify-space-between {
  justify-content: space-between;
}

.align-center {
  align-items: center;
}

.time-display {
  color: #413e87;

  /* font-family: 'Montserrat', sans-serif; */
  font-size: 22px;
  font-weight: 500;
  inline-size: fit-content;
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

.miktar {
  font-size: 28px;
  font-weight: bold;
  margin-block-start: -10px;
  text-align: end;
}

.baslik {
  padding: 4px;
  border-radius: 6px 6px 0 0;

  /* background-color: rgb(var(--v-theme-table-header-color)); */
  block-size: 47px;
  padding-inline-start: 15px;
}

.baslik1 {
  padding: 4px;
  border-radius: 6px 6px 0 0;

  /* background-color: rgb(var(--v-theme-table-header-color)); */
  font-size: 18px;
  padding-inline-start: 15px;
}

.popup-title {
  background-color: #393378;
  color: white;
  font-size: 20px;
  font-weight: bold;
}

.uretim {
  /* color: #efc002; */
  margin-block-start: -10px;
  padding-inline: 10px;
  text-align: center;
}

#gauge {
  padding: 0%;
}
</style>

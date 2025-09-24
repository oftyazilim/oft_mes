<template>
  <VRow class="match-height pa-0">
    <VCol cols="6" md="3" class="pa-2">
      <VBtn variant="outlined" class="text-center mb-2" block @click="popupIsemriSecVisible = true">
        İş Emri Seç
      </VBtn>
    </VCol>

    <VCol cols="6" md="3" class="pa-2">
      <VBtn variant="outlined" :disabled="isEmriNo.length === 0" class="text-center mb-2" block @click="pdfGoster">
        Teknik Resim
      </VBtn>
    </VCol>

    <VCol cols="6" md="3" class="pa-2">
      <VBtn variant="outlined" :disabled="isEmriNo.length === 0" class="text-center mb-2" block @click="KontrolKapat">
        Kontrol Sonu
      </VBtn>
    </VCol>

    <VCol cols="6" md="3" class="pa-2">
      <VBtn variant="outlined" :disabled="isEmriNo.length === 0" class="text-center mb-2" block @click="hataAc">
        Mail Gönder
      </VBtn>
    </VCol>
  </VRow>

  <VRow class="match-height mt-0 gap-0">

    <VCol md="4" cols="12">
      <VCard>

        <VCardItem style="background-color: #20ad5d;" class="d-flex align-center pa-1 ps-4">
          <VCardTitle style=" color: black; font-size: 16px;">Genel Bilgiler</VCardTitle>
        </VCardItem>

        <VCardText class="gap-2 pa-2">
          <VTextField v-model="seciliIsEmri.istasyon" class="mt-5" label="İstasyon" variant="outlined" readonly />
          <VTextField v-model="seciliIsEmri.siparisNo" class="mt-4" label="Sipariş No" variant="outlined" readonly />
          <VTextField :value="formatNumber(seciliIsEmri.isEmriMiktari)" v-model="seciliIsEmri.isEmriMiktari"
            class="mt-4" label="İş Emri Miktarı" variant="outlined" readonly />
          <VTextField v-model="seciliIsEmri.isEmriNo" class="mt-4" label="İşemri No" variant="outlined" readonly />
          <VTextField v-model="seciliIsEmri.urunKodu" class="mt-4" label="Ürün Kodu" variant="outlined" readonly />
          <VTextField v-model="seciliIsEmri.urunAdi" class="mt-4" label="Ürün Adı" variant="outlined" readonly />
          <VTextField v-model="seciliIsEmri.musteri" class="mt-4" label="Müşteri" variant="outlined" readonly />
          <VTextField v-model="seciliIsEmri.planTarih" class="mt-4" label="Planlanan Üretim Tarihi" variant="outlined"
            readonly />
          <VCheckbox v-model="seciliIsEmri.aksesuar" class="mt-4" label="Aksesuar" variant="outlined" readonly />
        </VCardText>

      </VCard>
    </VCol>

    <VCol md="8" cols="12">


      <VCol cols="12" class="pa-0">
        <VCard :disabled="isEmriNo.length === 0">
          <VCardItem style="background-color: #20ad5d;" class="d-flex align-center pa-1 ps-4">
            <VCardTitle style=" color: black; font-size: 16px;">Seri Başlangiç Onayı</VCardTitle>
          </VCardItem>

          <VCardText class="gap-2 pa-3">
            <VRow dense>
              <VCol cols="12" md="6" class="d-flex mt-2 pa-0">
                <VRow dense>
                  <VCol cols="12" md="6" class="d-flex pa-1 pe-2">
                    <VTextarea v-model="kontroller.urunAgaciText" label="Ürün Ağacı Kontrolü" variant="outlined"
                      auto-grow rows="1" />
                  </VCol>
                  <VCol cols="6" md="3" class="d-flex pa-1">
                    <VBtn v-model="kontroller.urunAgaciCheck" icon="tabler-binary-tree-2" variant="tonal" block
                      color="success" rounded @click="popupUrunAgaciVisible = true"></VBtn>
                  </VCol>
                  <VCol cols="6" md="3" class="d-flex pa-1 pe-2">
                    <DxSelectBox :data-source="data" v-model:value="kontroller.urunAgaciCheck" display-expr="title"
                      value-expr="value" :height="37" :read-only="true" placeholder="Boş" />
                  </VCol>
                </VRow>
              </VCol>

              <VCol cols="12" md="6" class="d-flex mt-2 pa-0">
                <VRow dense>
                  <VCol cols="6" class="d-flex pa-1">
                    <VTextarea v-model="kontroller.gorunumText" label="Görünüm Kontrolü" variant="outlined" auto-grow
                      rows="1" />
                  </VCol>
                  <VCol cols="6" class="d-flex pa-1 pe-2">
                    <DxSelectBox :data-source="data" v-model:value="kontroller.gorunumCheck" display-expr="title"
                      value-expr="value" :height="37" />
                  </VCol>
                </VRow>
              </VCol>

              <VCol cols="12" md="6" class="d-flex mt-2 pa-0">
                <VRow dense>
                  <VCol cols="6" class="d-flex pa-1">
                    <VTextarea v-model="kontroller.olcuselText" label="Ölçüsel Kontrol" variant="outlined" auto-grow
                      rows="1" />
                  </VCol>
                  <VCol cols="6" class="d-flex pa-1 pe-2">
                    <DxSelectBox :data-source="data" v-model:value="kontroller.olcuselCheck" display-expr="title"
                      value-expr="value" :height="37" />
                  </VCol>
                </VRow>
              </VCol>

              <VCol cols="12" md="6" class="d-flex mt-2 pa-0">
                <VRow dense>
                  <VCol cols="6" class="d-flex pa-1">
                    <VTextarea v-model="kontroller.paketlemeText" label="Paketleme Kontrolü" variant="outlined"
                      auto-grow rows="1" />
                  </VCol>
                  <VCol cols="6" class="d-flex pa-1 pe-2">
                    <DxSelectBox :data-source="data" v-model:value="kontroller.paketlemeCheck" display-expr="title"
                      value-expr="value" :height="37" />
                  </VCol>
                </VRow>
              </VCol>

              <VCol cols="12" md="6" class="d-flex mt-2 pa-0">
                <VRow dense>
                  <VCol cols="6" class="d-flex pa-1">
                    <VTextarea v-model="kontroller.etiketText" label="Etiket Kontrolü" variant="outlined" auto-grow
                      rows="1" />
                  </VCol>
                  <VCol cols="6" class="d-flex pa-1 pe-2">
                    <DxSelectBox :data-source="data" v-model:value="kontroller.etiketCheck" display-expr="title"
                      value-expr="value" :height="37" />
                  </VCol>
                </VRow>
              </VCol>

              <VCol cols="12" md="6" class="d-flex mt-2 pa-0">
                <VRow dense>
                  <VCol cols="6" class="d-flex pa-1">
                    <VTextarea v-model="kontroller.dokumanText" label="Doküman Kontrolü" variant="outlined" auto-grow
                      rows="1" />
                  </VCol>
                  <VCol cols="6" class="d-flex pa-1 pe-2">
                    <DxSelectBox :data-source="data" v-model:value="kontroller.dokumanCheck" display-expr="title"
                      value-expr="value" :height="37" />
                  </VCol>
                </VRow>
              </VCol>


              <VBtn variant="outlined" class="d-flex justify-center mt-4" block color="warning" @click="kaydetKontroller">
                Kaydet / Güncelle
              </VBtn>

            </VRow>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" class="pa-0 pt-3">
        <VCard :disabled="isEmriNo.length === 0">

          <VCardItem style="background-color: #20ad5d;" class="d-flex align-center pa-1 ps-4">
            <VCardTitle style=" color: black; font-size: 16px;">Onay Süreci</VCardTitle>
          </VCardItem>

          <VCardText class="gap-2 pa-2">
            <DxDataGrid id="gridSurec" :data-source="gridSurec" key-expr="id" :show-borders="true"
              :focused-row-enabled="true" :row-alternation-enabled="true" width="100%" :column-auto-width="true"
              :word-wrap-enabled="false" :allow-column-resizing="true" column-resizing-mode="widget"
              v-model:focused-row-key="focusedRowKey" height="242">

              <DxColumn data-field="id" caption="ID" width="50" :visible="false" />
              <DxColumn data-field="revizyon" caption="Rvz" width="50" />
              <DxColumn data-field="kontrol_tarihi" caption="Tarih" data-type="date" width="130" :format="{
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
              <DxColumn data-field="urun_agaci_ok" caption="ÜA" cell-template="sonucTemplate" />
              <DxColumn data-field="urun_agaci_not" caption="Ürün Ağacı Not" />
              <DxColumn data-field="gorunum_ok" caption="GR" cell-template="sonucTemplate" />
              <DxColumn data-field="gorunum_not" caption="Görünüm Not" />
              <DxColumn data-field="olcusel_ok" caption="ÖL" cell-template="sonucTemplate" />
              <DxColumn data-field="olcusel_not" caption="Ölçüsel Not" />
              <DxColumn data-field="paketleme_ok" caption="PK" cell-template="sonucTemplate" />
              <DxColumn data-field="paketleme_not" caption="Paketleme Not" />
              <DxColumn data-field="etiket_ok" caption="ET" cell-template="sonucTemplate" />
              <DxColumn data-field="etiket_not" caption="Etiket Not" />
              <DxColumn data-field="dokuman_ok" caption="DK" cell-template="sonucTemplate" />
              <DxColumn data-field="dokuman_not" caption="Doküman Not" />

              <DxScrolling mode="virtual" />
              <DxSorting mode="none" />

              <template #sonucTemplate="{ data }">
                <template v-if="data.value === 'Onaylandı'">
                  <i>✔️</i>
                  <!-- <i :class="['dx-icon', 'dx-icon-isnotblank']" :style="{ fontSize: '16px', color: 'green' }">✔️</i> -->
                </template>
                <template v-else-if="data.value === 'Şartlı Onay'">
                  <i>⚠️</i>
                  <!-- <i :class="['dx-icon', 'dx-icon-isnotblank']" :style="{ fontSize: '16px', color: 'gold' }"></i> -->
                </template>
                <template v-else-if="data.value === 'Red'">
                  <i>❌</i>
                  <!-- <i :class="['dx-icon', 'dx-icon-isnotblank']" :style="{ fontSize: '16px', color: 'red' }"></i> -->
                </template>
                <template v-else>
                  <i :class="['dx-icon', 'dx-icon-isblank']" :style="{ fontSize: '16px', color: 'yellow' }"></i>
                </template>
              </template>
            </DxDataGrid>
          </VCardText>

        </VCard>
      </VCol>
    </VCol>

  </VRow>

  <VRow class="match-height pa-0">
    <VCol cols="12" class="py-2">
      <VBtn variant="outlined" :disabled="kontroller.masterId === 0" class="text-center me-2 mb-2" color="info" block
        @click="popupVisible = true">
        Kontrol Gir
      </VBtn>

    </VCol>
  </VRow>

  <VCol cols="12" class="pa-0 pt-3">
    <VCard :disabled="isEmriNo.length === 0">
      <VCardItem style="background-color: #20ad5d;" class="d-flex align-center pa-1 ps-4">
        <VCardTitle style=" color: black; font-size: 16px;">Seri Kontroller</VCardTitle>
      </VCardItem>

      <div class="pa-2 d-flex justify-center gap-8">
        <VBtn variant="tonal" icon="tabler-trash" rounded @click="kontrolSil" />
        <VBtn variant="tonal" icon="tabler-photo" rounded @click="fotoGoster" />

      </div>

      <VCardText class="gap-2 pa-2">

        <DxDataGrid id="gridKontroller" ref="dataGridRefKontrol" :data-source="gridDataKontroller" key-expr="id"
          :show-borders="true" width="100%" :focused-row-enabled="true" :row-alternation-enabled="true" height="250"
          :word-wrap-enabled="false" @focused-row-changed="onFocusedRowChangedK">
          <!-- 
          <DxColumn type="buttons" :width="60">
            <DxButton name="delete" />
            <DxButton hint="Clone" icon="image" @click="e => fotoGoster(e.row?.data)" />
          </DxColumn> -->
          <DxColumn data-field="id" caption="ID" width="70" :visible="false" />
          <DxColumn data-field="urun_kontrol_m_id" caption="Master ID" width="70" :visible="false" />
          <DxColumn data-field="kontrol_tarihi" caption="Kontrol Tarihi" data-type="date" :width="130" :format="{
            formatter: (date: string | number | Date) => {
              const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: 'numeric',
                minute: 'numeric',
              }).format(new Date(date));
              return formattedDate.replace(/\//g, '.');
            },
          }" :allow-sorting="false" />
          <DxColumn data-field="seri_no" caption="Seri No" width="170" />
          <DxColumn data-field="is_photo" :header-cell-template="photoHeaderTemplate" width="30"
            cell-template="fotoTemplate" />
          <DxColumn data-field="sonuc" caption="Sonuç" width="70" cell-template="sonucTemplate" />
          <DxColumn data-field="not" caption="Not" width="100" />
          <DxColumn data-field="cc" caption="CC" width="40" cell-template="hataTemplate"
            :header-cell-template="sharedHeaderWithTooltip" :tooltip="'Camda Çizik'" />
          <DxColumn data-field="bke" caption="BKE" width="40" cell-template="hataTemplate"
            :header-cell-template="sharedHeaderWithTooltip" :tooltip="'Bağlantı Kalemi Eksik'" />
          <DxColumn data-field="cm" caption="CM" width="40" cell-template="hataTemplate"
            :header-cell-template="sharedHeaderWithTooltip" :tooltip="'Çapaklı Malzeme'" />
          <DxColumn data-field="kh" caption="KH" width="40" cell-template="hataTemplate"
            :header-cell-template="sharedHeaderWithTooltip" :tooltip="'Kilit Hatası'" />
          <DxColumn data-field="fb" caption="FB" width="40" cell-template="hataTemplate"
            :header-cell-template="sharedHeaderWithTooltip" :tooltip="'Formda Bozukluk'" />
          <DxColumn data-field="sh" caption="SH" width="40" cell-template="hataTemplate"
            :header-cell-template="sharedHeaderWithTooltip" :tooltip="'Serigrafi Hatası'" />
          <DxColumn data-field="kah" caption="KAH" width="47" cell-template="hataTemplate"
            :header-cell-template="sharedHeaderWithTooltip" :tooltip="'Kaynak Hatası'" />
          <DxColumn data-field="bc" caption="BC" width="40" cell-template="hataTemplate"
            :header-cell-template="sharedHeaderWithTooltip" :tooltip="'Boyada Çizik'" />
          <DxColumn data-field="kpl" caption="KPL" width="40" cell-template="hataTemplate"
            :header-cell-template="sharedHeaderWithTooltip" :tooltip="'Kaplama Hatası'" />
          <DxColumn data-field="tkh" caption="TKH" width="47" cell-template="hataTemplate"
            :header-cell-template="sharedHeaderWithTooltip" :tooltip="'Topraklama Hatası'" />
          <DxColumn data-field="bh" caption="BH" width="40" cell-template="hataTemplate"
            :header-cell-template="sharedHeaderWithTooltip" :tooltip="'Boya Hatası'" />
          <DxColumn data-field="ieh" caption="IEH" width="40" cell-template="hataTemplate"
            :header-cell-template="sharedHeaderWithTooltip" :tooltip="'İş Emri Hatası'" />
          <DxColumn data-field="oh" caption="OH" width="40" cell-template="hataTemplate"
            :header-cell-template="sharedHeaderWithTooltip" :tooltip="'Ölçüsel Hata'" />
          <DxColumn data-field="h19" caption="H19" width="40" cell-template="hataTemplate"
            :header-cell-template="sharedHeaderWithTooltip" :tooltip="'19\'\' Ölçü Hatası'" />
          <DxColumn data-field="smh" caption="SMH" width="47" cell-template="hataTemplate"
            :header-cell-template="sharedHeaderWithTooltip" :tooltip="'Saç Malzeme Hatası'" />
          <DxColumn data-field="th" caption="TH" width="40" cell-template="hataTemplate"
            :header-cell-template="sharedHeaderWithTooltip" :tooltip="'Taşeron Hatası'" />
          <DxColumn data-field="ah" caption="AH" width="40" cell-template="hataTemplate"
            :header-cell-template="sharedHeaderWithTooltip" :tooltip="'Aksesuar Hatası'" />
          <DxColumn data-field="mh" caption="MH" width="40" cell-template="hataTemplate"
            :header-cell-template="sharedHeaderWithTooltip" :tooltip="'Montaj Hatası'" />
          <DxColumn data-field="sth" caption="STH" width="47" cell-template="hataTemplate"
            :header-cell-template="sharedHeaderWithTooltip" :tooltip="'Su Testi Hatası'" />
          <DxColumn data-field="bks" caption="BKS" width="40" cell-template="hataTemplate"
            :header-cell-template="sharedHeaderWithTooltip" :tooltip="'Bağlantı Kalemi Sıkılmamış'" />
          <DxColumn data-field="eh" caption="EH" width="40" cell-template="hataTemplate"
            :header-cell-template="sharedHeaderWithTooltip" :tooltip="'Etiket Hatası'" />
          <DxColumn data-field="elk" caption="ELK" width="40" cell-template="hataTemplate"
            :header-cell-template="sharedHeaderWithTooltip" :tooltip="'Elektrik Hatası'" />
          <DxColumn data-field="sph" caption="SPH" width="40" cell-template="hataTemplate"
            :header-cell-template="sharedHeaderWithTooltip" :tooltip="'Sipariş Hatası'" />
          <DxColumn data-field="olusturan_id" caption="Prs ID" width="40" />

          <DxSorting mode="none" />
          <DxScrolling mode="virtual" />

          <template #sonucTemplate="{ data }">
            <template v-if="data.value === 'Onaylandı'">
              <i :class="['dx-icon', 'dx-icon-isnotblank']" :style="{ fontSize: '20px', color: 'green' }"></i>
            </template>
            <template v-else-if="data.value === 'Şartlı Onay'">
              <i :class="['dx-icon', 'dx-icon-isnotblank']" :style="{ fontSize: '20px', color: 'gold' }"></i>
            </template>
            <template v-else-if="data.value === 'Red'">
              <i :class="['dx-icon', 'dx-icon-isnotblank']" :style="{ fontSize: '20px', color: 'red' }"></i>
            </template>
            <template v-else>
              <i :class="['dx-icon', 'dx-icon-isblank']" :style="{ fontSize: '20px', color: 'yellow' }"></i>
            </template>
          </template>

          <template #hataTemplate="{ data }">
            <template v-if="data.value === 'f' || data.value === false">
              <i :class="['dx-icon', '']" :style="{ fontSize: '16px', color: 'green' }"></i>
            </template>
            <template v-else>
              <i :class="['dx-icon', 'dx-icon-isnotblank']" :style="{ fontSize: '16px', color: 'orange' }"></i>
            </template>
          </template>


          <template #fotoTemplate="{ data }">
            <template v-if="data.value === 't' || data.value === true">
              <i @click="fotoGoster(data?.data)" :class="['dx-icon', 'dx-icon-image']"
                :style="{ fontSize: '16px', color: 'green', cursor: 'pointer' }" title="Fotoğrafları Göster"></i>
            </template>
          </template>

        </DxDataGrid>

      </VCardText>

    </VCard>
  </VCol>


  <VRow class="match-height mt-0">

    <VCol cols="12" class="d-flex py-4 gap-4">

      <VCombobox v-model="aciklama" :items="hazirGerekceler" label="Gerekçe" variant="outlined" item-title="tanim"
        item-value="id" :disabled="isEmriNo.length === 0 || secilenSatirlar.length === 0" auto-grow clearable
        rows="1" />
      <VBtn variant="outlined" :disabled="isEmriNo.length === 0 || secilenSatirlar.length === 0" color="warning" :height="37"
        @click="GerekceGir">
        Gerekçe Gir
      </VBtn>
    </VCol>
  </VRow>

  <VCol cols="12" class="pa-0 pt-3">
    <VCard :disabled="isEmriNo.length === 0">
      <VCardItem style="background-color: #20ad5d;" class="d-flex align-center pa-1 ps-4">
        <VCardTitle style=" color: black; font-size: 16px;">Kontrol Edilmeyenler</VCardTitle>
      </VCardItem>

      <VCardText class="gap-2 pa-2">

        <DxDataGrid id="gridKontroller" ref="dataGridRefKontrol" :data-source="serinoListesi" key-expr="id"
          :show-borders="true" width="100%" :focused-row-enabled="true" :row-alternation-enabled="true" height="250"
          :word-wrap-enabled="false" @selection-changed="onSelectionChangedKontrolsuzler">

          <DxColumn data-field="id" caption="ID" width="70" :visible="false" />
          <DxColumn data-field="serino" caption="Seri No" width="150" />

          <DxSorting mode="none" />
          <DxScrolling mode="virtual" />
          <DxSelection mode="multiple" show-check-boxes-mode="always" />

        </DxDataGrid>

      </VCardText>

    </VCard>
  </VCol>

  <DxPopup v-model:visible="popupIsemriSecVisible" :width="'90%'" :height="'90%'" :hide-on-outside-click="false"
    :show-close-button="true" title="İşemri Seçiniz...">
    <div class="text-center">
      <h3>seçilen iş emri: {{ isEmriNo }} </h3>
    </div>

    <DxDataGrid id="gridEmirler" ref="emirlerRef" :key="emirlerKey" :data-source="gridEmirler" key-expr="id"
      :show-borders="true" :focused-row-enabled="true" :row-alternation-enabled="true" :min-width="200" height="95%"
      :allow-column-resizing="true" column-resizing-mode="widget" :column-auto-width="true"
      v-model:focused-row-key="focusedRowKey" @row-dbl-click="onRowDblClick" @selection-changed="onSelectionChanged">

      <DxColumn data-field="id" caption="ID" :visible="false" width="40" cell-template="secimTemplate" />
      <DxColumn data-field="isemri_id" caption=" " data-type="number" :width="30" :visible="false" />
      <DxColumn data-field="tanim" caption="İSTASYON" :min-width="130" />
      <DxColumn data-field="belge_no" caption="BELGE NO" :min-width="130" />
      <DxColumn data-field="cari_ad" caption="CARİ AD" :min-width="130" />
      <DxColumn data-field="isemri_no" caption="İŞ EMRİ NO" :min-width="120" :allow-sorting="false" />
      <DxColumn data-field="item_name" caption="STOK ADI" :allow-sorting="false" />
      <DxColumn data-field="item_code" caption="STOK KODU" :visible="true" :min-width="120" :allow-sorting="false" />
      <DxColumn data-field="istasyon_id" caption="İSTASYON ID" data-type="number" :width="60" :visible="false" />


      <DxLoadPanel v-model:visible="loadingVisible" :show-indicator="true" :show-pane="true" :shading="true" />
      <DxGroupPanel :visible="false" />
      <DxHeaderFilter :visible="false" />
      <DxFilterPanel :visible="false" />
      <DxFilterRow :visible="false" />
      <DxSearchPanel :visible="true" :width="240" />
      <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always" />
      <DxSorting mode="multiple" />
      <DxSelection select-all-mode="page" mode="single" show-check-boxes-mode="always" />

      <DxSummary>
        <DxTotalItem :align-by-column="true" column="TANIM" summary-type="count" display-format="{0} adet" />
      </DxSummary>

      <template #secimTemplate="{ data }">
        <template v-if="data.value === selectedRowKeys">
          <i :class="['dx-icon', 'dx-icon-check']" :style="{ fontSize: '20px', color: 'green' }"></i>
        </template>
        <template v-else>
          <i> </i>
        </template>
      </template>

    </DxDataGrid>

    <DxToolbarItem widget="dxButton" toolbar="bottom" location="center" :options="kaydetOptions" @click="EmirSec" />
    <DxToolbarItem widget="dxButton" toolbar="bottom" location="center" :options="vazgecOptions" />
  </DxPopup>

  <DxPopup v-model:visible="popupUrunAgaciVisible" :width="'90%'" :height="'90%'" :hide-on-outside-click="false"
    :show-close-button="true" :title="'Ürün Ağacı: ' + seciliIsEmri.urunKodu">
    <div class="text-center">
      <h2>{{ isEmriNo }} </h2>
      <h3>Onay Tarihi: {{ agacKontrolTarih }} </h3>
      <h3>Onaylayan: {{ agacKontrolPersonel }} </h3>
    </div>
    <VRow class="d-flex justify-center align-center pt-1 pb-2">
      <VCol cols="6" class="d-flex justify-stasrt ps-5">
        <h4>Onay: &nbsp;&nbsp;<span style="font-size: 20px;">{{ selectedCount || 0 }} / {{ malzemeSayisi }}</span>
        </h4>
      </VCol>
      <VCol cols="6" class="d-flex justify-end pe-5">
        {{ allSelected === true ? items[0].title : items[1].title }}
      </VCol>
    </VRow>
    <DxDataGrid id="gridAgac" ref="agacRef" :data-source="dataAgac" key-expr="user_line_no" :show-borders="true"
      :focused-row-enabled="true" :row-alternation-enabled="true" :min-width="200" :column-auto-width="true"
      :height="'96%'" :allow-column-resizing="true" column-resizing-mode="widget"
      @selection-changed="onSelectionChangedUA">

      <DxColumn data-field="qty_base_bom" caption="ADET" :width="50" data-type=number :format="{
        type: 'fixedPoint',
        precision: 1,
        thousandsSeparator: ',',
      }" />
      <DxColumn data-field="item_id" caption="ID" :width="90" :visible="false" />
      <DxColumn data-field="user_line_no" caption="NO" :width="50" :visible="false" />
      <DxColumn data-field="stok_kodu" caption="STOK KODU" :width="120" />
      <DxColumn data-field="stok_adi" caption="STOK ADI" />

      <DxGroupPanel :visible="false" />
      <DxSorting mode="none" />
      <DxSelection select-all-mode="page" mode="multiple" show-check-boxes-mode="always" :allow-select-all="false" />
      <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always" />
      <DxSummary>
        <DxTotalItem :align-by-column="true" column="stok_kodu" summary-type="count"
          display-format="Satır sayısı: {0}" />
      </DxSummary>

    </DxDataGrid>

    <DxToolbarItem
      :disabled="isSavingAgac"
      widget="dxButton"
      toolbar="bottom"
      location="center"
      :options="{ ...kaydetOptions, text: isSavingAgac ? 'Kaydediliyor...' : 'Kaydet' }"
      @click="AgacKaydet"
    />
    <DxToolbarItem widget="dxButton" toolbar="bottom" location="center" :options="vazgecOptions" />
  </DxPopup>

  <DxPopup :visible="popupVisible" :show-title="true" title="Hata Girişi" :width="'90%'" :height="'90%'"
    :drag-enabled="true" :close-on-outside-click="false" :show-close-button="false" @shown="focusSerino">

    <VRow dense>
      <VCol cols="12" class="d-flex pa-1 pb-0">
        <VTextField ref="serinoRef" v-model="serino" label="Seri No" variant="outlined"
          :rules="[v => !!v || 'Seri no zorunlu']" @keydown.enter.prevent="odaklaSonrakiAlan" @blur="kontrolEtVeOdakla"
          @change="formatSerino" />
      </VCol>
      <VCol cols="12" class="d-flex pa-1 pb-3 justify-end">
        <VTextarea :disabled="serino.length === 0" label="Not" v-model="hataOzet" auto-grow class="mt-4"
          variant="outlined" rows="1" />
      </VCol>
      <VCol cols="6" class="d-flex pa-1 pe-2 pb-4">
        <div>
          <DxSelectBox :disabled="serino.length === 0" :data-source="items" :value="sonuc" display-expr="title"
            value-expr="value" @value-changed="e => sonuc = e.value" :height="37" />
        </div>
      </VCol>
      <VCol cols="6" class="d-flex pa-1 pb-3 justify-end">
        <VBtn :disabled="serino.length === 0" block icon="tabler-camera-plus" variant="outlined" rounded
          @click="openCamera">
        </VBtn>
      </VCol>
    </VRow>


    <hr />

    <!-- Hata Grupları -->
    <VContainer>
      <VRow v-for="(grup, gIndex) in hataGruplari" :key="gIndex">
        <VCol cols="12">
          <strong>{{ grup.baslik }}</strong>
        </VCol>
        <VCol v-for="(hata, hIndex) in grup.hatalar" :key="hIndex" cols="12" sm="6" md="4" class="py-0 my-0">
          <VCheckbox :disabled="serino.length === 0" v-model="seciliHatalar[hata.kod]"
            :label="`${hata.kod}: ${hata.aciklama}`" density="compact" hide-details class="py-0 my-0" />
        </VCol>
      </VRow>
    </VContainer>

    <div class="d-flex align-center gap-4" style="flex: 1;">
      <!-- Gizli file input -->
      <v-file-input ref="fileInput" v-model="selectedPhotos" accept="image/*" label="Fotoğraf Seç" multiple
        style="display: none;" @change="onFilesSelected" />

    </div>
    <VCol cols="12" class="d-flex pa-0 pt-2 pb-2 justify-end gap-2">
      <VBtn :disabled="serino.length === 0" icon="tabler-camera-plus" variant="outlined" block rounded
        @click="openCamera"></VBtn>
      <!-- <VBtn icon="tabler-photo" rounded @click="dosyaYukle"></VBtn> -->
    </VCol>
    <v-row>
      <v-col v-for="photo in photos" :key="photo.id" cols="12" sm="4" md="3">
        <v-img :src="photo.url" max-height="200" class="rounded-lg" @click="previewPhoto(photo.url)" />
        <VBtn icon="tabler-trash" color="error" rounded @click="deletePhoto(photo.id)" class="mt-1"></VBtn>
      </v-col>
    </v-row>

    <DxToolbarItem :disabled="serino.length === 0 || sonuc.length === 0 || isSavingKontrol" widget="dxButton"
      toolbar="bottom" location="center"
      :options="{ ...kaydetOptions, text: isSavingKontrol ? 'Kaydediliyor...' : kaydetOptions.text }"
      @click="KontrolKaydet" />
    <DxToolbarItem widget="dxButton" toolbar="bottom" location="center" :options="vazgecOptions" />
  </DxPopup>

  <v-dialog v-model="previewDialog" max-width="1200">
    <v-card>
      <v-img :src="selectedPhoto" class="ma-4" aspect-ratio="1.5" />
      <v-card-actions>
        <v-spacer />
        <v-btn text @click="previewDialog = false">Kapat</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <VAlert type="error" class="mt-2" border="start" colored-border elevation="2" variant="tonal">

    <strong>CC:</strong> Camda Çizik &nbsp;
    <strong>BKE:</strong> Bağlantı Kalemi Eksik &nbsp;
    <strong>CM:</strong> Çapaklı Malzeme &nbsp;
    <strong>KH:</strong> Kilit Hatası &nbsp;
    <strong>FB:</strong> Formda Bozukluk &nbsp;
    <strong>SH:</strong> Serigrafi Hatası &nbsp;
    <strong>KAH:</strong> Kaynak Hatası &nbsp;
    <strong>BC:</strong> Boyada Çizik &nbsp;
    <strong>KPL:</strong> Kaplama Hatası &nbsp;
    <strong>TKH:</strong> Topraklama Hatası &nbsp;
    <strong>BH:</strong> Boya Hatası &nbsp;
    <strong>IEH:</strong> İş Emri Hatası &nbsp;
    <strong>OH:</strong> Ölçüsel Hata &nbsp;
    <strong>H19:</strong> 19'' Ölçü Hatası &nbsp;
    <strong>SMH:</strong> Saç Malzeme Hatası &nbsp;
    <strong>TH:</strong> Taşeron Hatası &nbsp;
    <strong>AH:</strong> Aksesuar Hatası &nbsp;
    <strong>MH:</strong> Montaj Hatası &nbsp;
    <strong>STH:</strong> Su Testi Hatası &nbsp;
    <strong>BKS:</strong> Bağlantı Kalemi Sıkılmamış &nbsp;
    <strong>EH:</strong> Etiket Hatası &nbsp;
    <strong>ELK:</strong> Elektrik Hatası &nbsp;
    <strong>SPH:</strong> Sipariş Hatası &nbsp;

  </VAlert>

  <VDialog v-model="fotoDialog" max-width="800">
    <VCard>
      <VCardTitle class="d-flex justify-space-between align-center">
        <span>Seri No: {{ seciliKayit?.seri_no || '' }}</span>
        <VBtn variant="tonal" icon="tabler-square-letter-x" rounded @click="fotoDialog = false" />
      </VCardTitle>
      <VCardText>
        <VRow>
          <VCol v-for="(foto, index) in seciliKayitFotolar" :key="index" cols="12" md="4">
            <VImg :src="foto" aspect-ratio="4/3" cover class="mb-2" />
            <div class="d-flex justify-space-between">
              <VBtn variant="tonal" icon="tabler-trash" rounded @click="confirmDelete(foto)" />
              <VBtn variant="tonal" icon="tabler-eye" rounded @click="showFullscreen(foto)" />
            </div>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
  </VDialog>

  <!-- Tam ekran VDialog -->
  <VDialog v-model="fullscreenDialog" fullscreen>
    <VCard>
      <VCardTitle class="d-flex justify-space-between align-center">
        <VSpacer />
        <VBtn variant="tonal" icon="tabler-square-letter-x" rounded @click="fullscreenDialog = false" />

      </VCardTitle>
      <div class="pa-2 d-flex justify-center gap-2">

        <VBtn variant="tonal" icon="tabler-zoom-in" rounded @click="zoomIn" />
        <VBtn variant="tonal" icon="tabler-zoom-out" rounded @click="zoomOut" />
        <VBtn variant="tonal" icon="tabler-zoom-reset" rounded @click="resetZoom" />

      </div>
      <VCardText>



        <!-- Resim -->
        <div style="overflow: auto; max-block-size: 80vh;" @wheel="onWheel" @mousedown="startPan" @mousemove="onPan"
          @mouseup="endPan" @mouseleave="endPan">

          <VImg :src="fullscreenFoto" class="w-100 h-auto" :style="{
            transform: `scale(${zoom}) translate(${translate.x}px, ${translate.y}px)`,
            cursor: isPanning ? 'grabbing' : 'grab',
            transition: isPanning ? 'none' : 'transform 0.2s ease',
            'max-width': 'unset',
            'max-height': 'unset',
            'user-select': 'none',
            'pointer-events': 'none', // Mouse olayları pan kutusundan alınır
          }" alt="Zoomable" draggable="false">

          </VImg>
        </div>
      </VCardText>
    </VCard>
  </VDialog>

  <HataBildirimiDialog ref="hataDialogRef" />


</template>

<script setup lang="ts">

import axios from "axios";
import {
  DxColumn,
  DxDataGrid,
  DxFilterPanel,
  DxFilterRow,
  DxGroupPanel,
  DxHeaderFilter,
  DxScrolling,
  DxSearchPanel,
  DxSelection,
  DxSorting,
  DxSummary,
  DxTotalItem
} from "devextreme-vue/data-grid";
import { DxLoadPanel } from 'devextreme-vue/load-panel';
import { DxPopup, DxToolbarItem } from 'devextreme-vue/popup';
import DxSelectBox from 'devextreme-vue/select-box';
import ArrayStore from 'devextreme/data/array_store';
import notify from "devextreme/ui/notify";
import Swal from "sweetalert2";
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { VBtn } from "vuetify/components";
import HataBildirimiDialog from './HataBildirimiDialog.vue';

const hataDialogRef = ref()

const openHataDialog = () => {
  hataDialogRef.value.dialog = true
}

const popupIsemriSecVisible = ref(false);
const popupUrunAgaciVisible = ref(false);
const isSavingAgac = ref(false)
const userData = useCookie<any>("userData");
const loadingVisible = ref<boolean>(false);
const gridEmirler = ref<any[]>([]);
const dataAgac = ref<any[]>([]);
const emirlerKey = ref(0);
const focusedRowKey = ref(null);
const isEmriID = ref(0);
interface KontrolGridRow { id?: number; seri_no: string; is_photo?: number | boolean;[key: string]: any }
const gridDataKontroller = ref<KontrolGridRow[]>([]);
const emirlerRef = ref<DxDataGrid | null>(null);
const agacRef = ref<DxDataGrid | null>(null);
// Ana "Seri Kontroller" grid'i için ref (foto silme sonrası refresh vs.)
const dataGridRefKontrol = ref<DxDataGrid | null>(null);
const selectedRowKeys = ref(0);
const isEmriNo = ref("");
const isEmriMiktari = ref(0);
const popupSeriKontrolVisible = ref(false)
const popupVisible = ref(false)
const serino = ref('')
const sonuc = ref('')
const secilenSonuc = ref('')
const hataOzet = ref('')
// Kayıt butonuna ardışık tıklamaları engellemek için
const isSavingKontrol = ref(false)
const seciliHatalar = ref<Record<string, boolean>>({})
const serinoRef = ref()
const gridSurec = ref<GridRow[]>([]);

const allSelected = ref(false)
const selectedCount = ref(0)
const malzemeSayisi = ref(0)
// removed unused resimler/preview/selectedOption
const fileInput = ref()
const previewDialog = ref(false)
const selectedPhoto = ref('')
const photo = ref<File | null>(null)
const photos = ref<PhotoItem[]>([])
let photoIdCounter = 1
const selectedPhotos = ref([])
const zoom = ref(1)
const agacKontrolTarih = ref('...')
const agacKontrolPersonel = ref('...')


type Kayit = {
  id: number
  serino: string
}

const serinoListesi = ref<Kayit[]>([])
const secilenSatirlar = ref<Kayit[]>([])
const aciklama = ref('')
const fullscreenDialog = ref(false)
const fullscreenFoto = ref('')
const fotoDialog = ref(false)
const seciliKayit = ref<any>(null)
const seciliKayitFotolar = ref<string[]>([])
const photoID = ref(0)
const photoSeriNo = ref('')
const hazirGerekceler = ref([])

// const kontrolAlanlari = [
//   'ÇM', 'KH', 'FB', 'OK', 'ET', 'DK', 'XY1', 'XY2', 'XY3', 'XY4', 'XY5',
//   'XY6', 'XY7', 'XY8', 'XY9', 'XY10', 'XY11', 'XY12', 'XY13', 'XY14',
//   'XY15', 'XY16', 'XY17'
// ] // toplam 23 kontrol alanı

interface GridRow {
  id?: number;
  revizyon?: string | number;
  kontrol_tarihi?: string | Date;
  [key: string]: any;
}

const items = [
  { title: '✔️ Onaylandı', value: 'Onaylandı', color: '#d4edda' },
  { title: '❌ Red', value: 'Red', color: '#f8d7da' },
  { title: '⚠️ Şartlı Onay', value: 'Şartlı Onay', color: '#fff3cd' }
]

const data = new ArrayStore({ data: items, key: 'value' });

const formatNumber = (number: number): string => new Intl.NumberFormat('tr-TR', { maximumFractionDigits: 0 }).format(number)

const kaydetOptions = {
  width: 100,
  type: 'success',
  text: 'Tamam',
  stylingMode: 'contained',
}

const vazgecOptions = {
  width: 100,
  type: 'normal',
  text: 'Vazgeç',
  stylingMode: 'contained',
  onClick: () => {
    popupIsemriSecVisible.value = false
    popupUrunAgaciVisible.value = false
    popupVisible.value = false
  },
}

const seciliIsEmri = ref({
  istasyon: '...',
  siparisNo: '...',
  isEmriId: 0,
  isEmriNo: '...',
  urunID: 0,
  urunAdi: '...',
  urunKodu: '...',
  musteri: '...',
  calSure: 0,
  durSure: 0,
  personelSayisi: 0,
  sure: 0,
  durum: '',
  sebep: '',
  calOran: 0,
  durOran: 0,
  istasyonID: 0,
  personelID: 0,
  planlamaNotu: '',
  UretimNotu: '',
  aksesuar: false,
  planTarih: '...',
  isEmriMiktari: 0,
})

const kontroller = ref({
  masterId: 0,
  isEmriId: 0,
  istasyonID: 0,
  personelID: 0,
  inspectorID: 0,
  urunAgaciText: '',
  urunAgaciCheck: '',
  gorunumText: '',
  gorunumCheck: '',
  olcuselText: '',
  olcuselCheck: '',
  paketlemeText: '',
  paketlemeCheck: '',
  etiketText: '',
  etiketCheck: '',
  dokumanText: '',
  dokumanCheck: '',
})

const EmirSec = async () => {
  if (isEmriID.value === 0) {
    notify({
      message: 'Lütfen bir iş emri seçiniz.',
      type: 'error',
      displayTime: 3000,
    });
    return
  }
  gridDataKontroller.value = []
  gridSurec.value = []
  try {
    const response = await axios.get("/api/isEmri", {
      params: {
        deger: isEmriID.value,
      },
    });
    seciliIsEmri.value.isEmriId = response.data.data[0].isemri_id;
    seciliIsEmri.value.istasyonID = response.data.data[0].IS_ISTASYONU_ID;
    seciliIsEmri.value.personelID = userData.value.id;
    seciliIsEmri.value.istasyon = response.data.data[0].IS_ISTASYONU;
    seciliIsEmri.value.isEmriNo = response.data.data[0].isemri_no;
    seciliIsEmri.value.urunAdi = response.data.data[0].stok_adi;
    seciliIsEmri.value.urunKodu = response.data.data[0].stok_kodu;
    seciliIsEmri.value.musteri = response.data.data[0].cari_ad;
    seciliIsEmri.value.siparisNo = response.data.data[0].siparis_belge_no;
    seciliIsEmri.value.isEmriMiktari = response.data.data[0].isemri_miktari;
    seciliIsEmri.value.planTarih = response.data.data[0].plan_tarih_araligi;
    seciliIsEmri.value.aksesuar = response.data.data[0].aksesuar === 'Aksesuarlı' ? true : false;
    isEmriMiktari.value = response.data.data[0].isemri_miktari;
    KontrolleriAl()
    KontrolAktifKaydet('aktif')
  } catch (error) {
    console.error("Master verileri alınırken hata oluştu:", error);
  }

  kontroller.value.isEmriId = seciliIsEmri.value.isEmriId
  kontroller.value.istasyonID = seciliIsEmri.value.istasyonID
  kontroller.value.personelID = seciliIsEmri.value.personelID
  popupIsemriSecVisible.value = false;

  serinoListesi.value = Array.from({ length: seciliIsEmri.value.isEmriMiktari }, (_, i) => {
    const id = i + 1
    const serino = `${seciliIsEmri.value.isEmriNo}-${String(id).padStart(5, '0')}`
    return { id, serino }
  })

}

// *************** watch ************************************************************* //

watch(popupIsemriSecVisible, (yeniDeger, eskiDeger) => {
  if (eskiDeger === false && yeniDeger === true) {
    EmirleriAl()
  }
})

watch(popupUrunAgaciVisible, (yeniDeger, eskiDeger) => {
  if (eskiDeger === false && yeniDeger === true) {
    dataAgac.value = []
    MalzemeleriAl()
    AgacYukle()
  }
  if (eskiDeger === true && yeniDeger === false) {
    agacKontrolPersonel.value = '...'
    agacKontrolTarih.value = '...'
  }
})

watch(popupSeriKontrolVisible, (yeniDeger, eskiDeger) => {
  if (eskiDeger === true && yeniDeger === false) {
    KontrolleriAl()
  }
})

watch(fullscreenDialog, (yeniDeger, eskiDeger) => {
  if (eskiDeger === true && yeniDeger === false) {
    resetZoom()
  }
})

watch(popupVisible, (yeniDeger, eskiDeger) => {
  if (eskiDeger === false && yeniDeger === true) {
    isSavingKontrol.value = false // açıldığında resetle
    focusSerino()
  }
  if (eskiDeger === true && yeniDeger === false) {
    temizle()
    KontrolleriAl()
  }
})

watch(fotoDialog, (yeniDeger, eskiDeger) => {
  if (eskiDeger === true && yeniDeger === false) {
    seciliKayitFotolar.value = ['']
  }
})

watch(photo, newPhoto => {
  if (newPhoto) {
    const reader = new FileReader()
    reader.onload = () => {
      if (typeof reader.result === 'string') {
        photos.value.push({
          id: photoIdCounter++,
          url: reader.result
        })
      }
    }
    reader.readAsDataURL(newPhoto)
  }
})

watch([isEmriNo, isEmriMiktari], ([yeniIsemri, yeniMiktar]) => {
  if (!yeniIsemri || !yeniMiktar) return;

  const liste = [];
  for (let i = 1; i <= Number(yeniMiktar); i++) {
    const serino = `${yeniIsemri}-${String(i).padStart(5, '0')}`;
    liste.push({ id: i, serino });
  }
  serinoListesi.value = liste;
});

// *************** Veri Tabani ******************************************************* //

const EmirleriAl = async () => {
  try {
    loadingVisible.value = true;
    const response = await axios.get("/api/aktiflerial", {
      params: {
        istasyonId: userData.value.istasyon_id,
      },
    });
    gridEmirler.value = response.data;
  } catch (error) {
    console.error("Veri çekilirken hata oluştu: ", error);
  } finally {
    loadingVisible.value = false;
  }
};

const KontrolleriAl = async () => {
  if (!seciliIsEmri.value.isEmriId || !seciliIsEmri.value.istasyonID) {
    console.warn('İş Emri veya İstasyon ID eksik')
    return
  }

  try {
    const response = await axios.get('/api/kontrolleri-al', {
      params: {
        istasyon: seciliIsEmri.value.istasyonID,
        isEmriId: seciliIsEmri.value.isEmriId,
        isEmriNo: seciliIsEmri.value.isEmriNo,
      },
    })

    const veri = response.data.kontroller

    gridSurec.value = response.data.revizyonlar || []
    gridDataKontroller.value = response.data.kontrollerd || []
    if (!veri) {
      console.warn('Kontrol kaydı bulunamadı')
      kontroller.value = {
        masterId: 0,
        isEmriId: seciliIsEmri.value.isEmriId,
        istasyonID: seciliIsEmri.value.istasyonID,
        personelID: seciliIsEmri.value.personelID,
        inspectorID: 0,
        urunAgaciText: '',
        urunAgaciCheck: '',
        gorunumText: '',
        gorunumCheck: '',
        olcuselText: '',
        olcuselCheck: '',
        paketlemeText: '',
        paketlemeCheck: '',
        etiketText: '',
        etiketCheck: '',
        dokumanText: '',
        dokumanCheck: '',
      }
      return
    }

    kontroller.value.masterId = veri.id
    kontroller.value.urunAgaciText = veri.urun_agaci_not
    kontroller.value.urunAgaciCheck = veri.urun_agaci_ok
    kontroller.value.gorunumText = veri.gorunum_not
    kontroller.value.gorunumCheck = veri.gorunum_ok
    kontroller.value.olcuselText = veri.olcusel_not
    kontroller.value.olcuselCheck = veri.olcusel_ok
    kontroller.value.paketlemeText = veri.paketleme_not
    kontroller.value.paketlemeCheck = veri.paketleme_ok
    kontroller.value.etiketText = veri.etiket_not
    kontroller.value.etiketCheck = veri.etiket_ok
    kontroller.value.dokumanText = veri.dokuman_not
    kontroller.value.dokumanCheck = veri.dokuman_ok

    KontrolEdilmeyenleriAl()

  } catch (error) {
    console.error('Kayıt çekilirken hata oluştu:', error)
  }
}

const MalzemeleriAl = async () => {
  try {
    const response = await axios.get('/api/isEmriDetay', {
      params: {
        tablo: 'DETAY',
        depo: 0,
        isemri_id: isEmriID.value,
      },
    })
    console.log('Malzemeler alındı:', response.data.malzemeler)
    dataAgac.value = response.data.malzemeler
    malzemeSayisi.value = response.data.toplamMalzeme
    console.log('Malzemeler alındı:', dataAgac.value)
  }
  catch (error) {
    console.error('Veri çekilirken hata oluştu: ', error)
  }
}

const kaydetKontroller = async () => {

  if (kontroller.value.urunAgaciText === '' && kontroller.value.gorunumText === '' && kontroller.value.olcuselText === '' &&
    kontroller.value.paketlemeText === '' && kontroller.value.etiketText === '' && kontroller.value.dokumanText === '' &&
    kontroller.value.gorunumCheck === '' && kontroller.value.olcuselCheck === '' && kontroller.value.paketlemeCheck === '' &&
    kontroller.value.etiketCheck === '' && kontroller.value.dokumanCheck === '' && kontroller.value.urunAgaciCheck === '') {
    notify({
      message: 'Lütfen en az bir kontrol alanını doldurun.',
      type: "error",
      displayTime: 2000,
    });
    return
  }

  try {

    const kayitResponse = await axios.post('/api/kontrol-kaydet', {
      ...kontroller.value,
      user_id: userData.value.id,
    })

    const mID = kayitResponse.data.mID // <-- BURADA mID'yi alıyoruz
    kontroller.value.masterId = mID // Kontrol verilerini güncelle

    notify({
      message: 'Kayıt başarılı...',
      type: "success",
      displayTime: 2000,
    });

    KontrolleriAl() // Kayıt sonrası kontrolleri tekrar al
  } catch (err) {
    console.error(err)
    alert('Hata oluştu.')
  }
}

const AgacKaydet = async () => {
  if (isSavingAgac.value) return
  try {
    isSavingAgac.value = true
    const selectedRowKeys = agacRef.value?.instance ? agacRef.value.instance.getSelectedRowKeys() : []
    await axios.post('/api/urun-agaci-secim/kaydet', {
      urunKodu: seciliIsEmri.value.urunKodu,
      selected_ids: selectedRowKeys,
      userId: userData.value.id,
    })
    notify({ message: 'Ürün ağacı seçimleri kaydedildi.', type: 'success', displayTime: 1500 })
    popupUrunAgaciVisible.value = false
  } catch (e) {
    console.error('AgacKaydet hata', e)
    notify({ message: 'Kayıt başarısız. Lütfen tekrar deneyin.', type: 'error', displayTime: 2000 })
  } finally {
    isSavingAgac.value = false
  }
}

const AgacYukle = async () => {
  console.log(seciliIsEmri.value.urunKodu)
  const response = await axios.get('/api/urun-agaci-secim/yukle', {
    params: { urunKodu: seciliIsEmri.value.urunKodu },
  })

  const oncekiSecimler = response.data.selected_ids
  const detay = response.data.data || {}

  if (detay.created_at) {
    const tarih = new Date(detay.created_at)

    const gun = String(tarih.getDate()).padStart(2, '0')
    const ay = String(tarih.getMonth() + 1).padStart(2, '0')
    const yil = tarih.getFullYear()

    const saat = String(tarih.getHours()).padStart(2, '0')
    const dakika = String(tarih.getMinutes()).padStart(2, '0')

    agacKontrolTarih.value = `${gun}.${ay}.${yil} ${saat}:${dakika}`
  } else {
    agacKontrolTarih.value = '...'
  }
  agacKontrolPersonel.value = detay.personel_adi || '...'

  if (agacRef.value && agacRef.value.instance) {
    agacRef.value.instance.selectRows(oncekiSecimler, true)
  }
}

const KontrolAktifAl = async () => {
  const response = await axios.get('/api/kontrolAktifAl', {
    params: { userID: userData.value.id },
  })

  isEmriID.value = response.data.aktif?.isemri_id ?? 0
  isEmriNo.value = response.data.aktif?.isemri_no ?? ''
  selectedRowKeys.value = response.data.aktif?.isemri_id ?? 0

  EmirSec()
}

const KontrolAktifKaydet = async (durum: string) => {

  await axios.post('/api/kontrolAktifKaydet', {
    durum: durum || 'aktif',
    isEmriID: seciliIsEmri.value.isEmriId,
    istasyonID: seciliIsEmri.value.istasyonID,
    userId: userData.value.id,
  })
};

const GerekceGir = async () => {
  if (!aciklama.value || secilenSatirlar.value.length === 0) {
    notify({
      message: 'Lütfen açıklama giriniz ve en az bir seri no seçiniz.',
      type: "error",
      displayTime: 2000,
    });
    return;
  }

  const kontrolKayitlari = secilenSatirlar.value.map(satir => ({
    serino: satir.serino,
    sonuc: 'Kontrol Edilmedi',
    aciklama: aciklama.value,
    urun_kontrol_m_id: kontroller.value.masterId,
    isemri_no: isEmriNo.value,
    user_id: userData.value.id,
  }));

  await axios.post('/api/kontroller/toplu-kaydet', kontrolKayitlari)

  // serinoListesi'nden çıkar
  const secilenSeriNolar = new Set(secilenSatirlar.value.map(s => s.serino));
  serinoListesi.value = serinoListesi.value.filter(s => !secilenSeriNolar.has(s.serino));

  // Temizle
  secilenSatirlar.value = [];
  aciklama.value = '';
  KontrolleriAl();
};

// *************** Olaylar *********************************************************** //

const onFocusedRowChangedK = (e: any) => {
  photoID.value = e.row?.data?.id || 0;
  photoSeriNo.value = e.row?.data?.seri_no || '';
};

const onSelectionChanged = (e: any) => {
  if (e.selectedRowsData.length > 0) {
    isEmriID.value = e.selectedRowsData[0].isemri_id;
    selectedRowKeys.value = e.selectedRowsData[0].id;
    isEmriNo.value = e.selectedRowsData[0].isemri_no;
  }
  else isEmriID.value = 0
};

onMounted(async () => {
  document.title = "OFT - Kalite Kontrol";
  KontrolAktifAl();
  const [gerekceler] = await Promise.all([
    axios.get('/api/gerekceler'),
  ])
  hazirGerekceler.value = gerekceler.data

});

const onRowDblClick = (e: any) => {
  EmirSec();
};

function onSelectionChangedUA(e: any) {
  selectedCount.value = e.selectedRowKeys.length
  allSelected.value = selectedCount.value === malzemeSayisi.value
  kontroller.value.urunAgaciCheck = allSelected.value ? 'Onaylandı' : 'Red'
}

const onSelectionChangedKontrolsuzler = (e: any) => {
  secilenSatirlar.value = e.selectedRowsData
}

// *************** Kontrol Gir ******************************************************* //


const openCamera = () => {
  fileInput.value?.$el?.querySelector('input[type=file]')?.click()
}

interface PhotoItem {
  id: number
  url: string
  file?: File
}

const hataGruplari = [
  {
    baslik: 'İş Emri Hataları', hatalar: [
      { kod: 'IEH', aciklama: 'İş Emri Hatası' },
      { kod: 'SPH', aciklama: 'Sipariş Hatası' },
      { kod: 'TH', aciklama: 'Taşeron Hatası' },
      { kod: 'EH', aciklama: 'Etiket Hatası' },
    ],
  },
  {
    baslik: 'Malzeme Hataları', hatalar: [
      { kod: 'CM', aciklama: 'Çapaklı Malzeme' },
      { kod: 'SMH', aciklama: 'Saç Malzeme Hatası' },
      { kod: 'KH', aciklama: 'Kilit Hatası' },
      { kod: 'FB', aciklama: 'Formda Bozukluk' },
      { kod: 'CC', aciklama: 'Camda Çizik' },
      { kod: 'OH', aciklama: 'Ölçüsel Hata' },
      { kod: "19''H", aciklama: "19'' Ölçü Hatası" },
      { kod: 'AH', aciklama: 'Aksesuar Hatası' },
      { kod: 'KPL', aciklama: 'Kaplama Hatası' },
    ],
  },
  {
    baslik: 'Montaj Hataları', hatalar: [
      { kod: 'MH', aciklama: 'Montaj Hatası' },
      { kod: 'TKH', aciklama: 'Topraklama Hatası' },
      { kod: 'KAH', aciklama: 'Kaynak Hatası' },
      { kod: 'BKE', aciklama: 'Bağlantı Kalemi Eksik' },
      { kod: 'BKS', aciklama: 'Bağlantı Kalemi Sıkılmamış' },
      { kod: 'ELK', aciklama: 'Elektrik Hatası' },
      { kod: 'STH', aciklama: 'Su Testi Hatası' },
    ],
  },
  {
    baslik: 'Yüzey / Boya Hataları', hatalar: [
      { kod: 'BC', aciklama: 'Boyada Çizik' },
      { kod: 'BH', aciklama: 'Boya Hatası' },
      { kod: 'SH', aciklama: 'Serigrafi Hatası' },
    ],
  },
]

const sharedHeaderWithTooltip = (container: HTMLElement, options: any) => {
  const span = document.createElement('span');
  span.textContent = options.column.caption;

  // Eğer column'da tooltip tanımı varsa kullan
  const tooltipText = options.column.tooltip || options.column.caption;
  span.setAttribute('title', tooltipText);

  container.appendChild(span);
};

const odaklaSonrakiAlan = () => {
  // formda serino'dan sonra gelen checkboxlara ya da bir sonraki mantıklı alana focus atamak için
  // örnek olarak ilk checkboxa odaklanalım
  nextTick(() => {
    const el = document.querySelector('input[type="checkbox"]') as HTMLElement
    el?.focus()
  })
}

const temizle = () => {
  serino.value = ''
  sonuc.value = ''
  hataOzet.value = ''
  seciliHatalar.value = {}
  photos.value = []
  selectedPhotos.value = []
}

const bosalt = () => {
  isEmriID.value = 0;
  isEmriNo.value = '';
  selectedRowKeys.value = 0;
  seciliIsEmri.value = {
    istasyon: '...',
    siparisNo: '...',
    isEmriId: 0,
    isEmriNo: '...',
    urunID: 0,
    urunAdi: '...',
    urunKodu: '...',
    musteri: '...',
    calSure: 0,
    durSure: 0,
    personelSayisi: 0,
    sure: 0,
    durum: '',
    sebep: '',
    calOran: 0,
    durOran: 0,
    istasyonID: seciliIsEmri.value.istasyonID,
    personelID: seciliIsEmri.value.personelID,
    planlamaNotu: '',
    UretimNotu: '',
    aksesuar: false,
    planTarih: '...',
    isEmriMiktari: 0,
  };
  kontroller.value = {
    masterId: 0,
    isEmriId: 0,
    istasyonID: 0,
    personelID: 0,
    inspectorID: 0,
    urunAgaciText: '',
    urunAgaciCheck: '',
    gorunumText: '',
    gorunumCheck: '',
    olcuselText: '',
    olcuselCheck: '',
    paketlemeText: '',
    paketlemeCheck: '',
    etiketText: '',
    etiketCheck: '',
    dokumanText: '',
    dokumanCheck: '',
  };
  gridDataKontroller.value = [];
  gridSurec.value = [];
  serinoListesi.value = [];
}

const focusSerino = () => {
  nextTick(() => {
    serinoRef.value?.focus()
  })
}

const KontrolKaydet = async () => {
  if (isSavingKontrol.value) {
    notify({
      message: 'Kayıt işlemi devam ediyor...',
      type: 'warning',
      displayTime: 1500,
    })
    return
  }
  isSavingKontrol.value = true
  const base64Resimler: Array<{ base64: string; extension: string }> = []

  for (const p of photos.value) {
    if (p.file) {
      const base64 = await fileToBase64(p.file)
      const extension = p.file.name.split('.').pop() || 'jpg'

      base64Resimler.push({
        base64: base64.replace(/^data:image\/\w+;base64,/, ''), // sadece base64 içeriği
        extension,
      })
    }
  }

  const payload = {
    urun_kontrol_m_id: kontroller.value.masterId,
    isemri_no: isEmriNo.value,
    serino: serino.value,
    hatalar: Object.keys(seciliHatalar.value).filter(k => seciliHatalar.value[k]),
    sonuc: sonuc.value,
    resimler: base64Resimler,
    hataOzet: hataOzet.value,
    user_id: userData.value.id,
    isPhoto: photos.value.length > 0 ? 1 : 0, // Fotoğraf var mı?
  }

  try {
    const response = await axios.post('/api/hata-kaydet', payload)
    if (response.data.success) {
      notify({
        message: 'Kayıt başarılı!',
        type: "success",
        displayTime: 2000,
      })
      popupVisible.value = false
    } else {
      alert('Kayıt başarısız!')
    }
  } catch (err) {
    console.error('Hata:', err)
    alert('Kayıt sırasında bir hata oluştu.')
  } finally {
    isSavingKontrol.value = false
  }
}

const fileToBase64 = (file: File): Promise<string> => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()
    reader.readAsDataURL(file)
    reader.onload = () => resolve(reader.result as string)
    reader.onerror = error => reject(error)
  })
}

const dosyaYukle = () => {
  fileInput.value?.$el?.querySelector('input[type=file]')?.click()
}

const deletePhoto = (id: number) => {
  photos.value = photos.value.filter(p => p.id !== id)
}

const previewPhoto = (url: string) => {
  selectedPhoto.value = url
  previewDialog.value = true
}

const onFilesSelected = () => {
  if (!selectedPhotos.value || selectedPhotos.value.length === 0) return

  for (const file of selectedPhotos.value) {
    const reader = new FileReader()
    reader.onload = (e) => {
      const url = e.target?.result
      if (typeof url === 'string') {
        photos.value.push({
          id: photoIdCounter++,
          url: URL.createObjectURL(file),
          file,
        })
      }
    }
    reader.readAsDataURL(file)
  }

  // Aynı dosyalar tekrar seçilebilsin diye sıfırla
  selectedPhotos.value = []
}

const KontrolEdilmeyenleriAl = () => {
  const kullanilmislar = new Set(gridDataKontroller.value.map(x => x.seri_no));
  const kalanlar = serinoListesi.value.filter(s => !kullanilmislar.has(s.serino));
  serinoListesi.value = kalanlar;
};

const kontrolEtVeOdakla = () => {
  if (!serino.value) {
    // Bir sonraki "tick"te tekrar focus verelim
    nextTick(() => {
      serinoRef.value.focus()
    })
  }
}

const formatSerino = () => {
  if (serino.value.length === 18) {

    return
  }
  const sadeceSayi = serino.value.replace(/\D/g, '') // Sayı olmayan karakterleri çıkar

  if (parseInt(sadeceSayi) > 0 && parseInt(sadeceSayi) <= seciliIsEmri.value.isEmriMiktari) {
    const padded = sadeceSayi.padStart(5, '0')
    serino.value = `${isEmriNo.value}-${padded}`
  }
  // else if (sadeceSayi.length > 5) {
  //   const padded = sadeceSayi.slice(-5) // Son 5 karakteri al
  //   serino.value = `${isEmriNo.value}-${padded}`
  // }
  else {
    serino.value = ''
    notify({
      message: 'Seri no formatı hatalı. Lütfen 5 haneyi geçmeyen ve sipariş miktarından büyük olmayan bir seri no giriniz.',
      type: "error",
      displayTime: 5000,
    })
  }
}

const KontrolKapat = () => {
  if (isEmriID.value === 0) {
    notify({
      message: 'Seçili iş emri yok.',
      type: "error",
      displayTime: 3000,
    });
    return
  }

  const swalWithBootstrapButtons = Swal.mixin({
    customClass: {
      confirmButton: "btn btn-primary",
      cancelButton: "btn btn-error",
    },
    buttonsStyling: true,
  });

  swalWithBootstrapButtons
    .fire({
      title: "Emin misiniz?",
      text: "Kontrol sonlandırılacak!!!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#88efb3",
      cancelButtonColor: "#ed9595",
      reverseButtons: true,
      confirmButtonText: "Evet, sonlandıralım!",
      cancelButtonText: "Hayır, vazcgeçtim!",
    })
    .then(async (result: any) => {
      if (result.isConfirmed) {

        await nextTick(); // Vue reaktif güncellemeleri tamamlansın
        temizle();
        KontrolAktifKaydet('pasif');
        bosalt();
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        // Cancelled
      }
    });
};

const fotoGoster = (rowData?: any) => {
  // Satırdan seri no geldiyse onu kullan; gelmediyse focused satırın seri no'su
  const seriNo = rowData?.seri_no || photoSeriNo.value
  if (!seriNo) {
    notify({ message: 'Seri no bulunamadı.', type: 'error', displayTime: 2000 })
    return
  }
  console.log('Fotoğraflar için seri no:', seriNo)
  photoSeriNo.value = seriNo
  fotoDialog.value = true
  axios.get('/api/hata-goster-resim', {
    params: { isEmriNo: seciliIsEmri.value.isEmriNo, serino: seriNo },
  }).then(res => {
    seciliKayitFotolar.value = Array.isArray(res.data) ? res.data.map((f: { url: string }) => f.url) : []
    if (seciliKayitFotolar.value.length === 0) {
      notify({ message: 'Fotoğraf bulunamadı.', type: 'warning', displayTime: 2000 })
    }
  }).catch(() => {
    seciliKayitFotolar.value = []
    notify({ message: 'Fotoğraflar alınamadı.', type: 'error', displayTime: 2000 })
  })
}

const kontrolSil = async () => {
  if (photoID.value === 0) {
    notify({
      message: 'Lütfen bir kontrol seçiniz.',
      type: "error",
      displayTime: 3000,
    });
    return
  }

  const confirm = window.confirm('Bu kaydı silmek istediğinize emin misiniz?')
  if (!confirm) return

  try {
    const id = photoID.value || 0
    const response = await axios.delete(`/api/kontrol-sil/${id}`)
    // alert(response.data.message)
    KontrolleriAl();
  } catch (error) {
    alert('Silme işlemi başarısız.')
    console.error(error)
  }
}

const confirmDelete = async (fotoUrl: any) => {
  const result = confirm('Bu fotoğrafı silmek istediğinizden emin misiniz?')
  if (!result) return
  try {
    const resp = await axios.delete('/api/hata-sil-resim', {
      params: {
        url: fotoUrl,
        isEmriNo: seciliIsEmri.value.isEmriNo,
      },
    })
    seciliKayitFotolar.value = seciliKayitFotolar.value.filter(f => f !== fotoUrl)
    // Backend remaining=0 ise ilgili seri_no satırında is_photo=0 yap
    const remaining = resp.data?.remaining
    const seriNo = resp.data?.seri_no
    if (remaining === 0 && seriNo) {
      const row = gridDataKontroller.value.find(r => r.seri_no.startsWith(seriNo))
      if (row) {
        row.is_photo = 0
        // DevExtreme grid yenile (ref varsa)
        if (dataGridRefKontrol?.value?.instance) {
          dataGridRefKontrol.value.instance.refresh();
        }
      }
    }
  } catch (err) {
    console.error('Silme hatası:', err)
    alert('Silinirken hata oluştu.')
  }
}

const showFullscreen = (fotoUrl: any) => {
  fullscreenFoto.value = fotoUrl
  fullscreenDialog.value = true
}

const zoomIn = () => zoom.value += 0.1
const zoomOut = () => zoom.value = Math.max(0.2, zoom.value - 0.1)
const resetZoom = () => {
  zoom.value = 1
  translate.value = { x: 0, y: 0 }
}

// Pan verileri
const isPanning = ref(false)
const startPos = ref({ x: 0, y: 0 })
const translate = ref({ x: 0, y: 0 })

const startPan = (e: MouseEvent) => {
  isPanning.value = true
  startPos.value = { x: e.clientX, y: e.clientY }
}

const onPan = (e: MouseEvent) => {
  if (!isPanning.value) return

  const dx = e.clientX - startPos.value.x
  const dy = e.clientY - startPos.value.y

  translate.value.x += dx
  translate.value.y += dy

  startPos.value = { x: e.clientX, y: e.clientY }
}

const endPan = () => {
  isPanning.value = false
}

const onWheel = (event: WheelEvent) => {
  event.preventDefault()
  event.deltaY < 0 ? zoomIn() : zoomOut()
}

const pdfGoster = () => {
  const url = '/api/pdf-goster'

  // Yeni sekmede (tab) aç
  window.open(url, '_blank')
}


const photoHeaderTemplate = (header: HTMLElement) => {
  const container = document.createElement('div')
  const icon = document.createElement('i')

  icon.className = 'dx-icon dx-icon-image'
  icon.style.fontSize = '16px'
  icon.style.color = 'green'

  container.appendChild(icon)
  header.appendChild(container)
}


const hataAc = () => {
  notify({
    message: seciliIsEmri.value.isEmriNo,
    type: "error",
    displayTime: 3000,
  });
  notify({
    message: seciliIsEmri.value.urunKodu,
    type: "error",
    displayTime: 3000,
  });
  hataDialogRef.value.acDialog({
    isEmriNo: seciliIsEmri.value.isEmriNo,
    urunKodu: seciliIsEmri.value.urunKodu,
    urunAdi: seciliIsEmri.value.urunAdi,
  })
}
</script>

<style scoped>
th,
td {
  text-align: center;
}

.etiket {
  padding: 0;
  margin: 0;
  font-size: 14px;
  font-weight: lighter;
}

.text {
  padding: 0;
  margin: 0;
  font-size: 16px;

  /* font-weight: bold; */
}

.status-toggle {
  cursor: pointer;
  inline-size: 180px;
}

.dx-dropdownlist-popup-wrapper .dx-list:not(.dx-list-select-decorator-enabled) .dx-list-item-content {
  padding-inline: 7px;
}

.custom-item {
  position: relative;
  min-block-size: 30px;
}

.custom-item .product-name {
  display: inline-block;
  font-size: 15px;
  inline-size: 100%;
  line-height: 30px;
  padding-inline-start: 50px;
  text-indent: 0;
}

.custom-item > img {
  position: absolute;
  inset-block-start: 50%;
  inset-inline-start: 1px;
  margin-block-start: -15px;
}

.dx-dropdowneditor-input-wrapper .custom-item > img {
  padding-inline-start: 8px;
}

.dx-theme-material #custom-templates .dx-texteditor-buttons-container {
  display: none;
}

.current-value {
  padding-block: 10px;
  padding-inline: 0;
}

.current-value > span {
  font-weight: bold;
}

.dx-theme-material .dx-selectbox-container .product-name {
  padding-inline-start: 58px;
}
</style>

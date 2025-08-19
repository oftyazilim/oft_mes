<template>
  <DxLoadPanel v-model:visible="fullLoading" :show-indicator="true" :show-pane="true" :shading="true"
    message="Yükleniyor..." :close-on-outside-click="false" />
  <VRow class="match-height pt-0 gap-0">
    <VCol md="8" cols="12">
      <VCard>
        <VCardItem class="pb-0" title="İş Emirleri" subtitle="Kontrole alınmış iş emirleri">
          <template #append>
            <div class="d-flex gap-2">
              <VBtn variant="tonal" append-icon="tabler-refresh" @click="EmirleriAl">
                Yenile
              </VBtn>
              <VBtn :disabled="isEmriNo.length === 0" variant="tonal" append-icon="tabler-picture-in-picture"
                @click="pdfGoster">
                Teknik Resim
              </VBtn>
              <VBtn :disabled="isEmriNo.length === 0" variant="tonal" append-icon="tabler-zoom-check"
                @click="KontrolAcKaydet">
                Aktif Yap
              </VBtn>
            </div>
          </template>
        </VCardItem>

        <VCardText class="pa-3 py-0 mt-0">
          <DxDataGrid id="gridEmirler" ref="emirlerRef" :key="emirlerKey" :data-source="gridEmirler" key-expr="id"
            :show-borders="true" :focused-row-enabled="true" :row-alternation-enabled="true" :min-width="200"
            :height="583" :allow-column-resizing="true" column-resizing-mode="widget" :column-auto-width="false"
            v-model:focused-row-key="focusedRowKey" @row-dbl-click="onRowDblClick"
            @selection-changed="onSelectionChanged">

            <DxColumn data-field="is_open" caption="Açık" :width="30" sort-order="desc" :sort-index="1"
              cell-template="openTemplate" />
            <DxColumn data-field="is_active" caption="Kontrol Ediliyor" :width="30" sort-order="desc" :sort-index="0"
              cell-template="aktifTemplate" />
            <DxColumn data-field="is_use_quality" caption="Kontrol Gerekli" :width="30"
              cell-template="gerekliTemplate" />
            <DxColumn data-field="is_check_quality_opr" caption="Inspektor Çağrıldı" :width="30"
              cell-template="cagirTemplate" />
            <DxColumn data-field="created_at" caption="Tarih" :width="130" data-type="date" sort-order="desc"
              :sort-index="2" :format="{
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
            <DxColumn data-field="id" caption="ID" :visible="false" :width="40" />
            <DxColumn data-field="isemri_id" caption=" " data-type="number" :width="30" :visible="false" />
            <DxColumn data-field="TANIM" caption="İstasyon" :width="100" />
            <DxColumn data-field="isemri_no" caption="İş Emri No" :width="110" :allow-sorting="false" />
            <DxColumn data-field="item_name" caption="Stok Adı" :allow-sorting="false" :min-width="300" />
            <DxColumn data-field="item_code" caption="Stok Kodu" :visible="true" :width="150" :allow-sorting="false" />
            <DxColumn data-field="name" caption="Inspektor" :width="120" />
            <DxColumn data-field="istasyon_id" caption="İSTASYON ID" data-type="number" :width="60" :visible="false" />


            <DxLoadPanel v-model:visible="loadingVisible" :show-indicator="true" :show-pane="true" :shading="true" />
            <DxGroupPanel :visible="false" />
            <DxHeaderFilter :visible="true" />
            <DxFilterPanel :visible="false" />
            <DxFilterRow :visible="false" />
            <DxSearchPanel :visible="true" :width="240" />
            <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always" />
            <DxSorting mode="none" />
            <DxSelection select-all-mode="page" mode="single" show-check-boxes-mode="always" />

            <DxSummary>
              <DxTotalItem :align-by-column="true" column="TANIM" summary-type="count" display-format="{0} adet"
                alignment="right" />
            </DxSummary>

            <template #openTemplate="{ data }">
              <template v-if="data.value === true">
                <i :class="['dx-icon', 'dx-icon-sun']" :style="{ fontSize: '20px', color: 'green' }"></i>
              </template>
              <template v-else>
                <i :class="['dx-icon', 'dx-icon-moon']" :style="{ fontSize: '20px', color: 'lightblue' }"></i>
              </template>
            </template>
            <template #aktifTemplate="{ data }">
              <template v-if="data.value === true">
                <i :class="['dx-icon', 'dx-icon-eyeopen']" :style="{ fontSize: '20px', color: 'green' }"></i>
              </template>
            </template>
            <template #gerekliTemplate="{ data }">
              <template v-if="data.value === true">
                <i :class="['dx-icon', 'dx-icon-taskcomplete']" :style="{ fontSize: '20px', color: 'green' }"></i>
              </template>
            </template>
            <template #cagirTemplate="{ data }">
              <template v-if="data.value === true">
                <i :class="['dx-icon', 'dx-icon-runner']" :style="{ fontSize: '20px', color: 'green' }"></i>
              </template>
            </template>

          </DxDataGrid>


          <VCardText class="d-flex justify-center my-0 py-2">
            <p class="d-flex  align-center">
              <i :class="['dx-icon', 'dx-icon-sun']" :style="{ fontSize: '20px', color: 'green' }"></i>
              <span class="ms-3">Kontrol Açık</span>
            </p>

            <p class="d-flex  align-center ps-4">
              <i :class="['dx-icon', 'dx-icon-moon']" :style="{ fontSize: '20px', color: 'lightblue' }"></i>
              <span class="ms-3">Kontrol Kapalı</span>
            </p>

            <p class="d-flex align-center ps-4">
              <i :class="['dx-icon', 'dx-icon-eyeopen']" :style="{ fontSize: '20px', color: 'green' }"></i>
              <span class="ms-3">Kontrol Yapılıyor</span>
            </p>

            <p class="d-flex align-center ps-4">
              <i :class="['dx-icon', 'dx-icon-taskcomplete']" :style="{ fontSize: '20px', color: 'green' }"></i>
              <span class="ms-3">Kontrol Gerekli</span>
            </p>

            <p class="d-flex align-center ps-4">
              <i :class="['dx-icon', 'dx-icon-runner']" :style="{ fontSize: '20px', color: 'green' }"></i>
              <span class="ms-3">İnspektör Çağırıldı</span>
            </p>

            <p class="d-flex align-center ps-8">
              <i :class="['dx-icon', 'dx-icon-info']" :style="{ fontSize: '20px', color: 'green' }"></i>
              <span class="ms-3" :style="{ fontStyle: 'italic', fontWeight: 'bold' }"> Detay bilgi için satıra çift
                tıklayınız
              </span>
            </p>
          </VCardText>

        </VCardText>
      </VCard>
    </VCol>

    <VCol md="4" cols="12">


      <VCol cols="12" class="pa-0">
        <VCard :disabled="isEmriNo.length === 0">
          <VCardItem class="pb-0" title="Seri Başlangıç Onayı" subtitle="Montajın başlangıcında yapılan kontroller">
            <template #append>
              <VBtn :disabled="isEmriNo.length === 0" variant="tonal" rounded icon="tabler-binary-tree-2"
                @click="popupUrunAgaciVisible = true" />
            </template>
          </VCardItem>

          <VCardText class="gap-2 pa-3">
            <VRow dense>
              <VCol cols="12" class="d-flex mt-2 pa-0">
                <VRow dense>
                  <VCol cols="8" class="d-flex pa-1 pe-2">
                    <VTextarea v-model="kontroller.urunAgaciText" label="Ürün Ağacı Kontrolü" variant="outlined"
                      auto-grow rows="1" readonly />
                  </VCol>
                  <VCol cols="4" class="d-flex pa-1 pe-2">
                    <DxSelectBox :data-source="data" v-model:value="kontroller.urunAgaciCheck" display-expr="title"
                      value-expr="value" :height="37" :read-only="true" placeholder="Boş" />
                  </VCol>
                </VRow>
              </VCol>

              <VCol cols="12" class="d-flex mt-2 pa-0">
                <VRow dense>
                  <VCol cols="8" class="d-flex pa-1">
                    <VTextarea v-model="kontroller.gorunumText" label="Görünüm Kontrolü" variant="outlined" auto-grow
                      rows="1" readonly />
                  </VCol>
                  <VCol cols="4" class="d-flex pa-1 pe-2">
                    <DxSelectBox :data-source="data" v-model:value="kontroller.gorunumCheck" display-expr="title"
                      value-expr="value" :height="37" :read-only="true" placeholder="Boş" />
                  </VCol>
                </VRow>
              </VCol>

              <VCol cols="12" class="d-flex mt-2 pa-0">
                <VRow dense>
                  <VCol cols="8" class="d-flex pa-1">
                    <VTextarea v-model="kontroller.olcuselText" label="Ölçüsel Kontrol" variant="outlined" auto-grow
                      rows="1" readonly />
                  </VCol>
                  <VCol cols="4" class="d-flex pa-1 pe-2">
                    <DxSelectBox :data-source="data" v-model:value="kontroller.olcuselCheck" display-expr="title"
                      value-expr="value" :height="37" :read-only="true" placeholder="Boş" />
                  </VCol>
                </VRow>
              </VCol>

              <VCol cols="12" class="d-flex mt-2 pa-0">
                <VRow dense>
                  <VCol cols="8" class="d-flex pa-1">
                    <VTextarea v-model="kontroller.paketlemeText" label="Paketleme Kontrolü" variant="outlined"
                      auto-grow rows="1" readonly />
                  </VCol>
                  <VCol cols="4" class="d-flex pa-1 pe-2">
                    <DxSelectBox :data-source="data" v-model:value="kontroller.paketlemeCheck" display-expr="title"
                      value-expr="value" :height="37" :read-only="true" placeholder="Boş" />
                  </VCol>
                </VRow>
              </VCol>

              <VCol cols="12" class="d-flex mt-2 pa-0">
                <VRow dense>
                  <VCol cols="8" class="d-flex pa-1">
                    <VTextarea v-model="kontroller.etiketText" label="Etiket Kontrolü" variant="outlined" auto-grow
                      rows="1" readonly />
                  </VCol>
                  <VCol cols="4" class="d-flex pa-1 pe-2">
                    <DxSelectBox :data-source="data" v-model:value="kontroller.etiketCheck" display-expr="title"
                      value-expr="value" :height="37" :read-only="true" placeholder="Boş" />
                  </VCol>
                </VRow>
              </VCol>

              <VCol cols="12" class="d-flex mt-2 pa-0">
                <VRow dense>
                  <VCol cols="8" class="d-flex pa-1">
                    <VTextarea v-model="kontroller.dokumanText" label="Doküman Kontrolü" variant="outlined" auto-grow
                      rows="1" readonly />
                  </VCol>
                  <VCol cols="4" class="d-flex pa-1 pe-2">
                    <DxSelectBox :data-source="data" v-model:value="kontroller.dokumanCheck" display-expr="title"
                      value-expr="value" :height="37" :read-only="true" placeholder="Boş" />
                  </VCol>
                </VRow>
              </VCol>

            </VRow>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" class="pa-0 pt-3">
        <VCard :disabled="isEmriNo.length === 0">

          <VCardItem class="pb-0" title="Onay Süreci" subtitle="Tam onay alana kadarki kontrollerin listesidir">
            <!-- <template #append>
              <VBtn :disabled="isEmriNo.length === 0" variant="tonal" rounded icon="tabler-binary-tree-2"
                @click="popupUrunAgaciVisible = true"/>
          </template> -->
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
                  <i :class="['dx-icon', 'dx-icon-isnotblank']" :style="{ fontSize: '16px', color: 'green' }"></i>
                </template>
                <template v-else-if="data.value === 'Şartlı Onay'">
                  <i :class="['dx-icon', 'dx-icon-isnotblank']" :style="{ fontSize: '16px', color: 'gold' }"></i>
                </template>
                <template v-else-if="data.value === 'Red'">
                  <i :class="['dx-icon', 'dx-icon-isnotblank']" :style="{ fontSize: '16px', color: 'red' }"></i>
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

  <VRow class="match-height pt-0 gap-0">

    <VCol cols="12" md="9">
      <VCard :disabled="isEmriNo.length === 0">
        <VCardItem class="pb-0 gap-2" title="Seri Kontroller" subtitle="Montajı yapılan ürünlerin birebir kontrolleri">
          <template #append>
            <div class="d-flex gap-2">
              <VBtn variant="tonal" icon="tabler-trash" rounded @click="kontrolSil" />
              <VBtn variant="tonal" icon="tabler-photo" rounded @click="fotoGoster" />
            </div>
          </template>
        </VCardItem>

        <VCardText class="gap-2 pa-2">

          <DxDataGrid id="gridKontroller" ref="dataGridRefKontrol" :data-source="gridDataKontroller" key-expr="id"
            :show-borders="true" width="100%" :focused-row-enabled="true" :row-alternation-enabled="true" height="250"
            :word-wrap-enabled="false" @focused-row-changed="onFocusedRowChangedK">

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
              <template v-if="data.value === '0' || data.value === 0">
                <i :class="['dx-icon', '']" :style="{ fontSize: '16px', color: 'green' }"></i>
              </template>
              <template v-else>
                <i :class="['dx-icon', 'dx-icon-isnotblank']" :style="{ fontSize: '16px', color: 'orange' }"></i>
              </template>
            </template>

            <template #fotoTemplate="{ data }">
              <template v-if="data.value === true">
                <i @click="fotoGoster()" :class="['dx-icon', 'dx-icon-image']"
                  :style="{ fontSize: '16px', color: 'green' }"></i>
              </template>
            </template>
          </DxDataGrid>

        </VCardText>

      </VCard>
    </VCol>

    <VCol cols="12" md="3">
      <VCard :disabled="isEmriNo.length === 0">
        <VCardItem class="pb-0 gap-2" title="Kontrol Edilmeyenler" subtitle="Gerekçeler girilmelidir" />

        <VCardText class="gap-2 pa-2">

          <DxDataGrid id="gridKontroller" ref="dataGridRefKontrol" :data-source="serinoListesi" key-expr="id"
            :show-borders="true" width="100%" :focused-row-enabled="true" :row-alternation-enabled="true" height="250"
            :word-wrap-enabled="false" @selection-changed="onSelectionChangedKontrolsuzler">

            <DxColumn data-field="id" caption="ID" width="70" :visible="false" />
            <DxColumn data-field="serino" caption="Seri No" width="150" />

            <DxSorting mode="none" />
            <DxScrolling mode="virtual" />
            <DxSelection mode="none" />

          </DxDataGrid>

        </VCardText>

      </VCard>
    </VCol>

  </VRow>

  <VRow class="match-height pt-0 gap-0">

    <VCol cols="12">
      <VAlert type="error" border="start" colored-border elevation="2" variant="tonal">

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
    </VCol>
  </VRow>

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
        <DxTotalItem :align-by-column="true" column="stok_kodu" summary-type="count" display-format="Satır sayısı: {0}"
          alignment="right" />
      </DxSummary>

    </DxDataGrid>

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
import { nextTick, onMounted, ref, watch } from 'vue';
import { VBtn } from "vuetify/components";

// const pdfUrl = ref('/api/pdf-goster')
const popupIsemriSecVisible = ref(false);
const popupUrunAgaciVisible = ref(false);
const userData = useCookie<any>("userData");
const loadingVisible = ref<boolean>(false);
// Satır çift tıklama tam süreç yükleme göstergesi
const fullLoading = ref(false);
const gridEmirler = ref<any[]>([]);
const dataAgac = ref<any[]>([]);
const emirlerKey = ref(0);
const focusedRowKey = ref(null);
const isEmriID = ref(0);
const gridDataKontroller = ref<{ seri_no: string }[]>([]);
const emirlerRef = ref<DxDataGrid | null>(null);
const agacRef = ref<DxDataGrid | null>(null);
// Seri kontroller grid'i (foto silme sonrası refresh için)
const dataGridRefKontrol = ref<DxDataGrid | null>(null);
const selectedRowKeys = ref(0);
const isEmriNo = ref("");
const isEmriMiktari = ref(0);
const popupSeriKontrolVisible = ref(false)
const popupVisible = ref(false)
const serino = ref('')
const sonuc = ref('')
// const secilenSonuc = ref('')
const hataOzet = ref('')
const seciliHatalar = ref<Record<string, boolean>>({})
// const serinoRef = ref()
const gridSurec = ref<GridRow[]>([]);

const allSelected = ref(false)
const selectedCount = ref(0)
const malzemeSayisi = ref(0)
const resimler = ref([])

// const preview = ref('')
// const fileInput = ref()
// const selectedOption = ref([])
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

interface GridRow {
  rev: number;
  tarih: Date;
  aciklama: string;
}

const items = [
  { title: '✔️ Onaylandı', value: 'Onaylandı', color: '#d4edda' },
  { title: '❌ Red', value: 'Red', color: '#f8d7da' },
  { title: '⚠️ Şartlı Onay', value: 'Şartlı Onay', color: '#fff3cd' }
]

const data = new ArrayStore({
  data: items,
  key: 'value',
});

// const formatNumber = number => {
//   return new Intl.NumberFormat('tr-TR', { maximumFractionDigits: 0 }).format(
//     number,
//   )
// }

// const kaydetOptions = {
//   width: 100,
//   type: 'success',
//   text: 'Tamam',
//   stylingMode: 'contained',
// }

const vazgecOptions = {
  width: 100,
  type: 'normal',
  text: 'Kapat',
  stylingMode: 'contained',
  onClick: () => {
    popupUrunAgaciVisible.value = false
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
  loadingVisible.value = true;

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
    // KontrolAktifKaydet('aktif')
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
  loadingVisible.value = false
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
    const response = await axios.get("/api/kontrolListesiAl", {
      params: {
        istasyon: 0,
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

  loadingVisible.value = true;

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
  finally {
    loadingVisible.value = false;
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

    dataAgac.value = response.data.malzemeler
    malzemeSayisi.value = response.data.toplamMalzeme
  }
  catch (error) {
    console.error('Veri çekilirken hata oluştu: ', error)
  }
}

const AgacYukle = async () => {
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

const KontrolAcikKaydet = async () => {

  await axios.post('/api/kontrolAcKaydet', {
    isEmriID: seciliIsEmri.value.isEmriId,
    userId: userData.value.id,
  })
  EmirleriAl()
};

// *************** Olaylar *********************************************************** //

const onFocusedRowChangedK = (e: any) => {
  photoID.value = e.row?.data?.id || 0;
  photoSeriNo.value = e.row?.data?.seri_no || '';
};

const onSelectionChanged = (e: any) => {
  // if (e.selectedRowsData.length > 0) {
  //   temizle();
  //   bosalt();
  //   isEmriID.value = e.selectedRowsData[0].isemri_id;
  //   selectedRowKeys.value = e.selectedRowsData[0].id;
  //   isEmriNo.value = e.selectedRowsData[0].isemri_no;
  // }
  // else isEmriID.value = 0
  // EmirSec();
};

onMounted(async () => {
  document.title = "OFT-Kalite Kontrolleri Listesi";
  EmirleriAl();

});

const onRowDblClick = async (e: any) => {
  // DevExtreme row-dbl-click event payload: e.data (clicked row), e.component (grid instance)
  const grid = e?.component;
  let rowData = e?.data;
  if (!rowData && grid && typeof grid.getSelectedRowsData === 'function') {
    const sel = grid.getSelectedRowsData();
    if (sel && sel.length) rowData = sel[0];
  }
  if (!rowData) {
    console.warn('Çift tıklanan satır verisi alınamadı.');
    return;
  }
  fullLoading.value = true;
  temizle();
  bosalt();
  isEmriID.value = rowData.isemri_id || 0;
  selectedRowKeys.value = rowData.id || 0;
  isEmriNo.value = rowData.isemri_no || '';
  try {
    await EmirSec();
  } finally {
    // EmirSec içinde async alt çağrılar (KontrolleriAl vs) da çalıştıktan sonra kapanır
    fullLoading.value = false;
  }
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

interface PhotoItem {
  id: number
  url: string
  file?: File
}

const sharedHeaderWithTooltip = (container: HTMLElement, options: any) => {
  const span = document.createElement('span');
  span.textContent = options.column.caption;

  // Eğer column'da tooltip tanımı varsa kullan
  const tooltipText = options.column.tooltip || options.column.caption;
  span.setAttribute('title', tooltipText);

  container.appendChild(span);
};

const temizle = () => {
  serino.value = ''
  sonuc.value = ''
  hataOzet.value = ''
  seciliHatalar.value = {}
  resimler.value = []
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

const KontrolEdilmeyenleriAl = () => {
  const kullanilmislar = new Set(gridDataKontroller.value.map(x => x.seri_no));
  const kalanlar = serinoListesi.value.filter(s => !kullanilmislar.has(s.serino));
  serinoListesi.value = kalanlar;
};

const KontrolAcKaydet = () => {
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
      text: "Kontrol aktifleştirilecek!!!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#88efb3",
      cancelButtonColor: "#ed9595",
      reverseButtons: true,
      confirmButtonText: "Evet, aktif yapalım!",
      cancelButtonText: "Hayır, vazcgeçtim!",
    })
    .then(async (result: Awaited<ReturnType<typeof Swal.fire>>) => {
      if (result.isConfirmed) {
        await nextTick();
        temizle();
        KontrolAcikKaydet();
        bosalt();
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        // Cancelled
      }
    });
};

const fotoGoster = () => {
  fotoDialog.value = true

  axios.get('/api/hata-goster-resim',
    {
      params: { isEmriNo: seciliIsEmri.value.isEmriNo, serino: photoSeriNo.value }
    }
  ).then(res => {
    seciliKayitFotolar.value = res.data.map((f: { url: string }) => f.url)
  }).catch(() => {
    seciliKayitFotolar.value = []
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

    const remaining = resp.data?.remaining
    const seriNo = resp.data?.seri_no
    if (remaining === 0 && seriNo) {
      const row = gridDataKontroller.value.find(r => r.seri_no.startsWith(seriNo))
      if (row) {
        // @ts-ignore add dynamic field
        row.is_photo = 0
        if (dataGridRefKontrol?.value?.instance) {
          dataGridRefKontrol.value.instance.refresh()
        }
      }
    }
    // İş emri listesini de güncelle (statüler değişmiş olabilir)
    EmirleriAl()
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

const photoHeaderTemplate = (header: HTMLElement, info: any) => {
  const container = document.createElement('div')
  const icon = document.createElement('i')

  icon.className = 'dx-icon dx-icon-image'
  icon.style.fontSize = '16px'
  icon.style.color = 'green'

  container.appendChild(icon)
  header.appendChild(container)
}

// Geçici odak fonksiyonu (ileride serino input'una odak eklenebilir)
function focusSerino(): void {
  // Placeholder
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

/* Base image rule first for specificity ordering */
.custom-item>img {
  position: absolute;
  inset-block-start: 50%;
  inset-inline-start: 1px;
  margin-block-start: -15px;
}

/* Adjustment when inside dropdown editor */
.dx-dropdowneditor-input-wrapper .custom-item>img {
  padding-inline-start: 8px;
}

.custom-item .product-name {
  display: inline-block;
  font-size: 15px;
  inline-size: 100%;
  line-height: 30px;
  padding-inline-start: 50px;
  text-indent: 0;
}

/* (moved above) */

.dx-theme-material #custom-templates .dx-texteditor-buttons-container {
  display: none;
}

.current-value {
  padding-block: 10px;
  padding-inline: 0;
}

.current-value>span {
  font-weight: bold;
}

.dx-theme-material .dx-selectbox-container .product-name {
  padding-inline-start: 58px;
}

/* Global tam ekran yükleme katmanı */

/* (Removed custom overlay CSS – using DxLoadPanel instead) */
</style>

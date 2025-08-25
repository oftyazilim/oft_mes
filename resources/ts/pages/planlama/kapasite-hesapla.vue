<template>
  <VCard class="mt-0 pa-0">
    <VCardText class="mt-0 pa-0">
      <VCol cols="12" class="mt-0 pa-1 pe-2">
        <VCardItem class="pb-0" title="Kapasite Hesaplama" subtitle="Sonlu kapasite hesaplaması">
          <template #append>
            <div class="d-flex gap-2">
              <div class="pa-2">Ekip</div>
              <div class="dx-field-value">
                <AppTextField v-model="ekipSayisi" type="number" placeholder="0" @input="sadeceTamSayi" width="80" />
              </div>
              <div class="pa-2">İş&nbsp;Merkezi </div>
              <div class="dx-field-value">
                <DxSelectBox :data-source="merkezler" v-model="selectedMerkez" display-expr="mrk_adi"
                  value-expr="ismerkezi_kodu" :height="37" :width="150" placeholder="Seçiniz..."
                  @value-changed="getIstasyon" />
              </div>
              <div class="pa-2">İstasyon </div>
              <div class="dx-field-value">
                <DxSelectBox :data-source="istasyonlar" v-model="selectedIstasyon" display-expr="IS_ISTASYONU"
                  value-expr="IS_ISTASYONU_ID" :height="37" :width="250" placeholder="Seçiniz..."
                  @value-changed="getTiplerHaftalar" />
              </div>
              <div class="pa-2">İş&nbsp;Emri&nbsp;Tipi </div>
              <div class="dx-field-value">
                <DxSelectBox :data-source="tipler" v-model="selectedTip" display-expr="isemri_tipi"
                  value-expr="isemri_tipi" :height="37" :width="200" placeholder="Seçiniz..." />
              </div>
              <div class="pa-2">Hafta </div>
              <div class="dx-field-value">
                <DxSelectBox :disabled="!selectedIstasyon || ekipSayisi === 0" :data-source="haftalar"
                  v-model="selectedHafta" display-expr="hafta" value-expr="hafta" :height="37" :width="100"
                  placeholder="Seçiniz..." />
              </div>
              <VBtn :disabled="!selectedHafta || !selectedIstasyon || ekipSayisi === 0" variant="tonal"
                append-icon="tabler-refresh" @click="getData">
                Listele
              </VBtn>
              <VBtn :disabled="!selectedHafta || !selectedIstasyon || ekipSayisi === 0" variant="tonal"
                append-icon="tabler-math" @click="hesaplaKapasite">
                Hesapla
              </VBtn>
              <VBtn :disabled="!selectedHafta || !selectedIstasyon || ekipSayisi === 0 || maxSure === 0" variant="tonal"
                append-icon="tabler-device-floppy" @click="kaydetKapasiteSonuclari">
                Güncelle
              </VBtn>

            </div>
          </template>
        </VCardItem>

        <VCardText class="pa-1 mt-2">
          <DxDataGrid id="grid" ref="emirlerRef" :data-source="dataSource" :show-borders="true" key-expr="sira"
            :row-alternation-enabled="true" :min-width="200" :allow-column-resizing="true" column-resizing-mode="widget"
            :column-auto-width="true" @cell-prepared="onCellPrepared" :focused-row-enabled="true"
            @exporting="onExporting" :allow-column-reordering="true" @selection-changed="onSelectionChanged">

            <DxColumn data-field="sira" caption="Sıra" :width="50" />
            <DxColumn data-field="aksesuar" caption="Aksesuar" :width="60" cell-template="aksesuarTemplate"
              alignment="center" :allow-sorting="false" />
            <DxColumn data-field="isemri_id" caption="İş Emri ID" :visible="false" />
            <DxColumn data-field="satir_id" caption="İş Emri Detay ID" :visible="false" />
            <DxColumn data-field="isemri_no" caption="İş Emri No" />
            <DxColumn data-field="cari_ad" caption="Müşteri" :visible="true" :width="90" :allow-sorting="false" />

            <DxColumn data-field="siparis_belge_no" caption="Sipariş No" :width="90" :visible="true"
              :allow-sorting="false" />
            <DxColumn data-field="OPERASYON" caption="Oprsyn" :width="70" :visible="false" />
            <DxColumn data-field="stok_kodu" caption="Stok Kodu" :visible="true" :width="120" :allow-sorting="false" />
            <DxColumn data-field="stok_adi" caption="Stok Adı" :width="150" :allow-sorting="false" />
            <DxColumn data-field="isemri_miktari" caption="İşemri Miktarı" data-type="number" :width="70"
              :visible="true" :format="{
                type: 'fixedPoint',
                precision: 0,
                thousandsSeparator: ',',
              }" />
            <DxColumn data-field="kalan_miktar" caption="Kalan Miktar" data-type="number" :width="70" :visible="true"
              :allow-sorting="false" :format="{
                type: 'fixedPoint',
                precision: 0,
                thousandsSeparator: ',',
              }" />
            <DxColumn data-field="hafta" caption="Hafta" :width="70" />
            <DxColumn data-field="baslangic" caption="Hsp. Başlangıç" data-type="datetime" :format="{
              formatter: (date) => {
                const dt = new Date(date)
                const gunler = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi']
                const gun = gunler[dt.getDay()]

                const tarih = dt.toLocaleDateString('tr-TR', {
                  day: '2-digit',
                  month: '2-digit',
                  year: 'numeric',
                }).replace(/\//g, '.')

                const saat = dt.toLocaleTimeString('tr-TR', {
                  hour: '2-digit',
                  minute: '2-digit',
                })

                return `${tarih} ${saat} ${gun}`
              }
            }" :widty="140" />
            <DxColumn data-field="bitis" :width="140" caption="Hsp. Bitiş" data-type="datetime" />
            <DxColumn data-field="hafta_hesaplanan" caption="Hesaplanan Hafta" :width="70" :visible="true" />
            <DxColumn data-field="sure" caption="Tpl Süre (dk)" cell-template="surecCellTemplate" alignment="center"
              data-type="number" :width="120" :visible="true" />
            <DxColumn data-field="teslim_tarihi" caption="Teslim Tarihi" data-type="date" :width="140" :format="{
              formatter: (date) => {
                const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                  year: 'numeric',
                  month: '2-digit',
                  day: '2-digit',
                }).format(new Date(date));
                return formattedDate.replace(/\//g, '.');
              },
            }" :cell-template="getIconType" :allow-sorting="false" />
            <DxColumn data-field="sevk_tarihi" caption="Sevk Tarihi" data-type="date" :width="140" :format="{
              formatter: (date) => {
                const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                  year: 'numeric',
                  month: '2-digit',
                  day: '2-digit',
                }).format(new Date(date));
                return formattedDate.replace(/\//g, '.');
              },
            }" :cell-template="getIconType" :allow-sorting="false" />
            data-type="number" :width="120" :visible="true" />
            <DxColumn data-field="baslangic_tarihi" caption="Öng. Başlangıç" data-type="date" :width="100"
              :visible="true" :format="{
                formatter: (date) => {
                  const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',

                  }).format(new Date(date));

                  return formattedDate.replace(/\//g, '.');
                },
              }" />
            <DxColumn data-field="bitis_tarihi" caption="Öng. Bitiş" data-type="date" :width="100" :visible="true"
              :format="{
                formatter: (date) => {
                  const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                  }).format(new Date(date));

                  return formattedDate.replace(/\//g, '.');
                },
              }" alignment="left" />
            <DxColumn data-field="planlanan_baslangic" caption="Pln. Başlangıç" data-type="date" :width="100"
              :visible="true" :format="{
                formatter: (date) => {
                  const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',

                  }).format(new Date(date));

                  return formattedDate.replace(/\//g, '.');
                },
              }" />
            <DxColumn data-field="planlanan_bitis_tarihi" caption="Pln Bitiş" data-type="date" :width="100"
              :visible="true" :format="{
                formatter: (date) => {
                  const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                  }).format(new Date(date));

                  return formattedDate.replace(/\//g, '.');
                },
              }" alignment="left" />
            <DxColumn data-field="operasyon_hazirlik_suresi" caption="Hz Dk" data-type="number" :width="70"
              :visible="true" :format="{
                type: 'fixedPoint',
                precision: 1,
                thousandsSeparator: ',',
              }" />
            <DxColumn data-field="operasyon_suresi" caption="Op Dk" data-type="number" :width="70" :visible="true"
              :format="{
                type: 'fixedPoint',
                precision: 1,
                thousandsSeparator: ',',
              }" :allow-sorting="false" />
            <DxColumn data-field="isemri_tipi" caption="İş Emri Tipi" :width="90" :visible="true" />

            <DxColumn data-field="siparis_tarihi" caption="Sipariş Trh" data-type="date" :width="100" :visible="true"
              :format="{
                formatter: (date) => {
                  const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',

                  }).format(new Date(date));

                  return formattedDate.replace(/\//g, '.');
                },
              }" />
            <DxColumn data-field="belge_tarihi" caption="İş Emri Trh" data-type="date" :width="100" :visible="true"
              :format="{
                formatter: (date) => {
                  const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',

                  }).format(new Date(date));

                  return formattedDate.replace(/\//g, '.');
                },
              }" />
            <DxColumn data-field="gerceklesen_baslangic" caption="Grç Bşl Trh" data-type="date" :width="100"
              :visible="true" :format="{
                formatter: (date) => {
                  const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',

                  }).format(new Date(date));

                  return formattedDate.replace(/\//g, '.');
                },
              }" />
            <DxColumn data-field="gerceklesen_bitis" caption="Grç Btş Trh" data-type="date" :width="100" :visible="true"
              :format="{
                formatter: (date) => {
                  const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                  }).format(new Date(date));

                  return formattedDate.replace(/\//g, '.');
                },
              }" alignment="left" />


            <DxLoadPanel :key="loadingVisible" v-model:visible="loadingVisible" :show-indicator="true"
              :message="loadingMessage" :show-pane="true" :shading="true" />
            <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always" />
            <DxHeaderFilter :visible="true" />
            <DxFilterPanel :visible="true" />
            <DxFilterRow :visible="true" />
            <DxSearchPanel :visible="true" :width="240" />
            <DxSorting mode="none" />
            <DxSelection mode="multiple" />
            <DxRowDragging :allow-reordering="true" :on-drag-start="onDragStart" :on-reorder="onReorder"
              :show-drag-icons="true" />
            <!-- <DxStateStoring :enabled="true" type="localStorage" :storage-key="tab" /> -->
            <DxExport :enabled="true" :allow-export-selected-data="false" />

            <DxColumnChooser height="540px" :enabled="true" mode="select">
              <DxColumnChooserSearch :enabled="true" />
              <DxColumnChooserSelection :allow-select-all="true" :select-by-click="true" :recursive="true" />
            </DxColumnChooser>


            <DxSummary>
              <DxGroupItem :align-by-column="true" column="isemri_no" summary-type="count" display-format="{0} iş emri"
                :alignment="right" />
              <DxGroupItem :align-by-column="true" column="kalan_miktar" summary-type="sum" :alignment="right"
                :customize-text="formatSummaryText" />
              <DxGroupItem :align-by-column="true" column="siparis_miktari" summary-type="sum" :alignment="right"
                :customize-text="formatSummaryText" />
              <DxGroupItem :align-by-column="true" column="isemri_miktari" summary-type="sum" :alignment="right"
                :customize-text="formatSummaryText" />
              <DxGroupItem :align-by-column="true" column="operasyon_hazirlik_suresi" summary-type="sum"
                :alignment="right" :customize-text="formatSummaryText" />
              <DxGroupItem :align-by-column="true" column="operasyon_suresi" summary-type="sum" :alignment="right"
                :customize-text="formatSummaryText" />

              <DxTotalItem :align-by-column="true" column="isemri_no" summary-type="count" display-format="{0} iş emri"
                :alignment="right" />
              <DxTotalItem :align-by-column="true" column="kalan_miktar" summary-type="sum" :alignment="right"
                :customize-text="formatSummaryText" />
              <DxTotalItem :align-by-column="true" column="siparis_miktari" summary-type="sum" :alignment="right"
                :customize-text="formatSummaryText" />
              <DxTotalItem :align-by-column="true" column="isemri_miktari" summary-type="sum" :alignment="right"
                :customize-text="formatSummaryText" />
              <DxTotalItem :align-by-column="true" column="operasyon_hazirlik_suresi" summary-type="sum"
                :alignment="right" :customize-text="formatSummaryText" />
              <DxTotalItem :align-by-column="true" column="operasyon_suresi" summary-type="sum" :alignment="right"
                :customize-text="formatSummaryText" />
              <DxTotalItem :align-by-column="true" column="sure" summary-type="sum" :alignment="right"
                :customize-text="formatSummaryText" />
            </DxSummary>

            <template #aksesuarTemplate="{ data }">
              <template v-if="data.value === 'Aksesuarlı'">
                <i :class="['dx-icon', 'dx-icon-gift']" :style="{ fontSize: '20px' }"></i>
              </template>
            </template>

            <template #surecCellTemplate="{ data: cellData }">
              <VProgressLinear :model-value="Math.ceil((cellData.value / maxSure) * 100)" height="20" color="warning">
                <template #default>
                  <span>{{ Math.ceil(cellData.value) }}</span>
                </template>
              </VProgressLinear>
            </template>

          </DxDataGrid>
        </VCardText>

      </VCol>
    </VCardText>
  </VCard>

</template>


<script setup lang="ts">
import axios from 'axios';
import {
  DxChart
} from 'devextreme-vue/chart';
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
  DxHeaderFilter,
  DxRowDragging,
  DxScrolling,
  DxSearchPanel,
  DxSelection,
  DxSorting,
  DxSummary,
  DxTotalItem
} from 'devextreme-vue/data-grid';
import { DxLoadPanel } from 'devextreme-vue/load-panel';
import DxSelectBox from 'devextreme-vue/select-box';
import { exportDataGrid } from 'devextreme/excel_exporter';
import notify from 'devextreme/ui/notify';
import { Workbook } from 'exceljs';
import { saveAs } from 'file-saver-es';
import { onMounted, ref } from 'vue';

const userData = useCookie<any>("userData");
const chart = ref<DxChart>();
const dataSource = ref<any[]>([]);
const haftalar = ref<Hafta[]>([]);
const selectedHafta = ref(null)
const merkezler = ref<Merkez[]>([]);
const istasyonlar = ref<Istasyon[]>([]);
const tipler = ref<Tip[]>([]);
const selectedMerkez = ref<string | null>(null)
const selectedIstasyon = ref<string | null>(null)
const selectedTip = ref<string | null>(null)
const loadingVisible = ref<boolean>(false)
const loadingMessage = ref<string>('')
const ekipSayisi = ref<number | null>(1)
const selectedRowKeys = ref<number[]>([])
const takvim = ref({})
const hataSatirlari = ref<any[]>([])
const loading = ref(false)
const maxSure = ref(0)


definePage({
  meta: { action: ['create'], subject: ['planlama'] }
})


interface Hafta {
  hafta: string | number;
}
interface Merkez {
  merkez: string | number;
}
interface Istasyon {
  istasyon: string | number;
}
interface Tip {
  tip: string | number;
}

function sadeceTamSayi(event: Event) {
  const input = event.target as HTMLInputElement
  input.value = input.value.replace(/[^\d]/g, '')
  ekipSayisi.value = input.value ? parseInt(input.value) : null
}

onMounted(async () => {
  try {
    const res = await axios.get('/api/kapasite-merkez-al');
    const rawMerkezler = Array.isArray(res.data.merkezler) ? res.data.merkezler : [];
    const normalized = rawMerkezler.map((t: any) => ({
      ismerkezi_kodu: t?.ismerkezi_kodu ?? '',
      mrk_adi: t?.mrk_adi ?? ''
    }));
    merkezler.value = [...normalized];
  } catch (e) {
    console.error('Veri çekilemedi', e);
  }
  fetchTakvim();
});

async function getIstasyon() {
  try {
    loadingMessage.value = 'İstasyonlar yükleniyor...'
    loadingVisible.value = true
    const res = await axios.get('/api/kapasite-istasyon-al', {
      params: {
        merkez: selectedMerkez.value,
      }
    });
    istasyonlar.value = res.data.istasyonlar;
  } catch (e) {
    console.error('Veri çekilemedi', e);
  } finally {
    loadingVisible.value = false
    loadingMessage.value = ''
  }
};

async function getTiplerHaftalar() {
  try {
    loadingMessage.value = 'Tipler ve haftalar yükleniyor...'
    loadingVisible.value = true
    const res = await axios.get('/api/kapasite-hafta', {
      params: {
        istasyon: selectedIstasyon.value,
      }
    });
    haftalar.value = res.data.haftalar;
    const rawTipler = Array.isArray(res.data.tipler) ? res.data.tipler : [];
    const normalized = rawTipler.map((t: any) => ({ isemri_tipi: t?.isemri_tipi ?? '' }));
    tipler.value = [{ isemri_tipi: 'TÜMÜ' }, ...normalized];
    if (selectedTip.value == null) selectedTip.value = 'TÜMÜ';
  } catch (e) {
    console.error('Veri çekilemedi', e);
  } finally {
    loadingVisible.value = false
    loadingMessage.value = ''
  }
}

async function getData() {
  loadingMessage.value = 'Veriler yükleniyor...'
  loadingVisible.value = true;
  dataSource.value = [];
  try {
    const res = await axios.get('/api/get-kapasite-data', {
      params: {
        hafta: selectedHafta.value,
        istasyon: selectedIstasyon.value,
        ekip: ekipSayisi.value,
        tip: selectedTip.value ?? null,
      }
    });
    dataSource.value = res.data;
  } catch (e) {
    console.error('Veri çekilemedi', e);
  }
  // Hesaplama sürecinde de kullanıcıyı bilgilendir
  try {
    loadingMessage.value = 'Hesaplanıyor...'
    hesaplaKapasite();
  } finally {
    loadingVisible.value = false;
    loadingMessage.value = ''
  }
}

const convertToISODate = (dateString: string | null | undefined) => {
  if (!dateString) return null

  const date = new Date(dateString)
  if (isNaN(date.getTime())) return null

  return date.toISOString().split('T')[0] // sadece yyyy-mm-dd
}

const getIconType = (cellElement: HTMLElement, cellInfo: any): void => {
  // Tarihleri kontrol et ve dönüştür
  const teslimTarihiISO = convertToISODate(cellInfo.data.teslim_tarihi)
  const planlananBitisISO = convertToISODate(cellInfo.data.bitis)

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

const fetchTakvim = async () => {
  try {
    loading.value = true
    const res = await axios.get('/api/kapasite-takvim')
    const hamVeri = res.data
    takvim.value = hamVeri.reduce((acc, satir) => {
      const gun = satir.gun.toLowerCase()
      if (!acc[gun]) acc[gun] = []
      acc[gun].push({ bas: parseInt(satir.bas), bit: parseInt(satir.bit) })
      return acc
    }, {})
  } catch (e) {
    console.error('Takvim alınamadı', e)
  } finally {
    loading.value = false
  }
}

const planlaKapasiteTakvimli = (veriler, takvim) => {
  const gunler = ['pazar', 'pazartesi', 'salı', 'çarşamba', 'perşembe', 'cuma', 'cumartesi']
  const sonZamanlar = {}
  const sonuc = []
  hataSatirlari.value = []
  console.log("Takvim: ", takvim)
  const calismaGunleri = Object.keys(takvim).filter(gun => takvim[gun] && takvim[gun].length > 0)

  for (const satir of veriler) {
    const istasyon = satir.IS_ISTASYONU_ID || satir.istasyon || 'GENEL'

    const opSure = Number(satir.operasyon_suresi || 0)
    const hazSure = Number(satir.operasyon_hazirlik_suresi || 0)
    const ekip = ekipSayisi.value ?? 1
    const toplamSure = (opSure / ekip) //+ hazSure

    maxSure.value = Math.max(maxSure.value, toplamSure)

    if (!sonZamanlar[istasyon]) {
      const now = new Date()
      const gunAdi = gunler[now.getDay()]
      const bloklar = takvim[gunAdi]

      if (bloklar && bloklar.length > 0) {
        const ilkBlok = bloklar[0]
        const blokBasSaat = Math.floor(ilkBlok.bas / 60)
        const blokBasDakika = ilkBlok.bas % 60

        if (now.getHours() < blokBasSaat || (now.getHours() === blokBasSaat && now.getMinutes() < blokBasDakika)) {
          now.setHours(blokBasSaat, blokBasDakika, 0, 0)
        }
      }

      sonZamanlar[istasyon] = new Date(
        now.getFullYear(), now.getMonth(), now.getDate(), now.getHours(), now.getMinutes(), 0, 0
      )
    }

    let z = new Date(sonZamanlar[istasyon])
    let baslangic = null
    let kalanSure = toplamSure

    for (let i = 0; i < 1000; i++) {
      const gunAdi = gunler[z.getDay()]
      const bloklar = takvim[gunAdi]

      if (!bloklar || bloklar.length === 0) {
        z.setDate(z.getDate() + 1)
        z.setHours(0, 0, 0, 0)
        continue
      }

      for (const blok of bloklar) {
        const blokBas = new Date(z.getFullYear(), z.getMonth(), z.getDate(), 0, 0, 0, 0)
        blokBas.setMinutes(blok.bas)

        const blokBit = new Date(z.getFullYear(), z.getMonth(), z.getDate(), 0, 0, 0, 0)
        blokBit.setMinutes(blok.bit)

        if (z >= blokBit) continue

        const basla = z < blokBas ? blokBas : new Date(z)
        const musaade = Math.max(0, (blokBit.getTime() - basla.getTime()) / 60000)

        if (!baslangic) baslangic = new Date(basla)

        if (musaade >= kalanSure) {
          const bitis = new Date(basla.getTime() + kalanSure * 60000)

          // +2 iş günü teslim tarihi hesapla
          const teslim = new Date(bitis)
          let eklendi = 0
          while (eklendi < 2) {
            teslim.setDate(teslim.getDate() + 1)
            const g = gunler[teslim.getDay()]
            if (calismaGunleri.includes(g)) eklendi++
          }

          const yil = bitis.getFullYear().toString().slice(-2)
          const haftalik = getISOWeek(bitis)
          const hesaplananHafta = `${yil}-${haftalik.toString().padStart(2, '0')}`

          // toLocaleString('sv-SE') → ISO benzeri format, saat farkı yaratmaz
          const formatDate = d => d.toLocaleString('sv-SE').replace(' ', 'T')

          sonuc.push({
            ...satir,
            baslangic: formatDate(baslangic),
            bitis: formatDate(bitis),
            teslim_tarihi: formatDate(teslim),
            sure: toplamSure,
            hafta_hesaplanan: hesaplananHafta,
          })

          sonZamanlar[istasyon] = bitis
          kalanSure = 0
          break
        }

        kalanSure -= musaade
        z = new Date(blokBit)
      }

      if (kalanSure <= 0) break
      z.setDate(z.getDate() + 1)
      z.setHours(0, 0, 0, 0)
    }

    if (kalanSure > 0) {
      hataSatirlari.value.push(satir)
    }
  }

  return sonuc
}

function getISOWeek(date) {
  const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()))
  const dayNum = d.getUTCDay() || 7
  d.setUTCDate(d.getUTCDate() + 4 - dayNum)
  const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1))
  return Math.ceil((((d - yearStart) / 86400000) + 1) / 7)
}

const hesaplaKapasite = () => {
  const planlanmis = planlaKapasiteTakvimli(dataSource.value, takvim.value)
  dataSource.value = planlanmis

  if (hataSatirlari.value.length > 0) {
    notify({
      message: `${hataSatirlari.value.length} iş emri planlanamadı.`,
      type: 'warning',
      displayTime: 5000,
    })
    // ElMessage.warning(`${hataSatirlari.value.length} iş emri planlanamadı.`)
  }
}

function formatSummaryText(e) {
  return new Intl.NumberFormat('tr-TR', { maximumFractionDigits: 0 }).format(e.value);
}

const onCellPrepared = (e: any) => {
  if (e.rowType === 'data' && (e.column.dataField === 'baslangic' ||
    e.column.dataField === 'bitis' ||
    e.column.dataField === 'hafta_hesaplanan' || e.column.dataField === 'teslim_tarihi')) {
    e.cellElement.style.color = 'black'
    e.cellElement.style.fontWeight = 'bold'
    e.cellElement.style.backgroundColor = '#c4f2c4'
  }

  if (e.rowType === 'data' && e.column.dataField === 'sira')
    e.cellElement.style.fontWeight = 'bold'
}

const onExporting = (e: DxDataGridTypes.ExportingEvent) => {
  const workbook = new Workbook()
  const worksheet = workbook.addWorksheet('KapasiteMontaj')

  exportDataGrid({
    component: e.component,
    worksheet,
    autoFilterEnabled: true,
  }).then(() => {
    workbook.xlsx.writeBuffer().then(buffer => {
      saveAs(
        new Blob([buffer], { type: 'application/octet-stream' }),
        'KapasiteMontaj.xlsx',
      )
    })
  })

  e.cancel = true
}

const onSelectionChanged = e => {
  selectedRowKeys.value = e.selectedRowKeys
}

const onDragStart = e => {
  const draggedId = e.itemData.sira
  e.itemData = selectedRowKeys.value.includes(draggedId)
    ? dataSource.value.filter(item => selectedRowKeys.value.includes(item.sira))
    : [e.itemData]
}

const onReorder = e => {
  const gridInstance = e.component;
  const visibleRows = gridInstance.getVisibleRows();

  // Hedef 'gerçek veri indexini' bulmak için
  const toVisibleRow = visibleRows[e.toIndex];
  let toIndex = dataSource.value.findIndex(item => item.sira === toVisibleRow?.data?.sira);

  const movedItems = Array.isArray(e.itemData) ? e.itemData : [e.itemData];

  const fromIndexes = movedItems.map(item =>
    dataSource.value.findIndex(row => row.sira === item.sira)
  );
  const fromMinIndex = Math.min(...fromIndexes);

  // Aşağıya taşımalarda toIndex'ten movedItems.length - 1 kadar çık
  if (toIndex > fromMinIndex) {
    toIndex = toIndex - movedItems.length + 1;
  }

  if (toIndex < 0) toIndex = 0;

  const remaining = dataSource.value.filter(item => !movedItems.includes(item));

  const newList = [
    ...remaining.slice(0, toIndex),
    ...movedItems,
    ...remaining.slice(toIndex),
  ];

  dataSource.value = newList;

  hesaplaKapasite()
};

const kaydetKapasiteSonuclari = async () => {
  try {
    const payload = dataSource.value.map(item => ({
      worder_id: item.isemri_id,
      worder_d_id: item.satir_id,
      worder_no: item.isemri_no,
      baslangic: item.baslangic,
      bitis: item.bitis,
      teslim_tarihi: item.teslim_tarihi,
    })).filter(item => item.worder_id && item.baslangic && item.bitis && item.teslim_tarihi)

    if (payload.length === 0) {
      notify({ message: 'Kaydedilecek veri yok', type: 'warning' })
      return
    }

    await axios.post('/api/kapasite-guncelle', { liste: payload })
    notify({ message: 'Kapasite verileri kaydedildi', type: 'success' })
  } catch (error) {
    console.error(error)
    notify({ message: 'Kayıt hatası oluştu', type: 'error' })
  }
}

</script>

<style scoped>
#grid {
  display: flex;
  flex-direction: column;
  block-size: 80vh;
}
</style>

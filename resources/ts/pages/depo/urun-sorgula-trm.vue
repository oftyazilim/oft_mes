<script setup lang="ts">
import axios from 'axios'
import {
  DxColumn,
  DxDataGrid
} from 'devextreme-vue/data-grid'
import notify from 'devextreme/ui/notify'
import { nextTick, onMounted, ref, watch } from 'vue'
import { VCardText } from 'vuetify/components'

document.title = 'OFT - Ürün Sorgula';

// Barkod alanına otomatik odaklanmak için ref
const barkodInput = ref<HTMLInputElement | null>(null)

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

// Grid verisi (depolar vb.)
const gridData = ref([])
const gridDataSayim = ref([])

// Sayfa yüklendiğinde barkod alanına odaklan
onMounted(() => {
  DepolariAl()
  nextTick(() => {
    barkodInput.value?.focus?.()
  })
})

const isLoading = ref(false)

// Snackbar durumu
const snackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref<'success' | 'error'>('success')

// Kod arama butonuna basıldığında
const applyCode = async () => {
  if (!formDatam.value.barkod) return

  isLoading.value = true

  try {
    formDatam.value.urunID = 0
    formDatam.value.urunKodu = ''
    formDatam.value.urunAdi = ''
    formDatam.value.urunAdi2 = ''
    formDatam.value.toplama = 0
    formDatam.value.toplami = 0
    formDatam.value.toplamSayim = 0
    gridData.value = []
    gridDataSayim.value = []

    await UrunSorgula()
    formDatam.value.barkod = ''
    barkodInput.value?.focus?.()

    // snackbarMessage.value = `Ürün "${formData.value.item_code}" başarıyla sorgulandı.`
    // snackbarColor.value = 'success'
    // snackbarVisible.value = true

  } catch (e) {
    console.error('Hata:', e)

    snackbarMessage.value = 'Bir hata oluştu!'
    snackbarColor.value = 'error'
    snackbarVisible.value = true
  } finally {
    isLoading.value = false
  }
}

// API'den ürün bilgilerini ve depo verilerini al
const UrunSorgula = async () => {
  try {
    const response = await axios.get('/api/urunsorgula', {
      params: {
        itemID: formDatam.value.barkod,
      },
    })

    formDatam.value.urunID = response.data.urun.item_id
    formDatam.value.urunKodu = response.data.urun.item_code
    formDatam.value.urunAdi = response.data.urun.item_name
    formDatam.value.urunAdi2 = response.data.urun.item_name2
    formDatam.value.toplama = response.data.toplama
    formDatam.value.toplami = response.data.toplami
    formDatam.value.toplamSayim = response.data.toplamSayim
    gridData.value = response.data.stoklar || []
    gridDataSayim.value = response.data.detay || []
    preview.value = ''

    fetchPhotos()
  } catch (error) {
    console.error('Veri çekilirken hata oluştu: ', error)
    throw error
  }
}

const onCellPrepared = (e: any) => {
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

interface StockPhotoItem { name: string; url: string; size: number; index: number }
const photo = ref<File | null>(null)
const photos = ref<StockPhotoItem[]>([])
const preview = ref('')
const raf = ref('')
const fileInput = ref()
const selectedOption = ref([])
const depolar = ref([])

const openCamera = () => {
  fileInput.value?.$el?.querySelector('input[type=file]')?.click()
}

const rafKaydet = async (itemID: any) => {
  try {
    const veriler = {
      depoID: selectedOption.value,
      raf: raf.value,
      itemID: itemID,
    }

    await axios.post('/api/rafgir', veriler)

    notify('Başarıyla kaydedildi.', 'success', 2000)
  }
  catch (error) {
    console.error('Kayıt sırasında hata oluştu:', error)
  }
}


const DepolariAl = async () => {
  try {
    const response = await axios.get('/api/depolarial', {
      params: {
      }
    })

    depolar.value = response.data.depolar
    // console.log(depolar.value)
  }
  catch (error) {
    console.error('Veri çekilirken hata oluştu: ', error)
  }
}

const uploadPhoto = async (itemID: any) => {
  if (!photo.value) {
    notify('Fotoğraf seçiniz.', 'warning', 2000)
    return
  }
  if (!formDatam.value.urunKodu) {
    notify('Ürün kodu yok.', 'error', 2000)
    return
  }
  const formData = new FormData()
  formData.append('photo', photo.value)
  formData.append('itemCode', formDatam.value.urunKodu)
  try {
    await axios.post('/api/stok-foto/upload', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
    notify('Fotoğraf yüklendi', 'success', 1500)
    photo.value = null
    preview.value = ''
    fetchPhotos()
  } catch (error) {
    console.error(error)
    notify('Yükleme hatası', 'error', 2000)
  }
}

watch(photo, (newPhoto) => {
  if (newPhoto) {
    preview.value = URL.createObjectURL(newPhoto)
  }
})

const fetchPhotos = async () => {
  if (!formDatam.value.urunKodu) { photos.value = []; return }
  try {
    const { data } = await axios.get('/api/stok-foto/list', { params: { itemCode: formDatam.value.urunKodu } })
    photos.value = data
  } catch (e) {
    console.error('Foto listeleme hata', e)
  }
}

const deletePhoto = async (name: string) => {
  if (!confirm('Silmek istediğinize emin misiniz?')) return
  try {
    await axios.delete('/api/stok-foto/delete', { data: { itemCode: formDatam.value.urunKodu, name } })
    fetchPhotos()
  } catch (e) {
    console.error('Silme hata', e)
  }
}
const previewDialog = ref(false)
const selectedPhoto = ref('')

const previewPhoto = (url: string) => {
  selectedPhoto.value = url
  previewDialog.value = true
}

const formatNumber = number => {
  return new Intl.NumberFormat('tr-TR', { maximumFractionDigits: 1 }).format(
    number,
  )
}
</script>


<template>
  <VRow>
    <VCol md="6" cols="12" class="mx-auto">
      <VCard class="overflow-visible">
        <div class="w-100 sticky-header overflow-hidden rounded-t">
          <div class="d-flex align-center gap-4 flex-wrap bg-custom-background pa-6">
            <div class="d-flex align-center gap-4" style="flex: 1;">
              <AppTextField ref="barkodInput" v-model="formDatam.barkod" placeholder="BARKOD" class="flex-1"
                @keyup.enter="applyCode" :disabled="isLoading" />
              <VBtn :loading="isLoading" :disabled="isLoading || !barkodInput" @click="applyCode">
                Ara
              </VBtn>
            </div>
          </div>
        </div>

        <VCardText>
          <h5 class="text-h5 mb-2">
            Ürün Bilgileri
          </h5>

          <VDivider class="my-2" />

          <AppTextField v-model="formDatam.urunID" label="Ürün ID" class="mb-2" readOnly />
          <AppTextField v-model="formDatam.urunKodu" label="Ürün Kodu" class="mb-2" readOnly />
          <AppTextarea v-model="formDatam.urunAdi" label="Ürün Adı" class="mb-2" auto-grow rows="1" readOnly />
          <AppTextarea v-model="formDatam.urunAdi2" label="Ürün Adı 2" auto-grow rows="1" readOnly />
        </VCardText>
      </VCard>
    </VCol>
    <VCol md="6" cols="12" class="mx-auto">
      <VCard class="overflow-visible">
        <VCardText>
          <div class="w-100 sticky-header overflow-hidden rounded-t">
            <h5 class="text-h5 mb-2">
              Depo Bilgileri
            </h5>


            <DxDataGrid id="gridDepo" ref="dataGridRefD" :data-source="gridData" :show-borders="true"
              :column-auto-width="true" :row-alternation-enabled="true" :allow-column-resizing="true"
              column-resizing-mode="widget" @cell-prepared="onCellPrepared" height="332px">

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
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Genel&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span class="toplam">{{
                formatNumber(formDatam.toplama + formDatam.toplami, 1) }}</span>
            </h5>

          </div>
        </VCardText>
      </VCard>
    </VCol>
    <VCol cols="12" class="mx-auto">
      <VCard class="overflow-visible">
        <VCardText>
          <div class="w-100 sticky-header overflow-hidden rounded-t">
            <h5 class="text-h5 mb-2">
              Sayım Bilgileri
            </h5>

            <DxDataGrid id="gridSayim" :data-source="gridDataSayim" :show-borders="true" column-auto-width="true"
              :row-alternation-enabled="true" :allow-column-resizing="true" column-resizing-mode="widget"
              @cell-prepared="onCellPrepared">

              <DxColumn data-field="id" caption="#" :visible="true" :min-width="50" />
              <DxColumn data-field="qty_prm" caption="Miktar" data-type="number" :visible="true" :min-width="60"
                :format="{ type: 'fixedPoint', precision: 2, thousandsSeparator: ',', }" />
              <DxColumn data-field="note1" caption="Raf Yeri" :visible="true" :min-width="60" />
              <DxColumn data-field="whouse_code" caption="Depo" :visible="true" :min-width="40" />
              <DxColumn data-field="sorumlu" caption="Sorumlu" :visible="true" :min-width="80" />
              <DxColumn data-field="create_date" caption="Sayım Tarihi" :visible="true" :min-width="60" data-type="date"
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
              Toplam: {{ formatNumber(formDatam.toplamSayim) }}
            </h5>

          </div>
        </VCardText>
      </VCard>
    </VCol>
    <VCol v-if="formDatam.urunKodu" cols="12" class="mx-auto">
      <VCard class="overflow-visible">
        <div class="d-flex align-center gap-4 flex-wrap bg-custom-background pa-6">
          <AppSelect v-model="selectedOption" :items="depolar" item-title="description" item-value="whouse_id"
            label="Depo" persistent-hint return-object single-line placeholder="Seçiniz..." variant="outlined" />

          <div class="w-100 sticky-header overflow-hidden rounded-t">
            <div class="d-flex align-center mt-2 gap-4" style="flex: 1;">
              <VTextField v-model="raf" label="Raf Yeri" variant="outlined" />
              <VBtn icon="tabler-device-floppy" rounded @click="rafKaydet(formDatam.urunID)"></VBtn>
            </div>
          </div>
        </div>

      </VCard>
    </VCol>
    <VCol v-if="formDatam.urunKodu" cols="12" class="mx-auto">
      <VCard class="overflow-visible">
        <div class="w-100 sticky-header overflow-hidden rounded-t">
          <div class="d-flex align-center gap-4 flex-wrap bg-custom-background pa-6">
            <div class="d-flex align-center gap-4" style="flex: 1;">
              <!-- Gizli file input -->
              <v-file-input ref="fileInput" v-model="photo" accept="image/*" capture="environment"
                label="Fotoğraf Çek veya Seç" prepend-icon="mdi-camera" />

            </div>
            <div class="d-flex align-center gap-4" style="flex: 1;">
              <!-- Buton ile aç -->
              <VBtn icon="tabler-camera-plus" rounded @click="openCamera"></VBtn>
              <VBtn icon="tabler-upload" rounded @click="uploadPhoto(formDatam.urunID)"></VBtn>
            </div>

          </div>
          <v-img v-if="preview" :src="preview" class="ma-4"></v-img>
        </div>

        <VCardText>
          <h5 class="text-h5 mb-2">
            Ürün Fotoğrafları
          </h5>

          <VDivider class="my-2" />


          <v-row>
            <v-col v-for="p in photos" :key="p.name" cols="12" sm="4" md="3">
              <v-img :src="p.url" max-height="200" style="cursor: pointer; margin-inline-start: -18px;"
                class="rounded-lg" @click="previewPhoto(p.url)" />
              <div class="d-flex align-center mt-1">
                <VBtn icon="tabler-trash" color="error" rounded @click="deletePhoto(p.name)"></VBtn>
                <span class="ms-2 text-caption">{{ p.name }}</span>
              </div>
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




  <!-- Snackbar -->
  <VSnackbar v-model="snackbarVisible" :color="snackbarColor" timeout="3000" location="bottom center">
    {{ snackbarMessage }}
  </VSnackbar>
</template>


<style>
.toplam {
  border: 1px solid rgb(250, 174, 119);
  border-radius: 5px;
  background-color: rgb(255, 249, 216);
  color: black;
  font-weight: bold;
  padding-block: 0;
  padding-inline: 5px;
}
</style>

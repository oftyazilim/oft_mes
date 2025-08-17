<template>
  <div>
    <!-- Ana dialog (v-model=dialog) -->
    <VDialog v-model="dialog" max-width="1000px" persistent>
      <VCard>
        <VCardTitle class="text-h6">Hata Girişi Ekranı</VCardTitle>

        <VCardText class="pt-4">
          <VForm ref="formRef" v-model="formValid" lazy-validation @submit.prevent>
            <VTextField ref="serinoRef" v-model="serino" label="Seri No" variant="outlined"
              :rules="[v => !!v || 'Seri no zorunlu']" class="mb-4" @keydown.enter.prevent="odaklaSonrakiAlan" />
          </VForm>





          <VRow dense>
            <VCol cols="6" class="d-flex pa-1 pb-2">


            </VCol>
            <VCol cols="6" class="d-flex pa-1 pe-2 pb-3">
<DxSelectBox
  :data-source="items"
  :value="sonuc"
  display-expr="title"
  value-expr="value"
  @value-changed="e => sonuc = e.value"
  :height="37"
/>
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
                <VCheckbox v-model="seciliHatalar[hata.kod]" :label="`${hata.kod}: ${hata.aciklama}`" density="compact"
                  hide-details class="py-0 my-0" />
              </VCol>
            </VRow>
          </VContainer>

          <hr />

          <VTextarea label="Hata Özeti" v-model="hataOzet" auto-grow readonly class="mt-4" variant="outlined" />

          <!-- <VCheckbox v-model="sonuc" label="Sonuç Onaylandı" class="mt-4" /> -->
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn text @click="temizle">Vazgeç</VBtn>
          <VBtn :disabled="!formValid" color="primary" @click="onUserConfirmed">Kaydet</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>

  <VSnackbar v-model="snackbar" :timeout="3000" color="success" location="top right">
    {{ snackbarMessage }}
  </VSnackbar>
</template>

<script setup lang="ts">
import { ref, watch, nextTick } from 'vue'
import axios from 'axios'
import notify from "devextreme/ui/notify";
import DxSelectBox from 'devextreme-vue/select-box';
import ArrayStore from 'devextreme/data/array_store';

const serinoRef = ref()
const props = defineProps({
  modelValue: Boolean,
  masterId: Number,
})
const emit = defineEmits(['update:modelValue'])
const snackbar = ref(false)
const snackbarMessage = ref('')

const formRef = ref()
const formValid = ref(false)

const serino = ref('')
const sonuc = ref('')
const hataOzet = ref('')
const dialog = ref(props.modelValue)
watch(() => props.modelValue, val => dialog.value = val)
watch(dialog, val => emit('update:modelValue', val))


const items = [
  { title: '✔️ Onaylandı', value: 'Onaylandı', color: '#d4edda' },
  { title: '❌ Red', value: 'Red', color: '#f8d7da' },
  { title: '⚠️ Şartlı Onay', value: 'Şartlı Onay', color: '#fff3cd' }
]

const data = new ArrayStore({
  data: items,
  key: 'value',
});

const focusSerino = () => {
  nextTick(() => {
    serinoRef.value?.focus()
  })
}

watch(dialog, (val) => {
  if (val) {
    focusSerino()
  }
})

const seciliHatalar = ref<Record<string, boolean>>({})
const userData = useCookie<any>("userData");

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

watch(seciliHatalar, () => {
  const secilenKodlar = Object.entries(seciliHatalar.value)
    .filter(([_, val]) => val)
    .map(([kod]) => {
      const hata = hataGruplari.flatMap(grup => grup.hatalar).find(h => h.kod === kod)
      return hata ? `${kod}: ${hata.aciklama}` : kod
    })
  hataOzet.value = secilenKodlar.join(', ')
}, { deep: true })

const onUserConfirmed = async () => {
  try {
    const secilenKodlar = Object.entries(seciliHatalar.value)
      .filter(([_, val]) => val)
      .map(([kod]) => kod)

    const veri = {
      serino: serino.value,
      hatalar: secilenKodlar,
      sonuc: sonuc.value,
      urun_kontrol_m_id: props.masterId,
      user_id: userData.value.id,
    }

    await axios.post('/api/hata-kaydet', veri)
    notify({ message: 'Kayıt başarılı!', type: "success", displayTime: 2000 })
    temizle()
  } catch (err) {
    console.error(err)
    if (err.response?.data?.message) {
      alert(err.response.data.message)
    } else {
      alert('Kayıt sırasında hata oluştu.')
    }
  }
}

const temizle = () => {
  formRef.value?.reset()
  serino.value = ''
  sonuc.value = false
  hataOzet.value = ''
  seciliHatalar.value = {}
  dialog.value = false
}

const odaklaSonrakiAlan = () => {
  // formda serino'dan sonra gelen checkboxlara ya da bir sonraki mantıklı alana focus atamak için
  // örnek olarak ilk checkboxa odaklanalım
  nextTick(() => {
    const el = document.querySelector('input[type="checkbox"]') as HTMLElement
    el?.focus()
  })
}
</script>

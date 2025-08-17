<template>
  <VDialog v-model="dialog" max-width="400" persistent>
    <VCard>
      <VCardTitle class="text-h6 pb-1">Hata Bildirimi Gönder</VCardTitle>
      <hr>
      <VCardText>
        <VTextField class="pb-4" v-model="form.is_emri_no" label="İş Emri No" variant="outlined" density="compact"
          readonly />

        <VTextField class="pb-4" v-model="form.urun_kodu" label="Ürün Kodu" variant="outlined" density="compact"
          readonly />

        <VTextField class="pb-4" v-model="form.urun_adi" label="Ürün Adı" variant="outlined" density="compact"
          readonly />

        <VSelect class="pb-2 pt-0" v-model="form.hata_sebebi_id" :items="hataSebepleri" item-title="tanim"
          item-value="id" label="Hata Sebebi" density="compact" outlined required />

        <VTextarea class="py-2" v-model="form.not" label="Açıklama / Not" density="compact" outlined rows="4" />

        <VSelect class="pa-0 pt-2" v-model="form.alicilar" :items="kullanicilar" item-title="email" item-value="email"
          label="Gönderilecek Kişiler" multiple chips density="compact" outlined />
      </VCardText>

      <VCardActions class="justify-end">
        <VBtn @click="dialog = false" variant="text">İptal</VBtn>
        <VBtn color="primary" @click="gonder" :loading="loading">Gönder</VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import notify from 'devextreme/ui/notify'

const dialog = ref(false)

const hataSebepleri = ref([])
const kullanicilar = ref([])

const form = ref({
  hata_sebebi_id: null,
  not: '',
  alicilar: [],
  gonderen_id: 13, // Oturum açan kullanıcıdan alınmalı
  is_emri_no: '',
  urun_kodu: '',
  urun_adi: '',
})

const loading = ref(false)

onMounted(async () => {
  const [sebepler, kisiler] = await Promise.all([
    axios.get('/api/hata-sebepleri'),
    axios.get('/api/mail-alicilari'),
  ])
  hataSebepleri.value = sebepler.data
  kullanicilar.value = kisiler.data
})

const gonder = async () => {
  if (!form.value.hata_sebebi_id) {
    notify({ message: 'Lütfen bir hata sebebi seçin.', type: 'warning', displayTime: 3000 })
    return
  }
  if (!form.value.alicilar.length) {
    notify({ message: 'Lütfen en az bir alıcı seçin.', type: 'warning', displayTime: 3000 })
    return
  }
  loading.value = true
  try {
    await axios.post('/api/hata-bildir', form.value)
    notify({ message: 'Mail başarıyla gönderildi', type: 'success', displayTime: 3000 })
    dialog.value = false
  } catch (err) {
    notify({ message: 'Gönderim sırasında hata oluştu', type: 'error', displayTime: 3000 })
    console.error(err)
  } finally {
    loading.value = false
  }
}

const acDialog = (params: {
  isEmriNo: string
  urunKodu: string
  urunAdi: string
}) => {
  form.value.is_emri_no = params.isEmriNo
  form.value.urun_kodu = params.urunKodu
  form.value.urun_adi = params.urunAdi
  dialog.value = true
}

defineExpose({ dialog, acDialog })
</script>

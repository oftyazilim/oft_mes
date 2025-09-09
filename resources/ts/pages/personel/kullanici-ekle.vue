<script setup lang="ts">
import { emailValidator, requiredValidator } from '@core/utils/validators';
import axios from 'axios';
import { nextTick, onMounted, reactive, ref, watch } from 'vue';
import type { VForm } from 'vuetify/components/VForm';

interface Emit {
  (e: 'update:isDrawerOpen', value: boolean): void
  (e: 'submit', value: Details): void

  // (e: 'userData', value: UserProperties): void
}

const emit = defineEmits<Emit>()

const roller = ref<{ name: string }[]>([]);
const isFormValid = ref(false)
const refForm = ref<VForm>()
const name = ref('')
const email = ref<any>(null)
const unvan = ref<any>(null)
const ismerkezi = ref<any>([]);
const istasyon = ref<any>([]);
const proses = ref<any>(null)
const tip = ref<any>(null)
const selectedRoles = ref<string[]>([]);
// İş Merkezleri tek seçim: scalar değer tut
const secimMerkezler = ref<number | string | null>(null);
const secimIstasyonlar = ref<Array<number | string>>([]);
const merkezler = ref<{ is_merkezi_id: number | string; mrk_adi: any }[]>([])
const istasyonlar = ref<{ istasyon_id: string | number; ist_adi: any }[]>([])
const roles = ref('')
const degisim = ref('false')

interface Details {
  id: number;
  name: string;
  email: string;
  roles: string;
  proses: string;
  tip: string;
  unvan: string;
  ismerkezi: string;
  istasyon: string;
}

interface Props {
  isDrawerOpen: boolean
  userData?: Details
}

const props = withDefaults(defineProps<Props>(), {
  userData: () => ({
    id: 0,
    name: '',
    email: '',
    roles: '',
    proses: '',
    tip: '',
    unvan: '',
    ismerkezi: '',
    istasyon: '',
  }),
})

const items = ref([
  { name: 'AR-GE' },
  { name: 'BAKIM' },
  { name: 'BOYAHANE' },
  { name: 'CAM YAPIŞTIRMA' },
  { name: 'DEPO' },
  { name: 'DUVAR TİPİ MONTAJ' },
  { name: 'DİKİLİ TİP MONTAJ' },
  { name: 'FAN-PDU MONTAJ' },
  { name: 'FİNANS' },
  { name: 'FO-CU MEKANİK MONTAJ' },
  { name: 'GENEL' },
  { name: 'IP PDU MONTAJ' },
  { name: 'İNSAN KAYNAKLARI' },
  { name: 'KALİTE' },
  { name: 'MEKANİK' },
  { name: 'MONTAJ' },
  { name: 'MUHASEBE' },
  { name: 'OUTDOOR ÜRÜN MONTAJ' },
  { name: 'OUTDOOR ÖZEL ÜRÜN MONTAJ' },
  { name: 'PAKETLEME' },
  { name: 'PASİF AKSESUAR MONTAJ' },
  { name: 'PLANLAMA' },
  { name: 'SERİGRAFİ' },
  { name: 'SEVKİYAT' },
  { name: 'SATINALMA' },
  { name: 'SATIŞ' },
])

const getIstasyonlar = async () => {
  if (secimMerkezler.value === null || secimMerkezler.value === '') {
    istasyonlar.value = [{ istasyon_id: '', ist_adi: 'Seçim yok' }]
    return
  }

  // console.log('İstasyonlar alınıyor', secimMerkezler.value)
  try {
    const { data } = await axios.get('/api/istasyonal', {
      params: {
        ismerkezi: String(secimMerkezler.value ?? ''),
      },
    })

    // Normalize response to an array (supports nested { data: { istasyonlar } })
    const root: any = data as any
    const candidate = root?.istasyonlar ?? root?.data?.istasyonlar ?? root
    const rawList = Array.isArray(candidate) ? candidate : []

    // Verileri sayıya çevirerek at (eğer ID'ler string ise) ve alanları standardize et
  istasyonlar.value = rawList.map((item: any) => {
      const idRaw = item?.istasyon_id ?? item?.id ?? item
      const idNum = typeof idRaw === 'number' ? idRaw : Number(idRaw)
      return {
        istasyon_id: Number.isFinite(idNum) ? idNum : (typeof idRaw === 'string' ? idRaw : 0),
        ist_adi: item?.ist_adi ?? item?.name ?? String(idRaw ?? ''),
      }
    }).filter((i: any) => i.istasyon_id !== 0 || (typeof i.istasyon_id === 'string' && i.istasyon_id !== ''))
  // Listenin başına boş seçenek ekle
  istasyonlar.value.unshift({ istasyon_id: '', ist_adi: 'Seçim yok' })

    // Düzenleme modundaysa, istasyon seçimlerini yükle
    if (props.userData?.id && props.userData.istasyon) {
      const gelenIDler = props.userData.istasyon
        .split(',')
        .map(id => Number(id.trim()))

      // istasyonlar.value içindeki ID'leri alın
      const mevcutIDler = istasyonlar.value.map(i => i.istasyon_id)

      // Sadece var olanları ata
      secimIstasyonlar.value = gelenIDler.filter(id => mevcutIDler.includes(id))
    }


  } catch (error: any) {
    console.error('İstasyonlar alınırken hata oluştu:', error)
  }
}

const getMerkezler = async () => {
  try {
    const { data } = await axios.get('/api/merkezal')
    const root: any = data as any
    const candidate = root?.merkezler ?? root?.data?.merkezler ?? root
    const rawList = Array.isArray(candidate) ? candidate : []
    const mapped = rawList.map((item: any) => {
      const idRaw = item?.is_merkezi_id ?? item?.id ?? item
      const idNum = typeof idRaw === 'number' ? idRaw : Number(idRaw)
      return {
        is_merkezi_id: Number.isFinite(idNum) ? idNum : (typeof idRaw === 'string' ? idRaw : 0),
        mrk_adi: item?.mrk_adi ?? item?.name ?? String(idRaw ?? ''),
      }
    }).filter((i: any) => i.is_merkezi_id !== 0 || (typeof i.is_merkezi_id === 'string' && i.is_merkezi_id !== ''))
    merkezler.value = [{ is_merkezi_id: '', mrk_adi: 'Seçim yok' }, ...mapped]

    if (props.userData?.id) {
      const first = (props.userData.ismerkezi || '')
        .split(',')
        .map(s => s.trim())
        .filter(Boolean)[0]
      secimMerkezler.value = first ? (isNaN(Number(first)) ? first : Number(first)) : null
      getIstasyonlar();
    }
  } catch (error: any) {
    console.error('Veri çekilirken hata oluştu: ', error)
  }
}

// Reaktif userData nesnesi
const userData = reactive({ ...props.userData })

// props.userData değiştiğinde userData'i güncelle
watch(() => props.userData, (newVal) => {
  Object.assign(userData, newVal)
})

watch(
  () => props.userData,
  (newData) => {
    if (newData && newData.id > 0) {
      name.value = newData.name || '';
      email.value = newData.email || '';
      selectedRoles.value = newData.roles != '' ? newData.roles.split(',').map(role => role.trim()) : [];
      proses.value = newData.proses || '';
      tip.value = newData.tip || '';
      unvan.value = newData.unvan || '';
      ismerkezi.value = newData.ismerkezi || '';
      istasyon.value = newData.istasyon || '';
      {
        const first = (newData.ismerkezi || '')
          .split(',')
          .map(s => s.trim())
          .filter(Boolean)[0]
        secimMerkezler.value = first ? (isNaN(Number(first)) ? first : Number(first)) : null
      }
      secimIstasyonlar.value = newData.istasyon != '' ? newData.istasyon.split(',').map(r => Number(r.trim())) : [];
    } else {
      selectedRoles.value = [];
      secimMerkezler.value = null;
      secimIstasyonlar.value = [];
      Object.assign(userData, {
        id: 0,
        name: '',
        email: '',
        roles: null,
        proses: '',
        tip: '',
        unvan: '',
        ismerkezi: null,
        istasyon: null,
      });
    }
  },
  { immediate: true } // İlk başta da çalıştır
);

onMounted(() => {
  fetchRoles();
  getMerkezler();
})

watch(secimMerkezler, (yeniDeger) => {
  if (yeniDeger !== null && yeniDeger !== '') {
    secimIstasyonlar.value = []
    istasyon.value = null
    getIstasyonlar()
  }
  else {
    istasyonlar.value = [{ istasyon_id: '', ist_adi: 'Seçim yok' }]
    secimIstasyonlar.value = []
    istasyon.value = null
  }
})

// Çoklu istasyon seçiminde "Seçim yok" (boş) ile diğer değerlerin birlikte seçilmesini engelle
watch(secimIstasyonlar, (yeni) => {
  if (Array.isArray(yeni) && yeni.includes('')) {
    // 'Seçim yok' seçildiyse, diğerlerini temizleyip yalnızca boş bırak
    secimIstasyonlar.value = ['']
  }
})

const fetchRoles = async () => {
  try {
    const { data } = await axios.get('/api/roles/available')
    const list = Array.isArray((data as any)?.roles)
      ? (data as any).roles
      : Array.isArray(data)
        ? (data as any)
        : []
    roller.value = list
      .map((r: any) => (typeof r === 'string' ? { name: r } : { name: String(r?.name ?? '') }))
      .filter((r: any) => r.name)
  } catch (error) {
    console.error('Roller alınırken hata oluştu:', error)
  }
}

// 👉 drawer close
const closeNavigationDrawer = () => {
  emit('update:isDrawerOpen', false)

  nextTick(() => {
    refForm.value?.reset()
    refForm.value?.resetValidation()
  })
}

const onSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {


      if (props.userData?.id) {
        updateUser();
      } else {
        saveUser();
      }



      emit('update:isDrawerOpen', false)
      nextTick(() => {
        refForm.value?.reset()
        refForm.value?.resetValidation()
      })
    }
  })
}

// Kaydetme işlemi
const saveUser = async () => {
  try {
    const response = await axios.post('/api/users', {
      name: userData.name,
      email: userData.email,
      password: 'pass1234',
      roles: selectedRoles.value,
      proses: userData.proses,
      tip: userData.tip,
      unvan: userData.unvan,
      ismerkezi: String(secimMerkezler.value ?? ''),
      istasyon: toCsv(secimIstasyonlar.value),
    });
    // Kaydetme sonrası kullanıcı güncelleme olayını yayınla
    try {
      const newId = (response as any)?.data?.id ?? 0
      window.dispatchEvent(new CustomEvent('user-updated', { detail: { id: newId, roles: selectedRoles.value } }))
    } catch { /* noop */ }
  } catch (error: any) {
    if (error?.response?.data?.errors) {
      console.error('Doğrulama hataları:', error.response.data.errors);
    } else {
      console.error('Beklenmeyen hata:', error);
    }
  }

  emit('update:isDrawerOpen', false);
};

// Kullanıcıyı güncelleme işlemi
const updateUser = async () => {
  try {
    const response = await axios.put(`/api/users/${userData.id}`, {
      name: userData.name,
      email: userData.email,
      roles: selectedRoles.value,
      proses: userData.proses,
      tip: userData.tip,
      unvan: userData.unvan,
      ismerkezi: String(secimMerkezler.value ?? ''),
      istasyon: toCsv(secimIstasyonlar.value),
    });
    // Güncelleme sonrası kullanıcı güncelleme olayını yayınla
    try {
      window.dispatchEvent(new CustomEvent('user-updated', { detail: { id: userData.id, roles: selectedRoles.value } }))
    } catch { /* noop */ }
  } catch (error: any) {
    console.error("Kullanıcı güncellenirken hata oluştu:", error?.response?.data ?? error);
  }
};

watch(merkezler, (yeniMerkezler) => {
  if (props.userData?.id && (secimMerkezler.value === null || secimMerkezler.value === '')) {
    const first = (props.userData.ismerkezi || '')
      .split(',')
      .map(s => s.trim())
      .filter(Boolean)[0]
    secimMerkezler.value = first ? (isNaN(Number(first)) ? first : Number(first)) : null
  }
})

const handleDrawerModelValueUpdate = (val: boolean) => {
  emit('update:isDrawerOpen', val)
}

// Tek seçimli iş merkezi değişince istasyon listesini yenile
const onMerkezChanged = (val: any) => {
  secimMerkezler.value = val
  secimIstasyonlar.value = []
  istasyon.value = null
  getIstasyonlar()
}

// Seçimleri CSV'ye çevir (tekil/multiple/Set korumalı)
const toCsv = (val: any): string => {
  if (Array.isArray(val)) return val.filter(v => v !== null && v !== '').join(',')
  if (val == null) return ''
  if (val instanceof Set) return Array.from(val).join(',')
  if (typeof val === 'object' && Array.isArray((val as any).value)) return (val as any).value.join(',')
  return String(val)
}
</script>

<template>
  <VDialog temporary :width="800" location="end" class="scrollable-content" :model-value="props.isDrawerOpen"
    @update:model-value="handleDrawerModelValueUpdate">
    <!-- 👉 Title -->
    <!-- <AppDrawerHeaderSection :title="props.userData.id > 0 ? 'Kullanıcı Düzenle' : 'Yeni Kullanıcı Ekle'"
      @cancel="closeNavigationDrawer" /> -->
    <VCard class="pa-2 pa-sm-10">

      <VCardItem class="text-center">
        <VCardTitle>
          <h4 class="text-h4 mb-2">
            {{ props.userData.id > 0 ? 'Kullanıcı Düzenle' : 'Yeni Kullanıcı Ekle' }}
          </h4>
        </VCardTitle>
      </VCardItem>

      <VDivider />

      <VCard flat>
        <VCardText>
          <!-- 👉 Form -->
          <!-- <VForm ref="refForm" v-model="isFormValid" @submit.prevent="onSubmit"> -->
          <VForm ref="refForm" lazy-validation @submit.prevent="onSubmit">
            <VRow>
              <!-- 👉 Full name -->
              <VCol cols="6">
                <AppTextField v-model="userData.name" :rules="[requiredValidator]" label="Adı Soyadı"
                  placeholder="Ali VELİ" />
              </VCol>

              <!-- 👉 Email -->
              <VCol cols="6">
                <AppTextField v-model="userData.email" :rules="[requiredValidator, emailValidator]" label="Email"
                  placeholder="aliveli@email.com" />
              </VCol>

              <!-- 👉 ünvan -->
              <VCol cols="6">
                <AppTextField v-model="userData.unvan" :rules="[requiredValidator]" label="Ünvan"
                  placeholder="Yönetici" />
              </VCol>


              <!-- 👉 Proses -->
              <VCol cols="6">
                <AppSelect v-model="userData.proses" :rules="[requiredValidator]" label="Görev Yeri (Proses)"
                  :items="items" item-title="name" />
              </VCol>

              <!-- 👉 tip -->
              <VCol cols="6">
                <AppSelect v-model="userData.tip" :rules="[requiredValidator]" label="Kademe"
                  :items="['YÖNETİM', 'ÜRETİM']" />
              </VCol>

              <!-- 👉 Roller -->
              <VCol cols="6">
                <AppSelect v-model="selectedRoles" :rules="[requiredValidator]" :items="roller" item-title="name"
                  item-value="name" :return-object="false" :menu-props="{ maxHeight: '400' }" label="Roller" multiple
                  persistent-hint placeholder="Rol seçiniz..." chips closable-chips />
              </VCol>

              <!-- 👉 İş Merkezleri -->
              <VCol cols="6">
                <AppSelect v-model="secimMerkezler" :items="merkezler" item-title="mrk_adi"
                  :menu-props="{ maxHeight: '400' }" label="İş Merkezleri" persistent-hint item-value="is_merkezi_id"
                  placeholder="Rol seçiniz..." @update:model-value="onMerkezChanged" />{{ secimMerkezler }}
              </VCol>
              <!-- @update:modelValue="getIstasyonlar"  -->
              <!-- 👉 İş İstasyonları -->
              <VCol cols="6">
                <AppSelect v-model="secimIstasyonlar" :items="istasyonlar" item-title="ist_adi"
                  :menu-props="{ maxHeight: '400' }" label="İş İstasyonları" persistent-hint item-value="istasyon_id"
                  placeholder="Rol seçiniz..." />{{ secimIstasyonlar }}
              </VCol>

              <!-- 👉 Submit and Cancel -->
              <VCol cols="12" class="text-center mt-2">
                <VBtn type="submit" class="me-3">
                  Kaydet
                </VBtn>
                <VBtn type="reset" variant="tonal" color="error" @click="closeNavigationDrawer">
                  Vazgeç
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCard>

  </VDialog>
</template>

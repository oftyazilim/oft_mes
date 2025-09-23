<template>
  <div>
    <VBtn v-if="!iconMode" class="feedback-fab" color="primary" elevation="8" icon @click="open = true">
      <VIcon icon="tabler-message-dots" />
    </VBtn>
    <IconBtn v-else @click="open = true" :title="'Geri Bildirim'">
      <VIcon icon="tabler-message-dots" />
    </IconBtn>

    <VDialog v-model="open" max-width="580">
      <VCard>
        <VCardTitle class="d-flex align-center justify-space-between">
          <div class="d-flex align-center gap-2">
            <VIcon color="primary" icon="tabler-stars" />
            <span>Geri Bildirim</span>
          </div>
          <VBtn icon variant="text" @click="open = false"><VIcon icon="tabler-x" /></VBtn>
        </VCardTitle>
        <VCardText>
          <VAlert type="info" variant="tonal" class="mb-4">
            Görüş, öneri veya bir hatayı bizimle paylaşın. Ekran görüntüsü ekleyebilir, isterseniz e‑postanızı bırakabilirsiniz.
          </VAlert>

          <VRow @paste="onPaste">
            <VCol cols="12" md="6">
              <VSelect v-model="form.category" :items="kategoriler" label="Kategori *" :rules="[v => !!v || 'Kategori zorunludur']" />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField v-model="form.email" label="E‑posta (opsiyonel)" type="email" clearable />
            </VCol>
            <VCol cols="12">
              <div class="mb-1 text-medium-emphasis">Memnuniyet</div>
              <div class="stars">
                <VBtn v-for="n in 5" :key="n" icon variant="text" :color="n <= (form.rating||0) ? 'warning' : ''"
                      @click="form.rating = n">
                  <VIcon :icon="n <= (form.rating||0) ? 'tabler-star-filled' : 'tabler-star'" size="28" />
                </VBtn>
              </div>
            </VCol>
            <VCol cols="12">
              <VTextarea v-model="form.message" auto-grow rows="4" counter="5000" label="Mesajınız" />
            </VCol>
            <VCol cols="12" md="8">
              <VFileInput
                v-model="screenshot"
                accept="image/*"
                label="Ekran görüntüsü (opsiyonel) — panodan yapıştırabilirsiniz"
                prepend-icon=""
                readonly
                @click.stop.prevent
              >
                <template #prepend>
                  <IconBtn color="primary" @click.stop="chooseFile" :title="'Görsel seç'">
                    <VIcon icon="tabler-camera" />
                  </IconBtn>
                </template>
              </VFileInput>
              <input ref="fileInputRef" type="file" accept="image/*" class="d-none" @change="onFileChange" />

              <!-- Küçük önizleme ve dosya adı -->
              <div v-if="screenshot" class="mt-2 d-flex align-center gap-2">
                <img v-if="previewUrl" :src="previewUrl" alt="Önizleme" class="screenshot-thumb" />
                <div class="text-caption">{{ screenshot?.name || 'gorsel' }}</div>
                <VBtn size="small" variant="text" color="error" @click="clearScreenshot">
                  <VIcon icon="tabler-x" class="me-1" /> Kaldır
                </VBtn>
              </div>
            </VCol>
            <VCol cols="12" md="4" class="d-flex align-end">
              <VBtn block variant="tonal" color="secondary" @click="takeScreenshot">Anlık ekranı ekle</VBtn>
            </VCol>
          </VRow>
        </VCardText>
        <VCardActions class="justify-end">
          <VBtn variant="text" @click="open = false">İptal</VBtn>
          <VBtn color="primary" :loading="loading" @click="submit">Gönder</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Başarı Snackbar -->
    <VSnackbar v-model="snack.success" color="success" timeout="3000" location="bottom right">
      Teşekkürler! Geri bildiriminiz alındı.
    </VSnackbar>
    <!-- Hata Snackbar -->
    <VSnackbar v-model="snack.error" color="error" timeout="5000" location="bottom right">
      {{ errorText || 'Gönderim sırasında bir hata oluştu.' }}
    </VSnackbar>
  </div>
  
 </template>

<script setup lang="ts">
import axios from 'axios';
import html2canvas from 'html2canvas';
import { onMounted, reactive, ref, watch } from 'vue';

// Navbar içinde ikon olarak kullanılabilmesi için mod
const props = defineProps<{ iconMode?: boolean }>()

const open = ref(false)
const loading = ref(false)
const snack = reactive({ success: false, error: false })
const errorText = ref('')
const kategoriler = [
  { title: 'Öneri', value: 'oneri' },
  { title: 'Hata', value: 'hata' },
  { title: 'İstek', value: 'istek' },
  { title: 'Diğer', value: 'diger' },
]

const form = reactive<{ category?: string; rating?: number; email?: string; message: string }>({ message: '' })
const screenshot = ref<File | null>(null)
const fileInputRef = ref<HTMLInputElement | null>(null)
const previewUrl = ref<string | null>(null)

// Otomatik e-posta doldurma: oturum açmış kullanıcının e-postasını al
onMounted(async () => {
  try {
    const { data } = await axios.get('/api/auth/user')
    if (data?.email) {
      form.email = String(data.email)
    }
  } catch (e) {
    // sessiz geç: anonim / erişim yok
  }
})

const takeScreenshot = async () => {
  try {
    const canvas = await html2canvas(document.body, {
      useCORS: true,
      allowTaint: true,
      backgroundColor: '#ffffff',
      foreignObjectRendering: true,
      logging: false,
      scale: window.devicePixelRatio || 1,
      onclone: (doc) => {
        // Icon fontlarının yüklenmesini garanti altına almak için
        // head içine mevcut stylesheet linklerini koruyoruz.
        // Bazı durumlarda svg ikonlar için inline render daha iyi sonuç verir.
      },
    })
    const blob: Blob | null = await new Promise(resolve => canvas.toBlob(b => resolve(b), 'image/png'))
    if (blob) {
      screenshot.value = new File([blob], 'screenshot.png', { type: 'image/png' })
    }
  } catch (e) {
    // sessiz geç
  }
}

// Panodan resim yapıştırma
const onPaste = async (ev: ClipboardEvent) => {
  try {
    const items = ev.clipboardData?.items
    if (!items) return
    for (const it of items as any) {
      if (it.type && it.type.startsWith('image/')) {
        const blob = it.getAsFile?.() || it.getAsFile?.call(it)
        if (blob) {
          screenshot.value = new File([blob], 'pasted.png', { type: blob.type || 'image/png' })
          break
        }
      }
    }
  } catch (e) {
    // sessiz geç
  }
}

const chooseFile = () => {
  fileInputRef.value?.click()
}

const onFileChange = (ev: Event) => {
  const input = ev.target as HTMLInputElement
  const file = input.files?.[0]
  if (file) {
    screenshot.value = file
  }
  // aynı dosyayı tekrar seçebilmek için input'u temizle
  if (input) input.value = ''
}

const submit = async () => {
  if (!form.category) {
    snack.error = true
    errorText.value = 'Lütfen kategori seçiniz.'
    return
  }
  if (!form.message || form.message.trim().length < 5) return
  loading.value = true
  try {
    const fd = new FormData()
    if (form.category) fd.append('category', form.category)
    if (form.rating) fd.append('rating', String(form.rating))
    if (form.email) fd.append('email', form.email)
    fd.append('message', form.message)
    fd.append('page_url', window.location.href)
    if (screenshot.value) fd.append('screenshot', screenshot.value)

    await axios.post('/api/feedback', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    // teşekkür bildirimi
    open.value = false
    form.message = ''
    form.rating = undefined
    form.category = undefined
    form.email = ''
    screenshot.value = null
    snack.success = true
  } catch (err: any) {
    errorText.value = err?.response?.data?.message || err?.message || ''
    snack.error = true
  } finally {
    loading.value = false
  }
}

// Önizleme için object URL üret ve sızıntıyı engelle
watch(screenshot, (file, old) => {
  // önceki URL'yi bırak
  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value)
    previewUrl.value = null
  }
  if (file instanceof File) {
    try {
      previewUrl.value = URL.createObjectURL(file)
    } catch {}
  }
})

const clearScreenshot = () => {
  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value)
    previewUrl.value = null
  }
  screenshot.value = null
}
</script>

<style scoped>
.feedback-fab {
  position: fixed;
  z-index: 999;
  inset-block-end: 22px;
  inset-inline-end: 22px;
}

.stars {
  display: flex;
  gap: 2px;
}
.d-none { display: none; }

.screenshot-thumb {
  border: 1px solid rgba(0, 0, 0, 12%);
  border-radius: 6px;
  block-size: 42px;
  inline-size: 42px;
  object-fit: cover;
}
</style>

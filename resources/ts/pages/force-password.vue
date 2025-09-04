<script setup lang="ts">
import axios from 'axios'

definePage({ meta: { layout: 'blank' } })

const router = useRouter()
const changing = ref(false)
const form = ref({ current_password: 'halapass1234', password: '', password_confirmation: '' })
const showNew = ref(false)
const showConf = ref(false)
const snack = ref<{ show: boolean; color: string; text: string }>({ show: false, color: 'info', text: 'Lütfen şifrenizi değiştirin.' })

const req = (v: string) => !!v || 'Zorunlu alan'
const min8 = (v: string) => (v?.length ?? 0) >= 8 || 'En az 8 karakter'
const matches = (v: string) => v === form.value.password || 'Şifreler uyuşmuyor'

const submit = async () => {
  if (!form.value.password || form.value.password !== form.value.password_confirmation) return
  try {
    changing.value = true
    await axios.post('/api/auth/change-password', form.value)
    snack.value = { show: true, color: 'success', text: 'Şifre güncellendi, girişe yönlendiriliyorsunuz.' }
    setTimeout(() => router.push('/login'), 900)
  } catch (e: any) {
    const msg = e?.response?.data?.message || 'İşlem sırasında hata oluştu'
    snack.value = { show: true, color: 'error', text: String(msg) }
  } finally {
    changing.value = false
  }
}

const cancel = async () => {
  // Vazgeçerse hemen logout
  try { await axios.get('/api/auth/logout') } catch {}
  const accessToken = useCookie('accessToken'); accessToken.value = null
  const userData = useCookie('userData'); userData.value = null
  const userAbilityRules = useCookie('userAbilityRules'); userAbilityRules.value = null
  router.push('/login')
}
</script>

<template>
  <VRow no-gutters class="auth-wrapper bg-surface">
    <VCol cols="12" md="4" class="auth-card-v2 d-flex align-center justify-center mx-auto">
      <VCard flat :max-width="500" class="mt-12 mt-sm-0 pa-4">
        <VCardTitle class="text-center">Şifre Değiştirme Zorunlu</VCardTitle>
        <VCardText class="text-center">Varsayılan şifre ile giriş yaptınız. Yeni bir şifre belirlemeden devam edemezsiniz.</VCardText>
        <VCardText>
          <VTextField
            v-model="form.password"
            label="Yeni Şifre"
            :type="showNew ? 'text' : 'password'"
            :disabled="changing"
            autocomplete="new-password"
            :rules="[req, min8]"
            :append-inner-icon="showNew ? 'tabler-eye-off' : 'tabler-eye'"
            @click:append-inner="showNew = !showNew"
          />
          <VTextField
            class="mt-2"
            v-model="form.password_confirmation"
            label="Yeni Şifre (Tekrar)"
            :type="showConf ? 'text' : 'password'"
            :disabled="changing"
            autocomplete="new-password"
            :rules="[req, matches]"
            :append-inner-icon="showConf ? 'tabler-eye-off' : 'tabler-eye'"
            @click:append-inner="showConf = !showConf"
          />
        </VCardText>
        <VCardActions class="justify-end">
          <VBtn variant="tonal" color="secondary" @click="cancel" :disabled="changing">Vazgeç</VBtn>
          <VBtn color="primary" @click="submit" :loading="changing">Kaydet</VBtn>
        </VCardActions>
      </VCard>
    </VCol>
  </VRow>
  <VSnackbar v-model="snack.show" :color="snack.color" timeout="2500">{{ snack.text }}</VSnackbar>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";
</style>

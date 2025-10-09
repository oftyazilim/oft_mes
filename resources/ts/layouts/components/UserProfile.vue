<script setup lang="ts">
import { purgeAuthCookies } from '@/utils/cookies'
import axios from 'axios'
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'

const router = useRouter()
const ability = useAbility()

// TODO: Get type from backend
const userData = useCookie<any>('userData')

const displayName = computed(() => {
  const u = userData.value
  if (!u) return ''
  if (typeof u === 'string') return u
  // Prefer name, fall back to fullName/username/email
  return (u.name || u.fullName || u.username || u.email || '') as string
})

const logout = async () => {
  // Çakışan path'li eski cookie'leri agresif temizle (Chromium gölgelemesini önle)
  try { purgeAuthCookies() } catch { }

  // Remove "accessToken" from cookie
  useCookie('accessToken').value = null

  // Remove "userData" from cookie
  userData.value = null
  try {
    const g: any = window as any
    if (g.__appTitleRef) g.__appTitleRef.value = null
  } catch { /* ignore */ }

  // Başlığı anında placeholder'a çek (yalnızca theme/layout; sekme başlığı sayfadan)
  try {
    const { themeConfig, layoutConfig } = await import('@themeConfig')
      ; (themeConfig as any).app.title = 'firma adı'
      ; (layoutConfig as any).app.title = 'firma adı'
  } catch { }

  // Redirect to login page
  await router.push('/login')

  // ℹ️ We had to remove abilities in then block because if we don't nav menu items mutation is visible while redirecting user to login page
  // Remove "userAbilities" from cookie
  useCookie('userAbilityRules').value = null

  // Reset ability to initial ability
  ability.update([])
}

const changeDialog = ref(false)
const form = ref({ current_password: '', password: '', password_confirmation: '' })
const changing = ref(false)
const showCur = ref(false)
const showNew = ref(false)
const showConf = ref(false)
const snack = ref<{ show: boolean; color: string; text: string }>({ show: false, color: 'success', text: '' })

// Basit validasyon kuralları
const req = (v: string) => !!v || 'Zorunlu alan'
const min8 = (v: string) => (v?.length ?? 0) >= 8 || 'En az 8 karakter'
const different = (v: string) => v !== form.value.current_password || 'Yeni şifre mevcut şifreyle aynı olamaz'
const matches = (v: string) => v === form.value.password || 'Şifreler uyuşmuyor'

const openChangePassword = () => {
  form.value = { current_password: '', password: '', password_confirmation: '' }
  changeDialog.value = true
}

const submitChangePassword = async () => {
  if (!form.value.current_password || !form.value.password || form.value.password !== form.value.password_confirmation)
    return
  try {
    changing.value = true
    await axios.post('/api/auth/change-password', form.value)
    snack.value = { show: true, color: 'success', text: 'Şifre güncellendi. Lütfen tekrar giriş yapın.' }
    changeDialog.value = false
    setTimeout(() => { logout() }, 800)
  } catch (e) {
    const msg = (e as any)?.response?.data?.message || 'İşlem sırasında hata oluştu'
    snack.value = { show: true, color: 'error', text: String(msg) }
  } finally {
    changing.value = false
  }
}

const userProfileList = [
  { type: 'divider' },
  { type: 'navItem', icon: 'tabler-user', title: 'Kullanıcı Profili', to: { name: 'apps-user-view-id', params: { id: 21 } } },
  { type: 'navItem', icon: 'tabler-settings', title: 'Ayarlar', to: { name: 'pages-account-settings-tab', params: { tab: 'account' } } },
  { type: 'navItem', icon: 'tabler-file-dollar', title: 'Faturalama Planı', to: { name: 'pages-account-settings-tab', params: { tab: 'billing-plans' } }, badgeProps: { color: 'error', content: '4' } },
  { type: 'divider' },
  { type: 'navItem', icon: 'tabler-currency-dollar', title: 'Fiyatlandırma', to: { name: 'pages-pricing' } },
  { type: 'navItem', icon: 'tabler-question-mark', title: 'SSS', to: { name: 'pages-faq' } },
]
</script>

<template>
  <div class="d-flex align-center gap-2">
    <VBadge v-if="userData" dot bordered location="bottom right" offset-x="1" offset-y="2" color="success">
      <VAvatar size="38" class="cursor-pointer" :color="!(userData && userData.avatar) ? 'primary' : undefined"
        :variant="!(userData && userData.avatar) ? 'tonal' : undefined">
        <VImg v-if="userData && userData.avatar" :src="userData.avatar" />
        <VIcon v-else icon="tabler-user" />

        <!-- SECTION Menu -->
        <VMenu activator="parent" width="240" location="bottom end" offset="12px">
          <VList>
            <VListItem>
              <div class="d-flex gap-2 align-center">
                <!--   <VListItemAction>
             <VBadge
                  dot
                  location="bottom right"
                  offset-x="3"
                  offset-y="3"
                  color="success"
                  bordered
                >
                  <VAvatar
                    :color="!(userData && userData.avatar) ? 'primary' : undefined"
                    :variant="!(userData && userData.avatar) ? 'tonal' : undefined"
                  >
                    <VImg
                      v-if="userData && userData.avatar"
                      :src="userData.avatar"
                    />
                    <VIcon
                      v-else
                      icon="tabler-user"
                    />
                  </VAvatar>
                </VBadge> 
              </VListItemAction>-->

                <div>
                  <h6 class="text-h6 font-weight-medium">
                    {{ userData.fullName || userData.username }}
                  </h6>
                  <VListItemSubtitle class="text-capitalize text-disabled">
                    {{ userData.unvan }}
                  </VListItemSubtitle>
                </div>
              </div>
            </VListItem>

            <PerfectScrollbar :options="{ wheelPropagation: false }">
              <div class="px-4 py-2">
                <VBtn block size="small" color="primary" append-icon="tabler-lock-password" @click="openChangePassword"
                  class="mb-2" variant="tonal">
                  Şifre Değiştir
                </VBtn>
              </div>
              <!-- <template
              v-for="item in userProfileList"
              :key="item.title"
            >
              <VListItem
                v-if="item.type === 'navItem'"
                :to="item.to"
              >
                <template #prepend>
                  <VIcon
                    :icon="item.icon"
                    size="22"
                  />
                </template>

                <VListItemTitle>{{ item.title }}</VListItemTitle>

                <template
                  v-if="item.badgeProps"
                  #append
                >
                  <VBadge
                    rounded="sm"
                    class="me-3"
                    v-bind="item.badgeProps"
                  />
                </template>
              </VListItem>

              <VDivider
                v-else
                class="my-2"
              />
            </template> -->

              <div class="px-4 py-2">
                <VBtn block size="small" color="error" append-icon="tabler-logout" @click="logout">
                  Çıkış Yap
                </VBtn>
              </div>
            </PerfectScrollbar>
          </VList>
        </VMenu>
        <!-- !SECTION -->
      </VAvatar>
    </VBadge>

    <!-- User name next to avatar (hidden on xs for space) -->
    <span class="d-none d-sm-inline text-body-1 font-weight-medium">{{ displayName }}</span>

    <VDialog v-model="changeDialog" max-width="420" persistent>
      <VCard>
        <VCardTitle>Şifre Değiştir</VCardTitle>
        <VCardText>
          <VTextField v-model="form.current_password" label="Mevcut Şifre" :type="showCur ? 'text' : 'password'"
            :disabled="changing" autocomplete="current-password" :rules="[req]"
            :append-inner-icon="showCur ? 'tabler-eye-off' : 'tabler-eye'" @click:append-inner="showCur = !showCur" />
          <VTextField class="mt-2" v-model="form.password" label="Yeni Şifre" :type="showNew ? 'text' : 'password'"
            :disabled="changing" autocomplete="new-password" :rules="[req, min8, different]"
            :append-inner-icon="showNew ? 'tabler-eye-off' : 'tabler-eye'" @click:append-inner="showNew = !showNew" />
          <VTextField class="mt-2" v-model="form.password_confirmation" label="Yeni Şifre (Tekrar)"
            :type="showConf ? 'text' : 'password'" :disabled="changing" autocomplete="new-password"
            :rules="[req, matches]" :append-inner-icon="showConf ? 'tabler-eye-off' : 'tabler-eye'"
            @click:append-inner="showConf = !showConf" />
        </VCardText>
        <VCardActions class="justify-end">
          <VBtn variant="tonal" @click="changeDialog = false" :disabled="changing">Vazgeç</VBtn>
          <VBtn color="primary" @click="submitChangePassword" :loading="changing">Kaydet</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
    <VSnackbar v-model="snack.show" :color="snack.color" timeout="2500">{{ snack.text }}</VSnackbar>
  </div>
</template>

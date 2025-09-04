<!-- ❗Errors in the form are set on line 60 -->
<script setup lang="ts">
import { normalizeAbilityRules } from '@/utils/ability-normalizer'
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import authV2LoginIllustrationBorderedDark from '@images/pages/auth-v2-login-illustration-bordered-dark.png'
import authV2LoginIllustrationBorderedLight from '@images/pages/auth-v2-login-illustration-bordered-light.png'
import authV2LoginIllustrationDark from '@images/pages/auth-v2-login-illustration-dark.png'
import authV2LoginIllustrationLight from '@images/pages/auth-v2-login-illustration-light.png'
import authV2MaskDark from '@images/pages/misc-mask-dark.png'
import authV2MaskLight from '@images/pages/misc-mask-light.png'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { VForm } from 'vuetify/components/VForm'

const authThemeImg = useGenerateImageVariant(
  authV2LoginIllustrationLight,
  authV2LoginIllustrationDark,
  authV2LoginIllustrationBorderedLight,
  authV2LoginIllustrationBorderedDark,
  true,
)

const authThemeMask = useGenerateImageVariant(authV2MaskLight, authV2MaskDark)

definePage({
  meta: {
    layout: 'blank',
    unauthenticatedOnly: true,
  },
})

const isPasswordVisible = ref(false)

// Güvenli başlık ve logo (dev'de undefined durumlarını tolere eder)
const appTitle = computed(() => themeConfig?.app?.title ?? 'oft mes')
const appLogo = computed(() => themeConfig?.app?.logo ?? null)

const route = useRoute()
const router = useRouter()
const ability = useAbility()

const errors = ref<Record<string, string | undefined>>({
  email: undefined,
  password: undefined,
})

const refVForm = ref<VForm>()

const credentials = ref({
  email: '',
  password: '',
})

const rememberMe = ref(false)

// Yükleme ve genel hata durumu
const isSubmitting = ref(false)
const generalError = ref<string | null>(null)

const login = async () => {
  // Başlangıç durumu: yükleme ve hataları temizle
  isSubmitting.value = true
  generalError.value = null
  errors.value = { email: undefined, password: undefined }
  try {
    const res = await $api('/auth/login', {
      method: 'POST',
      body: {
        email: credentials.value.email,
        password: credentials.value.password,
      },
      onResponseError({ response }) {
        console.error('Login error:', response._data)
        const apiErrors = response._data?.errors || null
        errors.value = apiErrors || { email: 'Giriş başarısız', password: 'Giriş başarısız' }
        generalError.value = response._data?.message
          || (typeof response._data === 'string' ? response._data : null)
          || 'Giriş işlemi başarısız oldu. Lütfen bilgilerinizi kontrol edin.'
      },
    })

    const { accessToken, userData, userAbilityRules } = res

    // Normalize ability rules
    const normalizedRules = normalizeAbilityRules(userAbilityRules)

    // Cookie ömrünü Remember Me'ye göre ayarla
    const cookieOpts = rememberMe.value
      ? { maxAge: 60 * 60 * 24 * 30 }
      : { session: true }

    // Ability ve cookie güncelle
    useCookie('userAbilityRules', cookieOpts as any).value = userAbilityRules
    ability.update(normalizedRules as any)

    const userDataCookie = useCookie<any>('userData', cookieOpts as any)
    userDataCookie.value = userData
    useCookie('accessToken', cookieOpts as any).value = accessToken

    await nextTick(() => {
      const isDefaultPwd = credentials.value.password === 'pass1234'
      const targetPath = isDefaultPwd ? '/force-password' : (route.query.to ? String(route.query.to) : '/home-page')
      router.push(targetPath)
    })
  }
  catch (err) {
    console.error(err)
    if (!generalError.value)
      generalError.value = 'Beklenmeyen bir hata oluştu. Lütfen tekrar deneyin.'
  }
  finally {
    isSubmitting.value = false
  }
}

const onSubmit = () => {
  refVForm.value?.validate()
    .then(({ valid: isValid }) => {
      if (isValid) login()
    })
}
</script>






<template>
  <RouterLink to="/">
    <div class="auth-logo d-flex align-center gap-x-3">
      <VNodeRenderer v-if="appLogo" :nodes="appLogo" />
      <h1 class="auth-title">
        {{ appTitle }}
      </h1>
    </div>
  </RouterLink>

  <VRow no-gutters class="auth-wrapper bg-surface">
    <VCol md="8" class="d-none d-md-flex">
      <div class="position-relative bg-background w-100 me-0">
        <div class="d-flex align-center justify-center w-100 h-100" style="padding-inline: 6.25rem;">
          <VImg max-width="613" :src="authThemeImg" class="auth-illustration mt-16 mb-2" />
        </div>

        <img class="auth-footer-mask" :src="authThemeMask" alt="auth-footer-mask" height="280" width="100">
      </div>
    </VCol>

    <VCol cols="12" md="4" class="auth-card-v2 d-flex align-center justify-center">
      <VCard flat :max-width="500" class="mt-12 mt-sm-0 pa-4">
        <VCardText>
          <h4 class="text-h4 mb-1">
            <span class="text-capitalize"> {{ appTitle }}'e hoşgeldiniz' </span>! 👋🏻
          </h4>
          <p class="mb-0">
            Lütfen hesabınıza giriş yapın ve maceraya başlayın...
          </p>
        </VCardText>
        <VCardText>
          <!-- Genel uyarılar -->
          <VAlert v-if="isSubmitting" type="info" class="mb-4">
            Giriş yapılıyor, lütfen bekleyin...
          </VAlert>
          <VAlert v-if="generalError" type="error" class="mb-4">
            {{ generalError }}
          </VAlert>

          <VForm ref="refVForm" @submit.prevent="onSubmit">
            <VRow>
              <!-- email -->
              <VCol cols="12">
                <AppTextField v-model="credentials.email" label="E-posta" placeholder="oft@oft.com" type="email"
                  autofocus :rules="[requiredValidator, emailValidator]" :error-messages="errors.email" />
              </VCol>

              <!-- password -->
              <VCol cols="12">
                <AppTextField v-model="credentials.password" label="Şifre" placeholder="············"
                  :rules="[requiredValidator]" :type="isPasswordVisible ? 'text' : 'password'" autocomplete="password"
                  :error-messages="errors.password"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible" />

                <div class="d-flex align-center flex-wrap justify-space-between my-6">
                  <VCheckbox v-model="rememberMe" label="Beni Hatırla" />
                  <RouterLink class="text-primary ms-2 mb-1" :to="{ name: 'forgot-password' }">
                    Şifremi Unuttum?
                  </RouterLink>
                </div>

                <VBtn block type="submit" :loading="isSubmitting" :disabled="isSubmitting">
                  Giriş Yap
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";
</style>

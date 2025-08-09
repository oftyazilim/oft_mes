<script setup lang="ts">
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import { requiredValidator } from '@core/utils/validators'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { useRoute, useRouter } from 'vue-router'
import { VForm } from 'vuetify/components/VForm'

import authV2ResetPasswordIllustrationDark from '@images/pages/auth-v2-reset-password-illustration-dark.png'
import authV2ResetPasswordIllustrationLight from '@images/pages/auth-v2-reset-password-illustration-light.png'
import authV2MaskDark from '@images/pages/misc-mask-dark.png'
import authV2MaskLight from '@images/pages/misc-mask-light.png'

const route = useRoute()
const router = useRouter()

const token = ref<string>((route.query.token as string) || '')
const email = ref<string>((route.query.email as string) || '')
const password = ref('')
const passwordConfirmation = ref('')
const isPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)
const isSubmitting = ref(false)
const successMessage = ref<string | null>(null)
const errorMessage = ref<string | null>(null)
const refVForm = ref<VForm>()

const authThemeImg = useGenerateImageVariant(authV2ResetPasswordIllustrationLight, authV2ResetPasswordIllustrationDark)
const authThemeMask = useGenerateImageVariant(authV2MaskLight, authV2MaskDark)

definePage({
  meta: {
    layout: 'blank',
    unauthenticatedOnly: true,
  },
})

const minLengthValidator = (val: string) => (val?.length ?? 0) >= 8 || 'Şifre en az 8 karakter olmalıdır.'
const confirmMatchValidator = (val: string) => val === password.value || 'Şifreler eşleşmiyor.'

watch(
  () => route.query,
  q => {
    token.value = (q.token as string) || ''
    email.value = (q.email as string) || ''
  },
  { deep: true }
)

const onSubmit = async () => {
  successMessage.value = null
  errorMessage.value = null

  if (!token.value || !email.value) {
    errorMessage.value = 'Bağlantı geçersiz. Lütfen “Şifremi Unuttum” üzerinden tekrar deneyin.'
    return
  }

  const result = await refVForm.value?.validate()
  if (!result?.valid) return

  try {
    isSubmitting.value = true
    await $api('/auth/reset-password', {
      method: 'POST',
      body: {
        token: token.value,
        email: email.value,
        password: password.value,
        password_confirmation: passwordConfirmation.value,
      },
      onResponseError({ response }) {
        errorMessage.value = response?._data?.message || 'İşlem sırasında bir hata oluştu.'
      },
    })
    successMessage.value = 'Şifreniz başarıyla güncellendi.'
    setTimeout(() => router.push({ name: 'login' }), 1500)
  } catch (e: any) {
    errorMessage.value = e?.message || 'İşlem sırasında bir hata oluştu.'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <RouterLink to="/">
    <div class="auth-logo d-flex align-center gap-x-3">
      <VNodeRenderer :nodes="themeConfig.app.logo" />
      <h1 class="auth-title">
        {{ themeConfig.app.title }}
      </h1>
    </div>
  </RouterLink>

  <VRow class="auth-wrapper bg-surface" no-gutters>
    <VCol md="8" class="d-none d-md-flex">
      <div class="position-relative bg-background w-100 me-0">
        <div class="d-flex align-center justify-center w-100 h-100" style="padding-inline: 150px;">
          <VImg max-width="451" :src="authThemeImg" class="auth-illustration mt-16 mb-2" />
        </div>

        <img class="auth-footer-mask flip-in-rtl" :src="authThemeMask" alt="auth-footer-mask" height="280" width="100" />
      </div>
    </VCol>

    <VCol cols="12" md="4" class="auth-card-v2 d-flex align-center justify-center">
      <VCard flat :max-width="500" class="mt-12 mt-sm-0 pa-6">
        <VCardText>
          <h4 class="text-h4 mb-1">
            Şifreyi Sıfırla 🔒
          </h4>
          <p class="mb-0">
            Yeni şifreniz en az 8 karakter olmalı ve onay şifresiyle eşleşmelidir.
          </p>
        </VCardText>

        <VCardText>
          <VAlert v-if="successMessage" type="success" variant="tonal" class="mb-4">
            {{ successMessage }}
          </VAlert>
          <VAlert v-if="errorMessage" type="error" variant="tonal" class="mb-4">
            {{ errorMessage }}
          </VAlert>

          <VForm ref="refVForm" @submit.prevent="onSubmit">
            <VRow>
              <VCol cols="12">
                <AppTextField v-model="email" label="E-posta" type="email" :readonly="true" />
              </VCol>

              <VCol cols="12">
                <AppTextField
                  v-model="password"
                  autofocus
                  label="Yeni Şifre"
                  placeholder="············"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  autocomplete="new-password"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  :rules="[requiredValidator, minLengthValidator]"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />
              </VCol>

              <VCol cols="12">
                <AppTextField
                  v-model="passwordConfirmation"
                  label="Yeni Şifre (Tekrar)"
                  placeholder="············"
                  :type="isConfirmPasswordVisible ? 'text' : 'password'"
                  autocomplete="new-password"
                  :append-inner-icon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  :rules="[requiredValidator, confirmMatchValidator]"
                  @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                />
              </VCol>

              <VCol cols="12">
                <VBtn block type="submit" :loading="isSubmitting" :disabled="isSubmitting">
                  Şifreyi Güncelle
                </VBtn>
              </VCol>

              <VCol cols="12">
                <RouterLink class="d-flex align-center justify-center" :to="{ name: 'login' }">
                  <VIcon icon="tabler-chevron-left" size="20" class="me-1 flip-in-rtl" />
                  <span>Giriş sayfasına dön</span>
                </RouterLink>
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

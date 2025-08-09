<script setup lang="ts">
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import { emailValidator, requiredValidator } from '@core/utils/validators'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { VForm } from 'vuetify/components/VForm'

import authV2ForgotPasswordIllustrationDark from '@images/pages/auth-v2-forgot-password-illustration-dark.png'
import authV2ForgotPasswordIllustrationLight from '@images/pages/auth-v2-forgot-password-illustration-light.png'
import authV2MaskDark from '@images/pages/misc-mask-dark.png'
import authV2MaskLight from '@images/pages/misc-mask-light.png'

const email = ref('')
const isSubmitting = ref(false)
const successMessage = ref<string | null>(null)
const errorMessage = ref<string | null>(null)
const refVForm = ref<VForm>()

const authThemeImg = useGenerateImageVariant(authV2ForgotPasswordIllustrationLight, authV2ForgotPasswordIllustrationDark)

const authThemeMask = useGenerateImageVariant(authV2MaskLight, authV2MaskDark)

definePage({
  meta: {
    layout: 'blank',
    unauthenticatedOnly: true,
  },
})

const onSubmit = async () => {
  successMessage.value = null
  errorMessage.value = null

  const result = await refVForm.value?.validate()
  if (!result?.valid) return

  try {
    isSubmitting.value = true
    await $api('/auth/forgot-password', {
      method: 'POST',
      body: { email: email.value },
      onResponseError({ response }) {
        errorMessage.value = response?._data?.message || 'İşlem sırasında bir hata oluştu.'
      },
    })
    successMessage.value = 'Şifre sıfırlama bağlantısı e-posta adresinize gönderildi.'
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

  <VRow
    class="auth-wrapper bg-surface"
    no-gutters
  >
    <VCol
      md="8"
      class="d-none d-md-flex"
    >
      <div class="position-relative bg-background w-100 me-0">
        <div
          class="d-flex align-center justify-center w-100 h-100"
          style="padding-inline: 150px;"
        >
          <VImg
            max-width="468"
            :src="authThemeImg"
            class="auth-illustration mt-16 mb-2"
          />
        </div>

        <img
          class="auth-footer-mask"
          :src="authThemeMask"
          alt="auth-footer-mask"
          height="280"
          width="100"
        >
      </div>
    </VCol>

    <VCol
      cols="12"
      md="4"
      class="d-flex align-center justify-center"
    >
      <VCard
        flat
        :max-width="500"
        class="mt-12 mt-sm-0 pa-4"
      >
        <VCardText>
          <h4 class="text-h4 mb-1">
            Şifremi Unuttum? 🔒
          </h4>
          <p class="mb-0">
            E-postanızı girin, şifrenizi sıfırlamak için talimatları size gönderelim.
          </p>
        </VCardText>

        <VCardText>
          <VAlert
            v-if="successMessage"
            type="success"
            variant="tonal"
            class="mb-4"
          >
            {{ successMessage }}
          </VAlert>
          <VAlert
            v-if="errorMessage"
            type="error"
            variant="tonal"
            class="mb-4"
          >
            {{ errorMessage }}
          </VAlert>
          <VForm ref="refVForm" @submit.prevent="onSubmit">
            <VRow>
              <!-- email -->
              <VCol cols="12">
                <AppTextField
                  v-model="email"
                  autofocus
                  label="E-posta"
                  type="email"
                  placeholder="oft@oft.com"
                  :rules="[requiredValidator, emailValidator]"
                />
              </VCol>

              <!-- Reset link -->
              <VCol cols="12">
                <VBtn
                  block
                  type="submit"
                  :loading="isSubmitting"
                  :disabled="isSubmitting"
                >
                  Şifre Sıfırlama Bağlantısını Gönder
                </VBtn>
              </VCol>

              <!-- back to login -->
              <VCol cols="12">
                <RouterLink
                  class="d-flex align-center justify-center"
                  :to="{ name: 'login' }"
                >
                  <VIcon
                    icon="tabler-chevron-left"
                    size="20"
                    class="me-1 flip-in-rtl"
                  />
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

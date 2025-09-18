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

// ---- Giriş Intro Animasyonu ----
const INTRO_KEY = 'loginIntroShownV1'
const showIntro = ref(false)
const prefersReducedMotion = ref(false)
const titleChars = computed(() => (appTitle.value || '').split(''))

function skipIntro() {
  showIntro.value = false
  try { localStorage.setItem(INTRO_KEY, '1') } catch (_) { /* ignore */ }
}

onMounted(() => {
  try { prefersReducedMotion.value = window.matchMedia('(prefers-reduced-motion: reduce)')?.matches } catch (_) { }
  const already = (() => { try { return localStorage.getItem(INTRO_KEY) === '1' } catch (_) { return false } })()
  if (already || prefersReducedMotion.value) {
    showIntro.value = false
    return
  }
  showIntro.value = true
  // Otomatik kapanma süresi (karakter animasyonu + küçük aralık)
  const base = 1800 + titleChars.value.length * 35
  const timeout = Math.min(3500, base)
  setTimeout(() => { if (showIntro.value) skipIntro() }, timeout)
  // ESC ile geçme
  const onKey = (e: KeyboardEvent) => { if (e.key === 'Escape') { skipIntro(); window.removeEventListener('keydown', onKey) } }
  window.addEventListener('keydown', onKey)
})
</script>






<template>
  <!-- Intro Overlay -->
  <div v-if="showIntro" class="login-intro" :class="{ 'reduced-motion': prefersReducedMotion }" aria-live="polite">
    <div class="intro-bg" />
    <div class="intro-noise" />
    <div class="intro-content">
      <div class="intro-title-wrapper">
        <h1 class="intro-title" v-if="prefersReducedMotion">{{ appTitle }}</h1>
        <h1 v-else class="intro-title-chars" :aria-label="appTitle">
          <span v-for="(ch, i) in titleChars" :key="i" class="char" :style="{ animationDelay: (i * 35) + 'ms' }">{{ ch
            === ' ' ? '\u00A0' : ch }}</span>
        </h1>
      </div>
      <p class="intro-sub" v-if="!prefersReducedMotion">Üretimde yeni bir gün başlıyor...</p>
      <button type="button" class="intro-skip" @click="skipIntro" aria-label="Animasyonu Geç">Geç</button>
    </div>
  </div>

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

/* stylelint-disable order/properties-order, @stylistic/max-line-length */

// Intro overlay (scoped olmadan çünkü sayfa blank layout)
.login-intro {
  position: fixed;
  z-index: 15000;
  inset: 0;
  display: grid;
  place-items: center;
  background: radial-gradient(circle at 30% 40%, #{rgba(#4b7bd1,0.18)}, #{rgba(#0a0f19,0.94)} 62%);
  overflow: hidden;
  animation: intro-fade-out 0.7s ease forwards;
  animation-delay: 1.6s;

  &.reduced-motion {
    animation: none;
  }
}

.intro-bg {
  position: absolute;
  inset: -20%;
  background: conic-gradient(from 120deg, #0b1d33, #123b70, #0b1d33, #0b1d33);
  filter: blur(80px) saturate(160%);
  animation: intro-rotate 12s linear infinite;
  opacity: 0.65;
}

.intro-noise {
  position: absolute;
  inset: 0;
  background-image:
    repeating-linear-gradient(0deg, rgba(255, 255, 255, 4%) 0 2px, transparent 2px 4px),
    repeating-linear-gradient(90deg, rgba(255, 255, 255, 3%) 0 3px, transparent 3px 6px);
  mix-blend-mode: overlay;
  opacity: 0.35;
  animation: noise-move 1.8s steps(8) infinite;
  pointer-events: none;
}

.intro-content {
  position: relative;
  text-align: center;
  padding-block: 32px 40px;
  padding-inline: 48px;
  border: 1px solid rgba(255, 255, 255, 12%);
  border-radius: 28px;
  backdrop-filter: blur(12px) brightness(1.2);
  background: linear-gradient(145deg, rgba(255, 255, 255, 8%), rgba(255, 255, 255, 2%));
  box-shadow: 0 10px 40px -6px rgba(0, 0, 0, 55%), 0 0 0 1px rgba(255, 255, 255, 8%) inset;
  animation: content-rise 0.9s cubic-bezier(0.55, 0.21, 0.17, 1.02);
}

.intro-title-chars,
.intro-title {
  font-family: Inter, system-ui, sans-serif;
  font-size: clamp(2.6rem, 6vw, 3.6rem);
  font-weight: 700;
  line-height: 1.05;
  letter-spacing: 1px;
  margin: 0;
  background: linear-gradient(92deg, #fff, #d8ebff 35%, #7fb3ff 55%, #fff);
  background-clip: text;
  color: transparent;
  filter: drop-shadow(0 4px 10px rgba(0, 0, 0, 35%));
}

.intro-title-chars .char {
  display: inline-block;
  transform: translateY(40px) scale(0.85) rotateX(65deg) rotateZ(-8deg);
  opacity: 0;
  animation: char-in 0.75s cubic-bezier(0.65, 0.12, 0.15, 1.1) forwards;
}

.intro-sub {
  margin-block: 18px 6px;
  margin-inline: 0;
  font-size: 14px;
  letter-spacing: 0.5px;
  color: #d0e5ff;
  opacity: 0.9;
  animation: fade-in 0.8s ease 0.35s both;
}

.intro-skip {
  margin-block-start: 18px;
  background: linear-gradient(120deg, #2d5fa5, #3f7dd4 55%, #5fa0ff);
  color: #fff;
  font-size: 13px;
  font-weight: 600;
  padding-block: 10px;
  padding-inline: 20px;
  border: none;
  border-radius: 999px;
  cursor: pointer;
  letter-spacing: 0.5px;
  box-shadow: 0 4px 18px -4px rgba(0, 0, 0, 55%), 0 0 0 1px rgba(255, 255, 255, 18%) inset;
  transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.34, 1), box-shadow 0.35s;

  &:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 22px -6px rgba(0, 0, 0, 60%), 0 0 0 1px rgba(255, 255, 255, 28%) inset;
  }

  &:active {
    transform: translateY(-1px) scale(0.97);
  }
}

@keyframes char-in {
  0% {
    opacity: 0;
    transform: translateY(40px) scale(0.85) rotateX(65deg) rotateZ(-8deg);
    filter: blur(6px);
  }

  55% {
    filter: blur(0);
  }

  100% {
    opacity: 1;
    transform: translateY(0) scale(1) rotateX(0) rotateZ(0);
    filter: blur(0);
  }
}

@keyframes intro-rotate {
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
}

@keyframes noise-move {
  0% {
    transform: translate(0, 0);
  }

  100% {
    transform: translate(10px, 12px);
  }
}

@keyframes content-rise {
  0% {
    opacity: 0;
    transform: translateY(60px) scale(0.96);
  }

  100% {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(10px);
  }

  to {
    opacity: 0.9;
    transform: translateY(0);
  }
}

@keyframes intro-fade-out {
  0% {
    opacity: 1;
    pointer-events: auto;
  }

  100% {
    opacity: 0;
    pointer-events: none;
    visibility: hidden;
  }
}

@media (max-width: 700px) {
  .intro-content {
    padding-inline: 28px;
    padding-block: 26px 34px;
  }

  .intro-title-chars,
  .intro-title {
    font-size: clamp(2.3rem, 9vw, 3rem);
  }

  .intro-skip {
    margin-block-start: 14px;
  }
}

@media (prefers-reduced-motion: reduce) {

  .intro-bg,
  .intro-noise,
  .intro-title-chars .char,
  .intro-content {
    animation: none !important;
  }

  .login-intro {
    animation: none !important;
  }
}
</style>

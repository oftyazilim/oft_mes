<script lang="ts" setup>
import navItems from '@/navigation/horizontal'
import { usePageTitleStore } from '@/stores/pageTitle'
import { themeConfig } from '@themeConfig'
import axios from 'axios'
import { onMounted, watch } from 'vue'
// import { onMounted, watch } from 'vue'
// import { useRoute } from 'vue-router'

// Components
import FeedbackWidget from '@/components/FeedbackWidget.vue'
import Footer from '@/layouts/components/Footer.vue'
import NavBarNotifications from '@/layouts/components/NavBarNotifications.vue'
import NavbarShortcuts from '@/layouts/components/NavbarShortcuts.vue'
import NavbarThemeSwitcher from '@/layouts/components/NavbarThemeSwitcher.vue'
import UserProfile from '@/layouts/components/UserProfile.vue'
import { useLoadingStore } from '@/stores/loading'
import { HorizontalNavLayout } from '@layouts'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'

// Store: current page title/alias
const pageTitleStore = usePageTitleStore()
const loadingStore = useLoadingStore()
let __pageLogSentForThisNavCycle = false

function sendPageLoadLog(title: string) {
  try {
    // userData cookie mevcutsa al
    const userData: any = useCookie('userData')
    const uid = userData?.value?.id
    if (!uid || !title) return
    // Aynı navigasyon döngüsünde bir kez gönder
    if (__pageLogSentForThisNavCycle) return
    __pageLogSentForThisNavCycle = true
    axios.post('/api/log-kayit', {
      userId: uid,
      sayfa: title,
      eylem: 'Yükleme',
    }).finally(() => {
      // 1 sn sonra tekrar gönderilebilir olsun (ör. dinamik başlık değişimleri)
      setTimeout(() => { __pageLogSentForThisNavCycle = false }, 1000)
    })
  } catch (e) {
    // sessiz geç
    // console.debug(e)
  }
}

onMounted(() => {
  if (pageTitleStore.title) sendPageLoadLog(pageTitleStore.title)
})

watch(() => pageTitleStore.title, (val) => {
  if (val) sendPageLoadLog(val)
})
// Başlık artık route değişiminde sıfırlanmıyor; her sayfa kendi başlığını ayarlasın

// Not: Bu değişkenler yalnızca template içinde kullanıldığı için
// VS Code'un "Organize Imports" aksiyonu tarafından "kullanılmıyor" sanılıp
// import'ları silinebiliyor. Aşağıdaki dummy referans bu davranışı engeller.
// eslint-disable-next-line @typescript-eslint/no-unused-vars
const __keepTemplateRefs = { navItems, themeConfig }
</script>

<template>
  <HorizontalNavLayout :nav-items="navItems">
    <!-- Global loading bar (fixed, no layout shift) -->
    <VProgressLinear v-show="loadingStore.isLoading" color="primary" height="3" indeterminate
      class="global-loading-bar" />
    <!-- 👉 navbar -->
    <template #navbar>
      <RouterLink to="/" class="app-logo d-flex align-center gap-x-3">
        <VNodeRenderer :nodes="themeConfig.app.logo" />

        <h1 class="app-title font-weight-bold leading-normal text-xl text-capitalize">
          {{ themeConfig.app.title }}
        </h1>
      </RouterLink>
      <VSpacer />

      <!-- Kalıcı sayfa bilgisi: logoya dokunmadan sağ tarafta gösterilir -->
      <div v-if="pageTitleStore.title" class="page-title-badge me-3 d-flex">
        <span class="badge-text">{{ pageTitleStore.title }}</span>
      </div>

      <!-- <NavSearchBar trigger-btn-class="ms-lg-n3" /> -->

      <!-- <NavBarI18n
        v-if="themeConfig.app.i18n.enable && themeConfig.app.i18n.langConfig?.length"
        :languages="themeConfig.app.i18n.langConfig"
      /> -->

      <NavbarThemeSwitcher />
      <FeedbackWidget :icon-mode="true" />
      <NavbarShortcuts />
      <NavBarNotifications class="me-2" />
      <UserProfile />
    </template>

    <!-- 👉 Pages -->
    <slot />

    <!-- FeedbackWidget artık navbar içinde ikon olarak yer alıyor -->

    <!-- 👉 Footer -->
    <template #footer>
      <Footer />
    </template>

    <!-- 👉 Customizer -->
    <TheCustomizer />
  </HorizontalNavLayout>
</template>

<style scoped>
.page-title-badge {
  align-items: center;
}

.badge-text {
  border-radius: 9999px;

  /* Vuetify primary rengini kullan */
  background-color: rgba(var(--v-theme-primary), 0.12);
  color: rgb(var(--v-theme-primary));
  font-size: 0.8rem;
  line-height: 1;
  padding-block: 6px;
  padding-inline: 10px;
  white-space: nowrap;
}

.global-loading-bar {
  position: fixed;
  z-index: 2000;
  inset-block-start: 0;
  inset-inline: 0;
}
</style>

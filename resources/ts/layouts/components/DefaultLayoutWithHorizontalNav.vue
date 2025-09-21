<script lang="ts" setup>
import navItems from '@/navigation/horizontal'
import { themeConfig } from '@themeConfig'
import { usePageTitleStore } from '@/stores/pageTitle'
// import { onMounted, watch } from 'vue'
// import { useRoute } from 'vue-router'

// Components
import Footer from '@/layouts/components/Footer.vue'
import NavBarNotifications from '@/layouts/components/NavBarNotifications.vue'
import NavbarShortcuts from '@/layouts/components/NavbarShortcuts.vue'
import NavbarThemeSwitcher from '@/layouts/components/NavbarThemeSwitcher.vue'
import UserProfile from '@/layouts/components/UserProfile.vue'
import { HorizontalNavLayout } from '@layouts'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'

// Store: current page title/alias
const pageTitleStore = usePageTitleStore()
// Başlık artık route değişiminde sıfırlanmıyor; her sayfa kendi başlığını ayarlasın
</script>

<template>
  <HorizontalNavLayout :nav-items="navItems">
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
      <NavbarShortcuts />
      <NavBarNotifications class="me-2" />
      <UserProfile />
    </template>

    <!-- 👉 Pages -->
    <slot />

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
</style>

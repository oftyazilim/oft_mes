<script lang="ts" setup>
import navItems from '@/navigation/vertical'
import { usePageTitleStore } from '@/stores/pageTitle'
import { themeConfig } from '@themeConfig'

// Components
import Footer from '@/layouts/components/Footer.vue'
import NavBarNotifications from '@/layouts/components/NavBarNotifications.vue'
import NavbarShortcuts from '@/layouts/components/NavbarShortcuts.vue'
import NavbarThemeSwitcher from '@/layouts/components/NavbarThemeSwitcher.vue'
import UserProfile from '@/layouts/components/UserProfile.vue'

// @layouts plugin
import FeedbackWidget from '@/components/FeedbackWidget.vue'
import { VerticalNavLayout } from '@layouts'

// Organize Imports'ın template kullanımını görmeyip importları silmesini önlemek için dummy referans
// eslint-disable-next-line @typescript-eslint/no-unused-vars
const __keepTemplateRefs = { navItems, themeConfig }

// Store: current page title/alias
const pageTitleStore = usePageTitleStore()
</script>

<template>
  <VerticalNavLayout :nav-items="navItems">
    <!-- 👉 navbar -->
    <template #navbar="{ toggleVerticalOverlayNavActive }">
      <div class="d-flex h-100 align-center">
        <IconBtn id="vertical-nav-toggle-btn" class="ms-n3 d-lg-none" @click="toggleVerticalOverlayNavActive(true)">
          <VIcon size="26" icon="tabler-menu-2" />
        </IconBtn>

        <!-- <NavSearchBar class="ms-lg-n3" /> -->

        <VSpacer />

        <!-- <NavBarI18n
          v-if="themeConfig.app.i18n.enable && themeConfig.app.i18n.langConfig?.length"
          :languages="themeConfig.app.i18n.langConfig"
        /> -->

        <!-- Sayfa başlığı rozeti (horizontal layout ile tutarlı) -->
        <div v-if="pageTitleStore.title" class="page-title-badge me-3 d-flex">
          <span class="badge-text">{{ pageTitleStore.title }}</span>
        </div>
        <NavbarThemeSwitcher />
        <FeedbackWidget :icon-mode="true" />
        <NavbarShortcuts />
        <NavBarNotifications class="me-1" />
        <UserProfile />
      </div>
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
  </VerticalNavLayout>
</template>

<style scoped>
.page-title-badge {
  align-items: center;
}

.badge-text {
  border-radius: 9999px;
  background-color: rgba(var(--v-theme-primary), 0.12);
  color: rgb(var(--v-theme-primary));
  font-size: 0.8rem;
  line-height: 1;
  padding-block: 6px;
  padding-inline: 10px;
  white-space: nowrap;
}
</style>

import VueI18nPlugin from "@intlify/unplugin-vue-i18n/vite";
import vue from "@vitejs/plugin-vue";
import vueJsx from "@vitejs/plugin-vue-jsx";
import laravel from "laravel-vite-plugin";
import { execSync } from "node:child_process";
import { readFileSync } from "node:fs";
import { fileURLToPath } from "node:url";
import AutoImport from "unplugin-auto-import/vite";
import Components from "unplugin-vue-components/vite";
import {
  VueRouterAutoImports,
  getPascalCaseRouteName,
} from "unplugin-vue-router";
import VueRouter from "unplugin-vue-router/vite";
import { defineConfig } from "vite";
import MetaLayouts from "vite-plugin-vue-meta-layouts";
import vuetify from "vite-plugin-vuetify";
import svgLoader from "vite-svg-loader";

// https://vitejs.dev/config/
const buildTime = (() => {
  // İstanbul/Türkiye saat dilimi formatı
  const now = new Date();
  const pad = (n: number) => String(n).padStart(2, "0");
  return `${pad(now.getDate())}.${pad(
    now.getMonth() + 1
  )}.${now.getFullYear()} ${pad(now.getHours())}:${pad(now.getMinutes())}`;
})();

// Uygulama versiyonu (package.json + git kısa hash) - isteğe bağlı APP_VERSION override
const appVersion = (() => {
  let pkgVersion = "0.0.0";
  try {
    const pkgJson = JSON.parse(
      readFileSync(new URL("./package.json", import.meta.url), "utf-8")
    );
    pkgVersion = pkgJson.version || pkgVersion;
  } catch (e) {
    // ignore
  }

  let gitHash = "";
  try {
    gitHash = execSync("git rev-parse --short HEAD", {
      stdio: ["ignore", "pipe", "ignore"],
    })
      .toString()
      .trim();
  } catch (e) {
    // repo olmayabilir ya da git yok - sorun değil
  }

  const base = process.env.APP_VERSION || pkgVersion;
  return gitHash && !base.includes(gitHash) ? `${base}+${gitHash}` : base;
})();

export default defineConfig({
  plugins: [
    // Docs: https://github.com/posva/unplugin-vue-router
    // ℹ️ This plugin should be placed before vue plugin
    VueRouter({
      getRouteName: (routeNode) => {
        // Convert pascal case to kebab case
        return getPascalCaseRouteName(routeNode)
          .replace(/([a-z\d])([A-Z])/g, "$1-$2")
          .toLowerCase();
      },

      beforeWriteFiles: (root) => {
        root.insert(
          "/apps/email/:filter",
          "/resources/ts/pages/apps/email/index.vue"
        );
        root.insert(
          "/apps/email/:label",
          "/resources/ts/pages/apps/email/index.vue"
        );
      },

      routesFolder: "resources/ts/pages",
    }),
    vue({
      template: {
        compilerOptions: {
          isCustomElement: (tag) =>
            tag === "swiper-container" || tag === "swiper-slide",
        },

        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
    laravel({
      input: ["resources/ts/main.ts"],
      refresh: true,
    }),
    vueJsx(), // Docs: https://github.com/vuetifyjs/vuetify-loader/tree/master/packages/vite-plugin
    vuetify({
      styles: {
        configFile: "resources/styles/variables/_vuetify.scss",
      },
    }), // Docs: https://github.com/dishait/vite-plugin-vue-meta-layouts?tab=readme-ov-file
    MetaLayouts({
      target: "./resources/ts/layouts",
      defaultLayout: "default",
    }), // Docs: https://github.com/antfu/unplugin-vue-components#unplugin-vue-components
    Components({
      dirs: [
        "resources/ts/@core/components",
        "resources/ts/views/demos",
        "resources/ts/components",
      ],
      dts: true,
      resolvers: [
        (componentName) => {
          // Auto import `VueApexCharts`
          if (componentName === "VueApexCharts")
            return {
              name: "default",
              from: "vue3-apexcharts",
              as: "VueApexCharts",
            };
        },
      ],
    }), // Docs: https://github.com/antfu/unplugin-auto-import#unplugin-auto-import
    AutoImport({
      imports: [
        "vue",
        VueRouterAutoImports,
        "@vueuse/core",
        "@vueuse/math",
        "vue-i18n",
        "pinia",
      ],
      dirs: [
        "./resources/ts/@core/utils",
        "./resources/ts/@core/composable/",
        "./resources/ts/composables/",
        "./resources/ts/utils/",
        "./resources/ts/plugins/*/composables/*",
      ],
      vueTemplate: true,

      // ℹ️ Disabled to avoid confusion & accidental usage
      ignore: ["useCookies", "useStorage"],
    }), // Docs: https://github.com/intlify/bundle-tools/tree/main/packages/unplugin-vue-i18n#intlifyunplugin-vue-i18n
    VueI18nPlugin({
      runtimeOnly: true,
      compositionOnly: true,
      include: [
        fileURLToPath(
          new URL("./resources/ts/plugins/i18n/locales/**", import.meta.url)
        ),
      ],
    }),
    svgLoader(),
  ],
  define: {
    "process.env": {},
    __BUILD_TIME__: JSON.stringify(buildTime),
    __APP_VERSION__: JSON.stringify(appVersion),
  },
  resolve: {
    alias: {
      // Avoid bundling native fsevents in browser build
      fsevents: false as any,
      "@core-scss": fileURLToPath(
        new URL("./resources/styles/@core", import.meta.url)
      ),
      "@": fileURLToPath(new URL("./resources/ts", import.meta.url)),
      "@themeConfig": fileURLToPath(
        new URL("./themeConfig.ts", import.meta.url)
      ),
      "@core": fileURLToPath(new URL("./resources/ts/@core", import.meta.url)),
      "@layouts": fileURLToPath(
        new URL("./resources/ts/@layouts", import.meta.url)
      ),
      "@images": fileURLToPath(new URL("./resources/images/", import.meta.url)),
      "@styles": fileURLToPath(new URL("./resources/styles/", import.meta.url)),
      "@configured-variables": fileURLToPath(
        new URL("./resources/styles/variables/_template.scss", import.meta.url)
      ),
      "@db": fileURLToPath(
        new URL("./resources/ts/plugins/fake-api/handlers/", import.meta.url)
      ),
      "@api-utils": fileURLToPath(
        new URL("./resources/ts/plugins/fake-api/utils/", import.meta.url)
      ),
    },
  },
  build: {
    chunkSizeWarningLimit: 5000,
  },
  optimizeDeps: {
    exclude: ["vuetify", "fsevents"],
    entries: ["./resources/ts/**/*.vue"],
  },
});

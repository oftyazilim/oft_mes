import { createApp } from "vue";

import App from "@/App.vue";
import { registerPlugins } from "@core/utils/plugins";

// ...

// Styles
import "@core-scss/template/index.scss";
import "@styles/styles.scss";

import "animate.css";
import axios from "axios";
import config from "devextreme/core/config";
import { loadMessages, locale } from "devextreme/localization";
import trMessages from "devextreme/localization/messages/tr.json";
import { licenseKey } from "./devextreme-license";
// import 'devextreme/dist/css/dx.fluent.saas.light.css'

loadMessages(trMessages); // Türkçe mesajları yükle
locale("tr"); // Lokalizasyonu Türkçe'ye ayarla
config({ licenseKey });

// Create vue app
const app = createApp(App);

// Register plugins
registerPlugins(app);

// Mount vue app
app.mount("#app");

// Set UI zoom to 90% on startup
(() => {
  const DEFAULT_ZOOM = 0.9;
  const zoomStr = localStorage.getItem("app-zoom");
  const zoom =
    Number.isFinite(Number(zoomStr)) && Number(zoomStr) > 0
      ? Number(zoomStr)
      : DEFAULT_ZOOM;

  const appEl = document.getElementById("app");
  if (!appEl) return;

  // Prefer native zoom when available (Chromium/Edge)
  const styleAny = appEl.style as any;
  if (typeof styleAny.zoom !== "undefined") {
    styleAny.zoom = String(zoom);
  } else {
    // Fallback for browsers without zoom (e.g., Firefox)
    appEl.style.transform = `scale(${zoom})`;
    appEl.style.transformOrigin = "0 0";
    // Compensate width so scaled content fills viewport width
    appEl.style.width = `${(100 / zoom).toFixed(3)}%`;
  }
})();

// Axios: attach Authorization header from cookie for all requests
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
axios.interceptors.request.use((config) => {
  try {
    const token = useCookie<string | null>("accessToken").value;
    if (token) {
      config.headers = config.headers ?? {};
      (config.headers as any)["Authorization"] = `Bearer ${token}`;
      (config.headers as any)["Accept"] = "application/json";
    }
  } catch (e) {
    // ignore
  }
  return config;
});

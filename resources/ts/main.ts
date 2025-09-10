import { createApp } from "vue";

import App from "@/App.vue";
import { registerPlugins } from "@core/utils/plugins";

// ...

// Styles
import "@core-scss/template/index.scss";

import "animate.css";
import axios from "axios";
import config from "devextreme/core/config";
import { loadMessages, locale } from "devextreme/localization";
import trMessages from "devextreme/localization/messages/tr.json";
import { licenseKey } from "./devextreme-license";
// import 'devextreme/dist/css/dx.fluent.saas.light.css'



// // DevExtreme CSS
// import 'devextreme/dist/css/dx.common.css'
// import "devextreme/dist/css/dx.fluent.saas.light.css";   // veya kullandığın tema

// // Global override (scoped değil!)
// import '../styles/devextreme-overrides.css'
import "@styles/styles.scss";



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
  const DEFAULT_ZOOM = 1.00;
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

// --- Session Timeout Handling ---
// Kullanıcı uzun süre açık bıraktığında 401/419 yakalanırsa ekrana bir uyarı gösterir.
// İlk 401/419 geldiğinde bir kere snackbar benzeri basit overlay yaratılır; kullanıcı ekrana tıklayınca login sayfasına yönlendirilir.
let sessionExpired = false;
let overlayEl: HTMLDivElement | null = null;
let idleTimerStarted = false;
let lastActivity = Date.now();

// ENV'den (Vite) gelecek maksimum süre (dakika) – yoksa 120.
const SESSION_MAX_MINUTES = Number(import.meta.env.VITE_SESSION_MAX_MINUTES || 360);
const SESSION_MAX_MS = SESSION_MAX_MINUTES * 60 * 1000;

function markActivity() {
  if (sessionExpired) return;
  lastActivity = Date.now();
}

function startIdleWatcher() {
  if (idleTimerStarted) return;
  idleTimerStarted = true;
  ['click','keydown','mousemove','touchstart','wheel'].forEach(evt => {
    window.addEventListener(evt, markActivity, { passive: true });
  });
  // Her 30 saniyede bir kontrol (daha kısa istenirse düşürülebilir)
  setInterval(() => {
    if (sessionExpired) return;
    const now = Date.now();
    if (now - lastActivity >= SESSION_MAX_MS) {
      showSessionExpiredOverlay(true); // proactive (idle)
    }
  }, 30_000);
}

function showSessionExpiredOverlay(fromIdle = false) {
  if (sessionExpired) return;
  sessionExpired = true;

  overlayEl = document.createElement('div');
  overlayEl.id = 'session-expired-overlay';
  overlayEl.style.position = 'fixed';
  overlayEl.style.inset = '0';
  overlayEl.style.zIndex = '9999';
  overlayEl.style.background = 'rgba(0,0,0,0.65)';
  overlayEl.style.display = 'flex';
  overlayEl.style.alignItems = 'center';
  overlayEl.style.justifyContent = 'center';
  overlayEl.style.fontFamily = 'system-ui, sans-serif';
  overlayEl.style.cursor = 'pointer';
  const reasonText = fromIdle ? 'Hareketsizlik nedeniyle' : 'Güvenlik nedeniyle';
  overlayEl.innerHTML = `
    <div style="max-width:480px;background:#1e1e1e;color:#fff;padding:32px 28px;border-radius:14px;box-shadow:0 8px 28px -6px rgba(0,0,0,.55);text-align:center;">
      <h2 style="margin:0 0 12px;font-size:22px;letter-spacing:.5px;">Oturum Süresi Doldu</h2>
      <p style="margin:0 0 18px;line-height:1.4;font-size:14px;opacity:.85;">
        ${reasonText} oturumunuz sona erdi.<br/>Devam etmek için yeniden giriş yapın.<br/><br/>
        <strong>Tıklayın</strong> veya herhangi bir tuşa basın.
      </p>
      <div style="display:flex;gap:10px;justify-content:center;">
        <button id="session-expired-relogin" style="background:#ff7043;border:none;color:#fff;padding:10px 18px;border-radius:6px;font-weight:600;letter-spacing:.5px;font-size:14px;cursor:pointer;">Giriş Ekranına Git</button>
      </div>
    </div>`;

  const redirect = () => {
    // accessToken cookie temizle
    try { useCookie('accessToken').value = null as any; } catch (_) {}
    window.location.href = '/login';
  };

  overlayEl.addEventListener('click', redirect, { once: true });
  window.addEventListener('keydown', redirect, { once: true });
  overlayEl.querySelector('#session-expired-relogin')?.addEventListener('click', (e) => {
    e.stopPropagation();
    redirect();
  }, { once: true });

  document.body.appendChild(overlayEl);
}

axios.interceptors.response.use(
  r => r,
  error => {
    const status = error?.response?.status;
    if (status === 401 || status === 419) {
      showSessionExpiredOverlay();
    }
    return Promise.reject(error);
  }
);

// --- Test Amaçlı Kısa Session (İsteğe bağlı) ---
// Geliştirme sırasında .env dosyasında SESSION_LIFETIME=2 (dakika) ayarlayıp,
// bu overlay tetiklenmesini hızlı doğrulayabilirsiniz. Production geri: 120.

// Uygulama açılışında idle watcher başlat
startIdleWatcher();
markActivity();



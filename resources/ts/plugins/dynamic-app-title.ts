import { layoutConfig as themeLayoutConfig, themeConfig } from '@themeConfig'
import { layoutConfig as layoutsLayoutConfig } from '@layouts'
import type { App } from 'vue'
import { watch } from 'vue'

export default function (_app: App) {
  try {
    const userData = useCookie<any>('userData')
    // Global referans: login/logout tarafı bu ref'in değerini doğrudan güncelleyebilsin
    try { (window as any).__appTitleRef = userData } catch { /* ignore */ }

  const PLACEHOLDER = "kurum adı";

    const extractCoName = (val: any): string | null => {
      const coName = val?.co_name
        ?? val?.firma_adi
        ?? val?.companyName
        ?? val?.user?.co_name
        ?? null
      if (!coName) return null
      const normalized = String(coName).trim()
      return normalized || null
    }

    const setTitle = (title: string) => {
      // Sadece theme/layout başlıklarını güncelle (sekme başlığı sayfa bileşenlerinden yönetiliyor)
      themeConfig.app.title = title as Lowercase<string>
      themeLayoutConfig.app.title = title as Lowercase<string>
      // Ayrıca @layouts tarafındaki layoutConfig'i de güncelle (VerticalNav gibi yerler bunu okuyor)
      try { (layoutsLayoutConfig as any).app.title = title } catch {}
    }

    const apply = (val: any) => {
      const name = extractCoName(val)
      if (!name) {
        setTitle(PLACEHOLDER)
        return
      }
      setTitle(name.toLowerCase())
    }

    // İlk değer
    apply(userData?.value)

    // Değişimleri izle (login/logout sonrası güncellensin)
    watch(
      () => userData?.value,
      (val) => apply(val),
      { deep: false }
    )

    // Ek sağlamlık: aynı sekmede login/logout sonrası cookie bazen reaktif olmayabilir.
    // - Odak/visibility değişiminde yeniden uygula
    const reapply = () => apply(userData?.value)
    window.addEventListener('focus', reapply)
    document.addEventListener('visibilitychange', reapply)

    // - Hafif polling: cookie değişimini yakala (dev HMR’de çoğalmayı önlemek için guard)
    const w = window as any
    if (!w.__appTitleCookiePoller) {
      let lastApplied = extractCoName(userData?.value) ?? PLACEHOLDER
      w.__appTitleCookiePoller = setInterval(() => {
        try {
          // Yalnızca useCookie ref'ini kullan. document.cookie üzerinde aynı isimli
          // birden fazla cookie (farklı path'lerle) bulunabiliyor ve yanlış değer seçilebiliyor.
          const current = extractCoName(userData?.value) ?? PLACEHOLDER
          if (current !== lastApplied) {
            lastApplied = current
            apply(userData?.value)
          }
        } catch { /* ignore */ }
      }, 1000)
    }
  } catch (e) {
    // sessizce geç
  }
}

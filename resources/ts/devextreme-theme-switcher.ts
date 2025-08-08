import dxLight from 'devextreme/dist/css/dx.fluent.saas.light.css?url'
import dxDark from 'devextreme/dist/css/dx.fluent.saas.dark.css?url'

export function setDevExtremeTheme(isDark: boolean) {
  document.querySelectorAll('link[data-dx-theme]').forEach(el => el.remove())
  const link = document.createElement('link')
  link.rel = 'stylesheet'
  link.setAttribute('data-dx-theme', 'true')
  link.href = isDark ? dxDark : dxLight
  document.head.appendChild(link)
}

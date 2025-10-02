import dxDark from "devextreme/dist/css/dx.fluent.saas.dark.css?url";
import dxLight from "devextreme/dist/css/dx.fluent.saas.light.css?url";
import overridesUrl from "../styles/devextreme-overrides.css?url";

export function setDevExtremeTheme(isDark: boolean) {
  document.querySelectorAll("link[data-dx-theme]").forEach((el) => el.remove());
  const link = document.createElement("link");
  link.rel = "stylesheet";
  link.setAttribute("data-dx-theme", "true");
  link.href = isDark ? dxDark : dxLight;
  document.head.appendChild(link);

  // Tema sonrası override stilini daima en sonra ekle
  const prevOverride = document.querySelector("link[data-dx-override]");
  if (prevOverride) prevOverride.remove();
  const overrideLink = document.createElement("link");
  overrideLink.rel = "stylesheet";
  overrideLink.setAttribute("data-dx-override", "true");
  overrideLink.href = overridesUrl;
  document.head.appendChild(overrideLink);
}

declare module "*.vue" {
  import type { DefineComponent } from "vue";

  const component: DefineComponent<{}, {}, any>;
  export default component;
}

declare module "vue-prism-component" {
  import { ComponentOptions } from "vue";
  const component: ComponentOptions;
  export default component;
}
declare module "vue-shepherd";
declare module "@videojs-player/vue";
declare module "exceljs";
declare module "file-saver-es";
declare module "sweetalert2";

// Vuetify global instance shorthand ($vuetify) için template/intellisense uyarılarını önleyen tip bildirimi
import "vue";
declare module "vue" {
  interface ComponentCustomProperties {
    $vuetify: any;
  }
}

// Genel yardımcı tipler ve eksik modüller için shims
declare const $: any;

// DevExtreme'de yer yer kullanılan isimler için basit tipler
type DataSource = any;
type dxPivotGridPivotGridCell = any;

// Fake DB alias'ları bulunan importlar için geniş şema
declare module "@db/*" {
  const mod: any;
  export = mod;
}

// unplugin-vue-router named export kullanımını sustur
declare module "unplugin-vue-router" {
  const definePage: any;
  export default definePage;
  export { definePage };
}

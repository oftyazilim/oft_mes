import type { VerticalNavItems } from '@layouts/types'
import dashboard from "./dashboard"
import planlama from "./planlama"
import satis from "./satis"
import satinalma from "./satinalma"
import sistem from "./sistem"
import uretim from "./uretim"
import kalite from "./kalite"
import depo from "./depo"
// import dinlence from "./dinlence"
// import yardim from "./yardim"

export default [
  ...dashboard,
  ...planlama,
  ...satis,
  ...satinalma,
  ...uretim,
  ...kalite,
  ...depo,
  // ...dinlence,
  ...sistem,
  // ...yardim
] as VerticalNavItems;

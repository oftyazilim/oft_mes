import type { HorizontalNavItems } from "@layouts/types";
import dashboard from "./dashboard";
import planlama from "./planlama";
import satis from "./satis";
import satinalma from "./satinalma";
import sistem from "./sistem";
import uretim from "./uretim";
import kalite from "./kalite";
import depo from "./depo";

export default [
  ...dashboard,
  ...planlama,
  ...satis,
  ...satinalma,
  ...uretim,
  ...kalite,
  ...depo,
  ...sistem,
] as HorizontalNavItems;

import type { HorizontalNavItems } from "@layouts/types";
import dashboard from "./dashboard";
import planlama from "./planlama";
import satis from "./satis";
import satinalma from "./satinalma";
import sistem from "./sistem";
import uretim from "./uretim";
import kalite from "./kalite";

export default [
  ...dashboard,
  ...planlama,
  ...satis,
  ...satinalma,
  ...uretim,
  ...kalite,
  ...sistem,
] as HorizontalNavItems;

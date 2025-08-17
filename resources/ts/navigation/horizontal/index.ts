import type { HorizontalNavItems } from "@layouts/types";
import dashboard from "./dashboard";
import planlama from "./planlama";
import satis from "./satis";
import sistem from "./sistem";
import uretim from "./uretim";

export default [
  ...dashboard,
  ...planlama,
  ...satis,
  ...uretim,
  ...sistem,
] as HorizontalNavItems;

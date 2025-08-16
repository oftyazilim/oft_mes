// import apps from './apps'
import type { HorizontalNavItems } from "@layouts/types";
import dashboard from "./dashboard";
import planlama from "./planlama";
import sistem from "./sistem";
import uretim from "./uretim";

export default [
  ...dashboard,
  ...planlama,
  ...uretim,
  ...sistem,
] as HorizontalNavItems;

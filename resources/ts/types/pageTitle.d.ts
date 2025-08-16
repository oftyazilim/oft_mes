declare module "@/stores/pageTitle" {
  import { StoreDefinition } from "pinia";
  interface PageTitleState {
    title: string;
    toplam: string;
  }
  interface PageTitleActions {
    setTitle(newTitle: string): void;
    setToplam(deger: string): void;
  }
  export const usePageTitleStore: StoreDefinition<
    "pageTitle",
    PageTitleState,
    {},
    PageTitleActions
  >;
}

declare module "uuid";

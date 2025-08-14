import { defineStore } from 'pinia';

export const usePageTitleStore = defineStore('pageTitle', {
  state: () => ({
    title: '',
    toplam: '',
  }),
  actions: {
    setTitle(newTitle) {
      this.title = newTitle;
    },
    setToplam(deger) {
      this.toplam = deger;
    }
  }
});

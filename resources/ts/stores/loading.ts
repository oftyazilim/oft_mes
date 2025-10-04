import { defineStore } from 'pinia'

export const useLoadingStore = defineStore('loading', {
  state: () => ({ count: 0 as number }),
  getters: {
    isLoading: (state) => state.count > 0,
  },
  actions: {
    start() {
      this.count++
    },
    stop() {
      if (this.count > 0) this.count--
    },
    reset() {
      this.count = 0
    },
  },
})

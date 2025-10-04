import { useLoadingStore } from '@/stores/loading'

export function useGlobalLoading() {
  const store = useLoadingStore()

  const wrapPromise = async <T>(p: Promise<T>): Promise<T> => {
    try {
      store.start()
      return await p
    } finally {
      store.stop()
    }
  }

  const withLoading = async <T>(fn: () => Promise<T>): Promise<T> => {
    try {
      store.start()
      return await fn()
    } finally {
      store.stop()
    }
  }

  return {
    isLoading: computed(() => store.isLoading),
    start: store.start,
    stop: store.stop,
    wrapPromise,
    withLoading,
  }
}

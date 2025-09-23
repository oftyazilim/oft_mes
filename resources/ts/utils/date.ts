import dayjs from 'dayjs'
import 'dayjs/locale/tr'

// Varsayılan locale: tr
dayjs.locale('tr')

export const setDateLocale = (loc: string) => {
  try {
    dayjs.locale(loc)
  } catch (e) {
    // bilinmeyen locale gelirse sessizce tr bırak
    dayjs.locale('tr')
  }
}

export const formatDateTime = (value: string | number | Date | null | undefined, fmt = 'DD.MM.YYYY HH:mm') => {
  if (!value) return '-'
  return dayjs(value).isValid() ? dayjs(value).format(fmt) : '-'
}

export const formatDate = (value: string | number | Date | null | undefined, fmt = 'DD.MM.YYYY') => {
  if (!value) return '-'
  return dayjs(value).isValid() ? dayjs(value).format(fmt) : '-'
}

export const formatTime = (value: string | number | Date | null | undefined, fmt = 'HH:mm') => {
  if (!value) return '-'
  return dayjs(value).isValid() ? dayjs(value).format(fmt) : '-'
}

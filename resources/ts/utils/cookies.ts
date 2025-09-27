// Util: Aggressively purge auth-related cookies across common paths to avoid
// Chromium path shadowing issues (same cookie name with different Path values).

export const AUTH_COOKIE_NAMES = [
  'userData',
  'accessToken',
  'userAbilityRules',
]

/**
 * Delete a cookie for specific path by setting Max-Age=0.
 */
function deleteCookie(name: string, path?: string) {
  try {
    const parts: string[] = [`${name}=`, 'Max-Age=0']
    if (path) parts.push(`Path=${path}`)
    // Try also with Secure/SameSite for completeness, but browsers ignore on delete
    // parts.push('SameSite=Lax')
    // If current origin is https, include Secure (harmless on delete attempt)
    if (typeof location !== 'undefined' && location.protocol === 'https:') parts.push('Secure')
    document.cookie = parts.join('; ')
  } catch { /* noop */ }
}

/**
 * Generate a set of likely paths where legacy cookies might reside.
 * Includes '/', current path segments, and a few known auth routes.
 */
function generateLikelyPaths(): string[] {
  const set = new Set<string>()
  set.add('/')
  try {
    const p = (typeof location !== 'undefined' ? location.pathname : '/') || '/'
    const segments = p.split('/').filter(Boolean)
    let cur = ''
    for (const seg of segments) {
      cur += `/${seg}`
      set.add(cur)
    }
  } catch { /* ignore */ }
  // Known routes in this app
  ;['/login', '/home-page', '/force-password'].forEach(x => set.add(x))
  return Array.from(set)
}

/**
 * Purge a single cookie across likely paths.
 */
export function purgeCookieAllPaths(name: string) {
  // Try without path (current path implicit)
  deleteCookie(name)
  for (const p of generateLikelyPaths()) deleteCookie(name, p)
}

/**
 * Purge all auth-related cookies across common paths.
 */
export function purgeAuthCookies() {
  for (const n of AUTH_COOKIE_NAMES) purgeCookieAllPaths(n)
}

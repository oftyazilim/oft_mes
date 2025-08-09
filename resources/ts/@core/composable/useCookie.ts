// Ported from [Nuxt](https://github.com/nuxt/nuxt/blob/main/packages/nuxt/src/app/composables/cookie.ts)

import type { CookieParseOptions, CookieSerializeOptions } from "cookie-es";
import { parse, serialize } from "cookie-es";
import { destr } from "destr";

type _CookieOptions = Omit<
  CookieSerializeOptions & CookieParseOptions,
  "decode" | "encode"
>;

export interface CookieOptions<T = any> extends _CookieOptions {
  decode?(value: string): T;
  encode?(value: T): string;
  default?: () => T | Ref<T>;
  watch?: boolean | "shallow";
  /** When true, create a session cookie (no Max-Age/Expires) */
  session?: boolean;
}

export type CookieRef<T> = Ref<T>;

const CookieDefaults: CookieOptions<any> = {
  path: "/",
  watch: true,
  decode: (val) => destr(decodeURIComponent(val)),
  encode: (val) =>
    encodeURIComponent(typeof val === "string" ? val : JSON.stringify(val)),
};

export const useCookie = <T = string | null | undefined>(
  name: string,
  _opts?: CookieOptions<T>
): CookieRef<T> => {
  const opts = { ...CookieDefaults, ...(_opts || {}) };
  const cookies = parse(document.cookie, opts);

  const cookie = ref<T | undefined>((cookies[name] as any) ?? opts.default?.());

  watch(cookie, () => {
    document.cookie = serializeCookie(name, cookie.value, opts);
  });

  return cookie as CookieRef<T>;
};

function serializeCookie(
  name: string,
  value: any,
  opts: CookieSerializeOptions & { session?: boolean } = {}
) {
  if (value === null || value === undefined)
    return serialize(name, value, { ...opts, maxAge: -1 });

  // If session=true, do not set maxAge/expires => session cookie
  if ((opts as any).session)
    return serialize(name, value, {
      ...opts,
      maxAge: undefined,
      expires: undefined,
    });

  // If caller provided maxAge/expires, honor it
  if (typeof opts.maxAge !== "undefined" || typeof opts.expires !== "undefined")
    return serialize(name, value, opts);

  // Default persistence: 30 days
  return serialize(name, value, { ...opts, maxAge: 60 * 60 * 24 * 30 });
}

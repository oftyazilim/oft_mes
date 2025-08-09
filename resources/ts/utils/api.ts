import { ofetch } from "ofetch";

export const $api = ofetch.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || "/api",
  async onRequest({ options }) {
    const accessToken = useCookie("accessToken").value;
    if (accessToken) {
      // Normalize headers to a plain object to avoid type issues with Headers
      const headersObj: Record<string, string> = {};
      const orig = options.headers as
        | Headers
        | Record<string, string>
        | undefined;
      if (orig instanceof Headers) {
        orig.forEach((value, key) => {
          headersObj[key] = value;
        });
      } else if (orig) {
        Object.assign(headersObj, orig);
      }
      headersObj["Authorization"] = `Bearer ${accessToken}`;
      const hdr = new Headers();
      Object.entries(headersObj).forEach(([k, v]) => hdr.set(k, v));
      options.headers = hdr;
    }
  },
});

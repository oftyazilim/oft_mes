import "vue-router";
declare module "vue-router" {
  interface RouteMeta {
    action?: string | string[];
    subject?: string | string[];
    layoutWrapperClasses?: string;
    navActiveLink?: RouteLocationRaw;
    layout?: "blank" | "default";
    unauthenticatedOnly?: boolean;
    public?: boolean;
  }
}
// Global build time (enjekte edilir)
declare const __BUILD_TIME__: string;
// Global app version (enjekte edilir)
declare const __APP_VERSION__: string;
// Global last commit summary (enjekte edilir)
declare const __LAST_COMMIT_SUMMARY__: string;
declare module "vue-router" {
  interface RouteMeta {
    action?: string | string[];
    subject?: string | string[];
    layoutWrapperClasses?: string;
    navActiveLink?: RouteLocationRaw;
    layout?: "blank" | "default";
    unauthenticatedOnly?: boolean;
    public?: boolean;
  }
}

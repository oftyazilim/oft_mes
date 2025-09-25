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

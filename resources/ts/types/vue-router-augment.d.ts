// Augment RouteMeta for unplugin-vue-router definePage typings
import "unplugin-vue-router";
import "unplugin-vue-router/types";

declare module "unplugin-vue-router" {
  interface RouteMeta {
    action?: string | string[];
    subject?: string | string[];
    public?: boolean;
    unauthenticatedOnly?: boolean;
  }
}

declare module "unplugin-vue-router/types" {
  interface RouteMeta {
    action?: string | string[];
    subject?: string | string[];
    public?: boolean;
    unauthenticatedOnly?: boolean;
  }
}

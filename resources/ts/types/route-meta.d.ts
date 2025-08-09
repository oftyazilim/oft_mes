// Route meta augmentation to allow array subjects and custom flags
import "vue-router";

declare module "vue-router" {
  interface RouteMeta {
    action?: string | string[];
    subject?: string | string[];
    public?: boolean;
    unauthenticatedOnly?: boolean;
  }
}

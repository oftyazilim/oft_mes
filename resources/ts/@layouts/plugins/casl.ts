import { getPolicyFor, policyVersion } from "@/plugins/1.router/guards";
import { useAbility } from "@casl/vue";
import type { NavGroup } from "@layouts/types";
import { getCurrentInstance } from "vue";
import type { RouteLocationNormalized } from "vue-router";

/**
 * Returns ability result if ACL is configured or else just return true
 * We should allow passing string | undefined to can because for admin ability we omit defining action & subject
 *
 * Useful if you don't know if ACL is configured or not
 * Used in @core files to handle absence of ACL without errors
 *
 * @param {string} action CASL Actions // https://casl.js.org/v4/en/guide/intro#basics
 * @param {string|string[]} subject CASL Subject(s) // supports single or multiple subjects
 */
export const can = (
  action: string | string[] | undefined,
  subject: string | string[] | undefined
) => {
  const vm = getCurrentInstance();

  if (!vm) return false;

  const localCan = vm.proxy && "$can" in vm.proxy;

  if (!localCan) return true;

  const check = (a: string, s: string) => {
    // @ts-expect-error Using instance proxy $can dynamically
    return vm.proxy?.$can(a, s);
  };

  // Normalize to arrays
  const actions = Array.isArray(action) ? action : action ? [action] : [];
  const subjects = Array.isArray(subject) ? subject : subject ? [subject] : [];

  // If either is empty, fallback to direct check (will end up undefined => false)
  if (!actions.length || !subjects.length) {
    // @ts-expect-error Using instance proxy $can dynamically
    return vm.proxy?.$can(action as any, subject as any);
  }

  // OR across combinations
  return actions.some((a) => subjects.some((s) => check(a, s)));
};

// DB policy anahtarına göre yetki kontrolü; yoksa fallback olarak action/subject kullanır
export const canByPolicyKey = (
  policyKey?: string,
  fallbackAction?: string | string[],
  fallbackSubject?: string | string[]
) => {
  const ability = useAbility();
  // Reaktivite: policy yüklenince yeniden değerlendirme için policyVersion'a dokun
  // eslint-disable-next-line no-unused-expressions
  policyVersion.value;

  if (policyKey) {
    const p = getPolicyFor(policyKey);
    if (p.actions?.length && p.subjects?.length) {
      return p.actions.some((a) => p.subjects.some((s) => ability.can(a, s)));
    }
  }
  // Policy bulunamadıysa veya boşsa fallback'a dön
  if (fallbackAction && fallbackSubject)
    return can(fallbackAction, fallbackSubject);
  // Hiçbiri yoksa görünmesin
  return false;
};

// Tekil nav item için görünürlük kontrolü (policyKey veya action/subject)
export const canViewNavMenuItem = (item: any) => {
  const key = (item && (item as any).policyKey) as string | undefined;
  return canByPolicyKey(key, (item as any)?.action, (item as any)?.subject);
};

/**
 * Check if user can view item based on it's ability
 * Based on item's action and subject & Hide group if all of it's children are hidden
 * @param {object} item navigation object item
 */
export const canViewNavMenuGroup = (item: NavGroup) => {
  const hasAnyVisibleChild = item.children.some((i: any) =>
    canViewNavMenuItem(i)
  );
  // Yeni mantık: En az bir çocuk görünürse grup da görünsün (group policyKey çocukları engellemesin)
  if (hasAnyVisibleChild) return true;

  // Hiç görünür çocuk yoksa, grubu gizle
  return false;
};

export const canNavigate = (to: RouteLocationNormalized) => {
  const ability = useAbility();

  // Get the most specific route (last one in the matched array)
  const targetRoute = to.matched[to.matched.length - 1];

  // console.log("Target route meta:", targetRoute?.meta);  //oft_not
  // console.log("Ability rules:", ability.rules); //oft_not

  // Route meta yoksa DB policy’den dene (key: route:<name>)
  const routeName = to.name ? String(to.name) : "";
  const policyKey = routeName ? `route:${routeName}` : "";
  const routePolicy = policyKey
    ? getPolicyFor(policyKey)
    : { actions: [], subjects: [] };
  const hasAclMeta =
    to.matched.some(
      (route) => (route.meta as any)?.action && (route.meta as any)?.subject
    ) ||
    (routePolicy.actions.length && routePolicy.subjects.length);
  if (!hasAclMeta) return true;

  // If the target route has specific permissions, check those first
  if (
    (targetRoute?.meta?.action && targetRoute?.meta?.subject) ||
    routePolicy.actions.length
  ) {
    const action =
      (targetRoute?.meta?.action as string | string[]) || routePolicy.actions;
    const subject =
      (targetRoute?.meta?.subject as string | string[]) || routePolicy.subjects;
    const actions = Array.isArray(action) ? action : [action];
    const subjects = Array.isArray(subject) ? subject : [subject];
    const canAccess = actions.some((a) =>
      subjects.some((s) => ability.can(a, s))
    );
    console.log("Checking", action, "on", subject, ":", canAccess);
    return canAccess;
  }

  // If no specific permissions, fall back to checking if any parent route allows access
  const hasAnyAccess = to.matched.some((route) => {
    const action = route.meta.action as string | string[] | undefined;
    const subject = route.meta.subject as string | string[] | undefined;
    if (!action || !subject) return false;
    const actions = Array.isArray(action) ? action : [action];
    const subjects = Array.isArray(subject) ? subject : [subject];
    return actions.some((a) => subjects.some((s) => ability.can(a, s)));
  });
  console.log("Has any access:", hasAnyAccess);
  return hasAnyAccess;
};

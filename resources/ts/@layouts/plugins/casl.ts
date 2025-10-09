import { getPolicyFor, policyVersion } from "@/plugins/1.router/guards";
import { normalizeAbilityRules } from "@/utils/ability-normalizer";
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
  // Ability enjekte edilmemişse (plugin henüz yüklenmeden çağrıldıysa) hata fırlatma
  // yerine zarifçe fallback/false dönelim.
  let ability: ReturnType<typeof useAbility> | null = null;
  try {
    ability = useAbility();
  } catch (e) {
    ability = null;
  }
  // Reaktivite: policy yüklenince yeniden değerlendirme için policyVersion'a dokun
  // eslint-disable-next-line no-unused-expressions
  policyVersion.value;

  // Ability yoksa cookie tabanlı OR kontrolü yapalım (userAbilityRules)
  if (!ability) {
    try {
      // Cookie'den kuralları çek ve normalize et
      const raw = useCookie("userAbilityRules").value as any;
      const rules = normalizeAbilityRules(raw);
      const aclDebug =
        typeof window !== "undefined" &&
        localStorage.getItem("acl-debug") === "1";
      if (policyKey) {
        const p = getPolicyFor(policyKey);
        if ((p.actions?.length || 0) && (p.subjects?.length || 0)) {
          // subject aliasları
          const SUBJECT_ALIASES: Record<string, string[]> = {
            arge: ["arge", "planlama"],
          };
          const expandSubjects = (s: string): string[] =>
            SUBJECT_ALIASES[s] ?? [s];
          // OR kombinasyonu: herhangi bir kural eşleşirse true
          let matched: { a: string; s: string } | null = null;
          const ok = p.actions.some((a) =>
            p.subjects.some((s) =>
              expandSubjects(s).some((sx) => {
                const hit = rules.some(
                  (r) => r.action === a && r.subject === sx
                );
                if (hit && !matched) matched = { a, s: sx };
                return hit;
              })
            )
          );
          if (aclDebug) {
            // eslint-disable-next-line no-console
            console.debug("[ACL][cookie-fallback]", {
              policyKey,
              actions: p.actions,
              subjects: p.subjects,
              matched,
            });
          }
          if (ok) return true;
        }
      }
      // Cookie fallback'ta da eşleşme olmadıysa optional fallbackAction/Subject dene
      if (fallbackAction && fallbackSubject)
        return can(fallbackAction, fallbackSubject);
      return false;
    } catch {
      // Cookie erişimi başarısızsa, fallbackAction/Subject varsa dene, yoksa gizle
      if (fallbackAction && fallbackSubject)
        return can(fallbackAction, fallbackSubject);
      return false;
    }
  }

  if (policyKey) {
    const p = getPolicyFor(policyKey);
    if (p.actions?.length && p.subjects?.length) {
      // Subject aliasları: bazı domain adları eş anlamlı kullanılabiliyor.
      // Örn: 'arge' => 'planlama' ile eşdeğer kabul edilsin.
      const SUBJECT_ALIASES: Record<string, string[]> = {
        arge: ["arge", "planlama"],
      };
      const expandSubjects = (s: string): string[] => SUBJECT_ALIASES[s] ?? [s];

      return p.actions.some((a) =>
        p.subjects.some((s) =>
          expandSubjects(s).some((sx) => ability!.can(a as any, sx as any))
        )
      );
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
  // Router guard çalışırken ability henüz sağlanmamış olabilir; güvenli erişim yapalım
  let ability: ReturnType<typeof useAbility> | null = null;
  try {
    ability = useAbility();
  } catch (e) {
    ability = null;
  }

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
    const canAccess = ability
      ? actions.some((a) => subjects.some((s) => ability!.can(a, s)))
      : true; // Ability yoksa engelleme yapma (login akışında router izin vermeli)
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
    return ability
      ? actions.some((a) => subjects.some((s) => ability!.can(a, s)))
      : true;
  });
  console.log("Has any access:", hasAnyAccess);
  return hasAnyAccess;
};

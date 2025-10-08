import { normalizeAbilityRules } from "@/utils/ability-normalizer";
import { canNavigate } from "@layouts/plugins/casl";
import axios from "axios";
import type { RouteNamedMap, _RouterTyped } from "unplugin-vue-router";
import { ref } from "vue";

export const setupGuards = (
  router: _RouterTyped<RouteNamedMap & { [key: string]: any }>
) => {
  // 👉 router.beforeEach
  // Docs: https://router.vuejs.org/guide/advanced/navigation-guards.html#global-before-guards
  router.beforeEach((to) => {
    /*
     * If it's a public route, continue navigation. This kind of pages are allowed to visited by login & non-login users. Basically, without any restrictions.
     * Examples of public routes are, 404, under maintenance, etc.
     */
    if (to.meta.public) return;

    /**
     * Check if user is logged in by checking if token & user data exists in local storage
     * Feel free to update this logic to suit your needs
     */
    const userDataRaw = useCookie("userData").value;
    const accessToken = useCookie("accessToken").value;

    // userData'yı basit string olarak kontrol et
    const userData = userDataRaw;

    const isLoggedIn = !!(userData && accessToken);

    // Policy'leri yalnızca ilk geçişte ve kullanıcı girişliyse arka planda yükle
    if (!_policiesInitOnce && isLoggedIn) {
      _policiesInitOnce = true;
      void loadAbilityPolicies(true);
    }

    // console.log('Cookie check - userData:', !!userData, 'accessToken:', !!accessToken, 'isLoggedIn:', isLoggedIn) //oft_not

    /*
      If user is logged in and is trying to access login like page, redirect to home
      else allow visiting the page
      (WARN: Don't allow executing further by return statement because next code will check for permissions)
     */
    if (to.meta.unauthenticatedOnly) {
      console.log(
        "Checking unauthenticatedOnly page:",
        to.path,
        "isLoggedIn:",
        isLoggedIn
      );
      if (isLoggedIn) {
        console.log("User is logged in, redirecting to analytics");
        // return "/dashboards/analytics";
        return "/home-page";
      } else {
        console.log("User not logged in, allowing access to login page");
        return undefined;
      }
    }

    if (!isLoggedIn) {
      return { name: "login" };
    }

    // Yetkilendirme kontrolü
    // console.log('Checking navigation for:', to.path)  //oft_not
    // console.log("User logged in:", isLoggedIn);  //oft_not
    // Ability'yi cookie'den yükle
    const userAbilityRules = useCookie("userAbilityRules").value as unknown as
      | import("@/utils/ability-normalizer").AbilityRule[]
      | { action?: string[]; subject?: string[] }
      | null;
    const ability = useAbility();

    if (userAbilityRules) {
      const normalizedRules = normalizeAbilityRules(userAbilityRules);
      ability.update(normalizedRules as any);
      // console.log(
      //   "Ability loaded from cookie, rules count:",
      //   ability.rules.length
      // );  //oft_not
    }

    // console.log("User ability rules:", userAbilityRules);  //oft_not

    const canNavigateResult = canNavigate(to);
    // console.log("Can navigate result:", canNavigateResult); //oft_not

    if (!canNavigateResult && to.matched.length) {
      console.log("Redirecting to not-authorized");
      return { name: "not-authorized" };
    }

    // Eğer kullanıcı girişliyse ve hedef sayfa login ise home'a yönlendir
    if (to.name === "login") {
      return { name: "dashboards-analytics" };
    }

    return true;
  });
};

// DB tabanlı ACL meta: policy yükleyici ve yardımcı
let _policyCache: Record<
  string,
  { actions: string[]; subjects: string[] }
> | null = null;
let _policyTs = 0;
const POLICY_TTL = 60_000; // 1 dk cache
let _policiesInitOnce = false;
export const policyVersion = ref(0);

export async function loadAbilityPolicies(force = false) {
  const now = Date.now();
  if (!force && _policyCache && now - _policyTs < POLICY_TTL)
    return _policyCache;
  try {
    const { data } = await axios.get("/api/ability-policies");
    const cache: Record<string, { actions: string[]; subjects: string[] }> = {};
    for (const row of data || []) {
      const key = String(row.key);
      const actions = Array.isArray(row.actions) ? row.actions : [];
      const subjects = Array.isArray(row.subjects) ? row.subjects : [];
      cache[key] = { actions, subjects };
    }
    _policyCache = cache;
    _policyTs = now;
    policyVersion.value++;
    return cache;
  } catch (e) {
    // Sessizce yut: DB policy yoksa mevcut statik meta ile devam
    return _policyCache || {};
  }
}

// Route veya bileşenler için bir anahtardan (key) aksiyon/konu listesi çek
export function getPolicyFor(key: string): {
  actions: string[];
  subjects: string[];
} {
  const cache = _policyCache || {};
  return cache[key] || { actions: [], subjects: [] };
}

export function clearAbilityPolicyCache() {
  _policyCache = null;
  _policyTs = 0;
  policyVersion.value++;
}

export async function refreshAbilityPolicies() {
  clearAbilityPolicyCache();
  return await loadAbilityPolicies(true);
}

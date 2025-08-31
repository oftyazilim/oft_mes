import { normalizeAbilityRules } from "@/utils/ability-normalizer";
import { canNavigate } from "@layouts/plugins/casl";
import type { RouteNamedMap, _RouterTyped } from "unplugin-vue-router";

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

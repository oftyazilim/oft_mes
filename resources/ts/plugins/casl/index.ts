import type { App } from "vue";

import { normalizeAbilityRules } from "@/utils/ability-normalizer";
import { createMongoAbility } from "@casl/ability";
import { abilitiesPlugin } from "@casl/vue";
import type { Rule } from "./ability";

export default function (app: App) {
  // Read ability rules from cookie (already decoded by our useCookie)
  const raw = useCookie<
    Rule[] | { action?: string[]; subject?: string[] } | null
  >("userAbilityRules").value as any;
  const rules = normalizeAbilityRules(raw);
  const initialAbility = createMongoAbility(rules ?? []);

  app.use(abilitiesPlugin, initialAbility, {
    useGlobalProperties: true,
  });
}

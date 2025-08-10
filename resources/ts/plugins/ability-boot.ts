import { normalizeAbilityRules } from "@/utils/ability-normalizer";
import { useAbility } from "@casl/vue";

export function bootAbilityFromCookies() {
  const ability = useAbility();
  // Our useCookie already decodes JSON into object/array via destr
  const raw = useCookie("userAbilityRules").value as any;
  const rules = normalizeAbilityRules(raw);
  if (rules.length) ability.update(rules as any);
}

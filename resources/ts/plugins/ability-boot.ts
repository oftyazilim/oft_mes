import { useAbility } from '@casl/vue'
import { normalizeAbilityRules } from '@/utils/ability-normalizer'

export function bootAbilityFromCookies() {
  const ability = useAbility()
  const raw = useCookie('userAbilityRules').value
  let parsed: any = null
  try {
    parsed = raw ? JSON.parse(raw) : null
  } catch {
    parsed = null
  }
  const rules = normalizeAbilityRules(parsed)
  if (rules.length) ability.update(rules)
}

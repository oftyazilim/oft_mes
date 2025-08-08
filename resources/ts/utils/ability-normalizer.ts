export type AbilityRule = { action: string; subject: string }

export function normalizeAbilityRules(
  input: AbilityRule[] | { action?: string[]; subject?: string[] } | null | undefined,
): AbilityRule[] {
  if (!input) return []

  // Zaten dizi ise (doğru format)
  if (Array.isArray(input)) {
    return input.filter((r: any) => r && typeof r.action === 'string' && typeof r.subject === 'string')
  }

  // { action:[], subject:[] } ise
  const actions = Array.isArray(input.action) ? input.action : []
  const subjects = Array.isArray(input.subject) ? input.subject : []

  if (!actions.length || !subjects.length) return []

  // manage + all varsa tek kural yeterli
  if (actions.includes('manage') && subjects.includes('all')) {
    return [{ action: 'manage', subject: 'all' }]
  }

  // Aksi halde kartesyen çarpım
  const rules: AbilityRule[] = []
  for (const a of actions) for (const s of subjects) rules.push({ action: a, subject: s })
  return rules
}

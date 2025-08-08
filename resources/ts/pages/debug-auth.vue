<script setup lang="ts">
definePage({
  meta: { action: 'manage', subject: 'all' }
})

const userData = useCookie('userData')
const userAbilityRules = useCookie('userAbilityRules')
const ability = useAbility()

const canManageAll = ability.can('manage', 'all')
</script>

<template>
  <VCard title="Debug Authorization">
    <VCardText>
      <VRow>
        <VCol cols="12" md="6">
          <h3>User Data:</h3>
          <pre>{{ JSON.stringify(userData, null, 2) }}</pre>
        </VCol>
        
        <VCol cols="12" md="6">
          <h3>Ability Rules:</h3>
          <pre>{{ JSON.stringify(userAbilityRules, null, 2) }}</pre>
        </VCol>
        
        <VCol cols="12">
          <h3>Can Manage All: {{ canManageAll }}</h3>
        </VCol>
      </VRow>
    </VCardText>
  </VCard>
</template> 

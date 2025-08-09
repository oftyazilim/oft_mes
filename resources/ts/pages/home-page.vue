<template>
  <DevExtremeGridExample />
</template>

<script setup lang="ts">

const userData = useCookie<any>("userData");


definePage({
  meta: { action: ['manage', 'read'], subject: ['montaj', 'all'] }
});

// Log cookie robustly: supports string (email) or object { id, name, email }
console.log('userData cookie raw:', userData.value)
if (typeof userData.value === 'string') {
  console.log('user email from cookie:', userData.value)
} else if (userData.value && typeof userData.value === 'object') {
  console.log('user object from cookie:', {
    id: (userData.value as any)?.id,
    name: (userData.value as any)?.name,
    email: (userData.value as any)?.email,
  })
} else {
  console.log('userData cookie is empty or not set')
}
</script>

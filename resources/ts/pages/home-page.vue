<template>
  <DevExtremeGridExample />
</template>

<script setup lang="ts">

const userData = useCookie<any>("userData");


definePage({
  meta: { action: ['manage', 'read'], subject: ['montaj', 'all'] }
});

console.log(userData.value.register_id);
</script>

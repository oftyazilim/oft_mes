<template>
  <DevExtremeGridExample />
</template>

<script setup lang="ts">

const userData = useCookie<any>("userData");

definePage({
  meta: { action: "manage", subject: "all" },
});

console.log(userData.value.register_id);
</script>

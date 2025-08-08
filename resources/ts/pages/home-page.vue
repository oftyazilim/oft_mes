<template>

</template>




<script setup lang="ts">

import { onMounted, ref } from "vue";

import axios from "axios";

const userData = useCookie<any>('userData')


definePage({
  meta: { action: 'manage', subject: 'all' }
})

console.log(userData.value.register_id)
</script>

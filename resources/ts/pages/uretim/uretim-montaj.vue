<template>
  <VContainer fluid class="pa-0 ma-0">
    <ProductionSlider />
  </VContainer>

</template>

<script setup lang="ts">
import ProductionSlider from './ProductionSlider.vue';

definePage({
  meta: { action: ['manage', 'read'], subject: ['montaj', 'all'] }
})
</script>

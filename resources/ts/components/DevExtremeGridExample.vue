<template>
  <div>
    <DxDataGrid :data-source="data" :show-borders="true">
      <DxColumn data-field="id" caption="ID" />
      <DxColumn data-field="name" caption="Ad" />
      <DxColumn data-field="email" caption="E-posta" />
    </DxDataGrid>
  </div>
</template>

<script setup lang="ts">
import { DxColumn, DxDataGrid } from 'devextreme-vue/data-grid';
import { ref } from 'vue';

const data = ref([
  { id: 1, name: 'Ali Veli2', email: 'ali@demo.com' },
  { id: 2, name: 'Ayşe Yılmaz', email: 'ayse@demo.com' },
  { id: 3, name: 'Mehmet Can', email: 'mehmet@demo.com' },
])
</script>

<style scoped>
/* DevExtreme grid için temel stil */
.dx-datagrid {
  margin-block-start: 24px;
}
</style>

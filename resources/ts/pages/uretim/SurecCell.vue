<template>
  <div style="display: flex; align-items: center; width: 100%;">
    <!-- DevExtreme ProgressBar -->
    <DxProgressBar :value="cellData.value" :show-status="false" style="flex: 1; margin-right: 8px;" showStatus:true,
      statusFormat:function(ratio, value) { return Math.round(ratio * 100) }, />
    <!-- Değer Gösterimi -->
    <span>{{ Math.ceil(cellData.value) }}%</span>
  </div>

</template>
<script setup lang="ts">
import {
  DxBullet,
  DxFont,
  DxMargin,
  DxSize,
  DxTooltip,
} from 'devextreme-vue/bullet';
import { DxDataGridTypes } from 'devextreme-vue/data-grid';
import DxProgressBar from 'devextreme-vue/progress-bar';

defineProps<{
  cellData: DxDataGridTypes.ColumnCellTemplateData
  maxDeger: number
}>();

const customizeTooltip = (data: { value: string }) => ({
  text: `${parseInt(data.value, 10)}`,
});
</script>
<style>
.dx-datagrid .dx-data-row>td.bullet {
  padding-top: 0;
  padding-bottom: 0;
}
</style>

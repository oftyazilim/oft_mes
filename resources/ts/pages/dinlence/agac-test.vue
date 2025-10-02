<template>
  <!-- Ağaç Görünümü (DevExtreme TreeList) -->
  <div class="pa-2">
    <div class="tree-toolbar">
      <VTextField v-model="bomInput" label="BOM ID" density="comfortable" hide-details="auto" class="bom-input"
        placeholder="ör: 120452" type="number" @keyup.enter="loadData" />
      <VBtn color="primary" variant="outlined" @click="loadData" :loading="loading">Göster</VBtn>
      <VBtn color="secondary" variant="outlined" @click="collapseAll">Collapse all</VBtn>
    </div>

    <DxTreeList id="agac" :data-source="treeData" ref="treeList" :allow-column-reordering="true"
      :allow-column-resizing="true" :show-borders="true" key-expr="exploded_id" parent-id-expr="parent_exploded_id"
      :column-auto-width="true" :width="'100%'" class="mt-2" :onRowPrepared="onRowPrepared"
      :focused-row-enabled="false">
      <DxSelection :recursive="true" mode="single" />
      <DxFilterRow :visible="true" />
      <DxStateStoring :enabled="true" type="localStorage" storage-key="treeListStorage" />
      <DxColumn data-field="exploded_id" caption="Exploded ID" :width="170" />
      <DxColumn data-field="parent_exploded_id" caption="Parent ID" :width="120" />
      <DxColumn data-field="exploded_level" caption="Seviye" :width="90" />
      <DxColumn data-field="line_no" caption="Satır No" :width="90" />
      <DxColumn data-field="bom_line_type" caption="Tip" :width="80" />
      <DxColumn data-field="item_code" caption="Stok Kodu" :width="140" />
      <DxColumn data-field="item_name" caption="Stok Adı" :min-width="220" />
      <DxColumn data-field="qty_prm_exploded" caption="Miktar" data-type="number" :width="110" />
      <DxColumn data-field="operation_no" caption="Operasyon" :width="110" />
    </DxTreeList>
  </div>

</template>

<script setup lang="ts">
import {
  DxColumn,
  DxFilterRow,
  DxSelection,
  DxStateStoring,
  DxTreeList,
} from 'devextreme-vue/tree-list';
// import type { GanttPdfExportDateRange, GanttPdfExportMode } from 'devextreme/ui/gantt';
import axios from 'axios';
import "jspdf-autotable";
import { computed, ref } from 'vue';

const treeList = ref<any>(null);
const loading = ref(false)
const bomInput = ref<string>('')
const rows = ref<any[]>([])

// TreeList veri kaynağı: kökleri gösterebilmek için parent_exploded_id = 0 -> null
const treeData = computed<any[]>(() => {
  return (rows.value || []).map(r => ({
    ...r,
    parent_exploded_id: r?.parent_exploded_id === 0 ? null : r?.parent_exploded_id,
  }))
})

async function loadData() {
  const id = (bomInput.value || '').trim()
  if (!id || !/^\d+$/.test(id)) return
  loading.value = true
  try {
    const { data } = await axios.get('/api/bom-exploded', { params: { bom_m_id: Number(id) } })
    rows.value = Array.isArray(data) ? data : []
  } finally {
    loading.value = false
  }
}

function collapseAll() {
  treeList.value?.instance?.state(null);
}

function onRowPrepared(e: any) {
  // Sadece veri satırlarını renklendir
  if (!e || e.rowType !== 'data' || !e.data) return
  const level = Number(e.data?.exploded_level ?? 0)
  const el: HTMLElement | null = e.rowElement as HTMLElement
  if (!el) return

  const palette = [
    { bg: 'rgb(59 30 246 / 70%)', bar: 'rgb(59 130 246 / 90%)' },   // blue-500
    { bg: 'rgb(59 130 246 / 30%)', bar: 'rgb(59 130 246 / 70%)' },   // blue-500
    { bg: 'rgb(59 130 246 / 20%)', bar: 'rgb(59 130 246 / 50%)' },   // blue-500
    { bg: 'rgb(59 130 246 / 10%)', bar: 'rgb(59 130 246 / 30%)' },   // blue-500
    { bg: 'rgb(59 130 246 / 5%)', bar: 'rgb(59 130 246 / 15%)' },   // blue-500
    { bg: 'rgb(59 130 246 / 1%)', bar: 'rgb(59 130 246 / 5%)' },   // blue-500

  ]
  // const palette = [
  //   { bg: 'rgb(59 130 246 / 10%)', bar: 'rgb(59 130 246 / 65%)' },   // blue-500
  //   { bg: 'rgb(16 185 129 / 10%)', bar: 'rgb(16 185 129 / 65%)' },   // emerald-500
  //   { bg: 'rgb(234 179 8 / 10%)',  bar: 'rgb(234 179 8 / 65%)' },    // amber-500
  //   { bg: 'rgb(249 115 22 / 10%)', bar: 'rgb(249 115 22 / 65%)' },   // orange-500
  //   { bg: 'rgb(244 63 94 / 10%)',  bar: 'rgb(244 63 94 / 65%)' },    // rose-500
  //   { bg: 'rgb(168 85 247 / 10%)', bar: 'rgb(168 85 247 / 65%)' },   // violet-500
  // ]
  const idx = ((isFinite(level) ? level : 0) % palette.length + palette.length) % palette.length
  const { bg, bar } = palette[idx]

  // Arka plan ve soldan iç gölge (seviye bandı)
  el.style.background = bg
  el.style.boxShadow = `inset 4px 0 0 0 ${bar}`

  // Seviye 0 için yazı rengi beyaz, diğerlerinde varsayılan
  if (level === 0) {
    el.style.color = '#ffffff'
  } else {
    el.style.color = ''
  }
}
</script>

<style scoped>
#agac {
  display: flex;
  flex-direction: column;
  block-size: 75vh;
}

.tree-toolbar {
  display: flex;
  align-items: center;
  gap: 12px; /* butonlar arasında hafif aralık */
}

.bom-input {
  inline-size: 200px;
}
</style>

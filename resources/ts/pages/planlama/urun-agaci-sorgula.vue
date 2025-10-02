<template>
  <!-- Ağaç Görünümü (DevExtreme TreeList) -->
  <div class="pa-2">
    <div class="tree-toolbar">
      <VTextField v-model="bomInput" label="BOM ID" density="comfortable" hide-details="auto" class="bom-input"
        placeholder="ör: 120452" type="number" @keyup.enter="loadData" />
      <VBtn color="primary" variant="outlined" @click="loadData" :loading="loading">Göster</VBtn>
      <VBtn color="secondary" variant="outlined" @click="expandAll">Düğümleri Aç</VBtn>
      <VBtn color="secondary" variant="outlined" @click="collapseAll">Düğümleri Kapat</VBtn>
      <VTextField v-model="expandLevel" label="Seviye" density="comfortable" hide-details="auto" class="level-input"
        placeholder="ör: 2" type="number" min="1" />
      <VBtn color="secondary" variant="outlined" @click="expandToLevel">Seviyeye kadar aç</VBtn>
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
import { computed, nextTick, ref } from 'vue';

const treeList = ref<any>(null);
const loading = ref(false)
const bomInput = ref<string>('')
const rows = ref<any[]>([])
const expandLevel = ref<string>('2')

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

// Belirli bir düğümü genişlet/kapat (örnek kullanım için)
function expandRowByKey(key: string | number) {
  treeList.value?.instance?.expandRow(key)
}

function collapseRowByKey(key: string | number) {
  treeList.value?.instance?.collapseRow(key)
}

// Tüm ağaçtaki düğümleri genişlet
function expandAll() {
  const inst = treeList.value?.instance
  if (!inst) return
  // Çocukları olan tüm ebeveyn anahtarlarını topla ve genişlet
  const parentKeys = new Set<any>()
  for (const r of rows.value) {
    const pid = r?.parent_exploded_id
    if (pid !== null && pid !== undefined) parentKeys.add(pid)
  }
  nextTick(() => {
    parentKeys.forEach(k => inst.expandRow(k))
  })
}

// Tüm düğümleri kapat
function collapseAll() {
  const inst = treeList.value?.instance
  if (!inst) return
  nextTick(() => {
    for (const r of rows.value) {
      const key = r?.exploded_id
      if (key !== null && key !== undefined) inst.collapseRow(key)
    }
  })
}

// Belirli seviyeye kadar genişlet (root seviye 0 kabul)
function expandToLevel() {
  const inst = treeList.value?.instance
  if (!inst) return

  const n = Number.parseInt((expandLevel.value || '').trim(), 10)
  if (!Number.isFinite(n) || n <= 0) return

  // Önce tamamını kapat
  collapseAll()

  // Ardından seviyeleri sırayla genişlet (0..n-1)
  nextTick(() => {
    for (let level = 0; level < n; level++) {
      for (const r of rows.value) {
        if (Number(r?.exploded_level ?? -1) === level) {
          const key = r?.exploded_id
          if (key !== null && key !== undefined) inst.expandRow(key)
        }
      }
    }
  })
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
  gap: 12px;
  
    /* butonlar arasında hafif aralık */
}

.bom-input {
  inline-size: 200px;
}
</style>

<template>
  <!-- Ağaç Görünümü (DevExtreme TreeList) -->
  <div>
    <div class="tree-toolbar">
      <VTextField v-model="stokKodu" label="Stok Kodu" density="comfortable" hide-details="auto" class="query-input"
        placeholder="örn: ABC-123" type="text" @input="onStokKoduInput" @keyup.enter="loadData" />
      <VTextField v-model="stokAdi" label="Stok Adı" density="comfortable" hide-details="auto" class="query-input"
        placeholder="örn: Vida" type="text" @input="onStokAdiInput" @keyup.enter="loadData" />
      <VTextField v-model="isEmriNo" label="İş Emri No" density="comfortable" hide-details="auto" class="query-input"
        placeholder="örn: IEN-25000000" type="text" @input="onIsEmriNoInput" @keyup.enter="loadData" />
      <VBtn color="primary" variant="outlined" @click="loadData" :loading="loading">Göster</VBtn>
      <VBtn color="secondary" variant="outlined" @click="expandAll">Düğümleri Aç</VBtn>
      <VBtn color="secondary" variant="outlined" @click="collapseAll">Düğümleri Kapat</VBtn>
      <VTextField v-model="miktar" label="Miktar" density="comfortable" hide-details="auto" class="miktar-input"
        placeholder="1" type="number" min="0" step="any" />
      <VBtn color="secondary" variant="outlined" @click="hesapla">Hesapla</VBtn>
      <VBtn color="success" variant="outlined" @click="exportExcel">Excel'e Aktar</VBtn>
    </div>

    <VAlert v-if="!loading && noResultMessage" :type="noResultType || 'info'" variant="tonal" density="comfortable"
      class="mt-2">
      {{ noResultMessage }}
    </VAlert>

    <DxTreeList id="agac" :data-source="treeData" ref="treeList" :allow-column-reordering="true"
      :allow-column-resizing="true" :show-borders="true" key-expr="exploded_id" parent-id-expr="parent_exploded_id"
      :column-auto-width="true" :width="'100%'" class="mt-2" :onRowPrepared="onRowPrepared"
      :onCellPrepared="onCellPrepared" :focused-row-enabled="false">

      <DxSelection :recursive="true" mode="single" />
      <DxFilterRow :visible="true" />
      <DxStateStoring :enabled="true" type="localStorage" storage-key="treeListStorage" />

      <DxColumn data-field="exploded_id" caption="Exploded ID" :width="170" :visible="true" />
      <DxColumn data-field="parent_exploded_id" caption="Parent ID" :width="120" :visible="true" />
      <DxColumn data-field="tipi" caption="Tipi" :width="250" />
      <DxColumn data-field="exploded_level" caption="Seviye" :width="90" :visible="true" />
      <DxColumn data-field="line_no" caption="Satır No" :width="90" />
      <DxColumn data-field="bom_line_type" caption="Tip" :width="80" :visible="false" />
      <DxColumn data-field="item_code" caption="Stok Kodu" :width="140" />
      <DxColumn data-field="item_name" caption="Stok Adı" :min-width="220" />
      <DxColumn data-field="operation_no" caption="Operasyon No" :width="110" />
      <DxColumn data-field="operation_name" caption="Operasyon Adı" :width="180" />
      <DxColumn data-field="qty_prm_exploded" caption="Miktar" data-type="number" :width="110" :format="{
        type: 'fixedPoint',
        precision: 1,
        thousandsSeparator: ',',
      }" />
      <DxColumn data-field="ihtiyac" caption="İhtiyaç" data-type="number" :width="110" :format="{
        type: 'fixedPoint',
        precision: 1,
        thousandsSeparator: ',',
      }" />
      <DxColumn data-field="stok" caption="Stok" data-type="number" :width="110" :format="{
        type: 'fixedPoint',
        precision: 1,
        thousandsSeparator: ',',
      }" />
      <DxColumn data-field="bakiye" caption="Bakiye" data-type="number" :width="110" :format="{
        type: 'fixedPoint',
        precision: 1,
        thousandsSeparator: ',',
      }" />
    </DxTreeList>
  </div>

</template>

<script setup lang="ts">
import axios from 'axios';
import {
  DxColumn,
  DxFilterRow,
  DxSelection,
  DxStateStoring,
  DxTreeList,
} from 'devextreme-vue/tree-list';
import { Workbook } from 'exceljs';
import { saveAs } from 'file-saver';
import "jspdf-autotable";
import { computed, nextTick, ref } from 'vue';

const treeList = ref<any>(null);
const loading = ref(false)
const rows = ref<any[]>([])
const miktar = ref<string>('1')
const appliedMiktar = ref<number>(1)
const bakiyeMiktar = ref<number>(1)
const noResultMessage = ref<string>('')
const noResultType = ref<'warning' | 'error' | 'info' | undefined>(undefined)

// Sorgu inputları
const stokKodu = ref<string>('')
const stokAdi = ref<string>('')
const isEmriNo = ref<string>('')

// TreeList veri kaynağı: kökleri gösterebilmek için parent_exploded_id = 0 -> null
const treeData = computed<any[]>(() => {
  const m = Number(appliedMiktar.value || 1)
  return (rows.value || []).map(r => {
    const qty = Number(r?.qty_prm_exploded ?? 0)
    const stok = Number(r?.stok ?? 0)
    return {
      ...r,
      parent_exploded_id: r?.parent_exploded_id === 0 ? null : r?.parent_exploded_id,
      ihtiyac: Number.isFinite(qty * m) ? qty * m : 0,
      bakiye: Number.isFinite(stok - qty * m) ? stok - qty * m : 0,
    }
  })
})

function hesapla() {
  const n = Number((miktar.value || '').toString().trim())
  appliedMiktar.value = Number.isFinite(n) ? n : 1
}

async function loadData() {
  const code = (stokKodu.value || '').trim()
  const name = (stokAdi.value || '').trim()
  const wo = (isEmriNo.value || '').trim()

  // Hangi input dolu ise ona göre parametre oluştur
  let params: Record<string, any> | null = null
  if (wo) params = { work_order_no: wo }
  else if (code) params = { stock_code: code }
  else if (name) params = { stock_name: name }
  else return // hiçbiri dolu değilse çık

  loading.value = true
  noResultMessage.value = ''
  noResultType.value = undefined
  try {
    const { data } = await axios.get('/api/urun-agaci-sorgula', { params })
    rows.value = Array.isArray(data) ? data : []
    if (rows.value.length === 0) {
      noResultMessage.value = 'Kayıt bulunamadı.'
      noResultType.value = 'warning'
    }
  } catch (e: any) {
    // Sunucu hatasını detaylı incelemek için console'a yaz
    // Not: Üretimde Sentry benzeri bir araca da gönderilebilir.
    // eslint-disable-next-line no-console
    console.error('urun-agaci-sorgula çağrısı hata verdi:', e)

    rows.value = []
    const status = e?.response?.status as number | undefined
    if (status === 401 || status === 419) {
      noResultMessage.value = 'Oturum doğrulaması gerekli. Lütfen giriş yapın ve tekrar deneyin.'
      noResultType.value = 'warning'
    } else if (status === 403) {
      noResultMessage.value = 'Bu işlem için yetkiniz yok.'
      noResultType.value = 'warning'
    } else if (status === 404) {
      noResultMessage.value = 'Servis bulunamadı (404).'
      noResultType.value = 'error'
    } else if (typeof status === 'number' && status >= 500) {
      noResultMessage.value = 'Sunucu tarafında bir hata oluştu. Lütfen daha sonra tekrar deneyin.'
      noResultType.value = 'error'
    } else {
      noResultMessage.value = 'Sorgu sırasında bir hata oluştu.'
      noResultType.value = 'error'
    }
  } finally {
    loading.value = false
  }
}

// Input temizleme mantığı
function onStokKoduInput() {
  if ((stokKodu.value || '').length > 0) {
    stokAdi.value = ''
    isEmriNo.value = ''
  }
}

function onStokAdiInput() {
  if ((stokAdi.value || '').length > 0) {
    stokKodu.value = ''
    isEmriNo.value = ''
  }
}

function onIsEmriNoInput() {
  if ((isEmriNo.value || '').length > 0) {
    stokKodu.value = ''
    stokAdi.value = ''
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

// Seviye bazlı açma kaldırıldı


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

function onCellPrepared(e: any) {
  if (!e || e.rowType !== 'data' || !e.column) return
  if ((e.column.dataField === 'ihtiyac' || e.column.dataField === 'stok' || e.column.dataField === 'bakiye') && e.cellElement) {
    (e.cellElement as HTMLElement).style.fontWeight = '700'
  }
  if (e.column.dataField === 'bakiye' && e.cellElement && Number(e.value) < 0) {
    (e.cellElement as HTMLElement).style.backgroundColor = 'red'
  }
}

async function exportExcel() {
  try {
    const wb = new Workbook()
    const ws = wb.addWorksheet('UrunAgaci')

    // Sütun tanımları
    ws.columns = [
      { header: 'ID', key: 'exploded_id', width: 10 },
      { header: 'Parent ID', key: 'parent_exploded_id', width: 10 },
      { header: 'Tipi', key: 'tipi', width: 25 },
      { header: 'Stok Kodu', key: 'item_code', width: 18 },
      { header: 'Stok Adı', key: 'item_name', width: 36 },
      { header: 'Operasyon No', key: 'operation_no', width: 16 },
      { header: 'Operasyon Adı', key: 'operation_name', width: 24 },
      { header: 'Miktar', key: 'qty_prm_exploded', width: 14 },
      { header: 'İhtiyaç', key: 'ihtiyac', width: 14 },
      { header: 'Stok', key: 'stok', width: 14 },
      { header: 'Bakiye', key: 'bakiye', width: 14 },
    ] as any

    // Başlıkları kalın yap
    ws.getRow(1).font = { bold: true }

    // Verileri yaz
    const data = treeData.value || []
    for (const r of data) {
      const level: number = Number(r?.exploded_level ?? 0)
      const row = ws.addRow({
        exploded_id: r?.exploded_id ?? '',
        parent_exploded_id: r?.parent_exploded_id ?? '',
        tipi: r?.tipi ?? '',
        item_code: r?.item_code ?? '',
        // Stok adını görsel girintiyle yaz (• ve boşluklarla)
        item_name: `${' '.repeat(level * 2)}${level > 0 ? '• ' : ''}${r?.item_name ?? ''}`,
        operation_no: r?.operation_no ?? '',
        operation_name: r?.operation_name ?? '',
        qty_prm_exploded: Number(r?.qty_prm_exploded ?? 0),
        ihtiyac: Number(r?.ihtiyac ?? 0),
        stok: Number(r?.stok ?? 0),
        bakiye: Number(r?.bakiye ?? 0),
      })
      // Outline seviyesi ile hiyerarşi vurgusu
      // @ts-ignore exceljs typings allow outlineLevel on row
      row.outlineLevel = Math.max(0, level)
    }

    // Sayı formatları ve kolon bazında vurgular
    // Yerel sayı formatı (tr-TR benzeri): binlik ayırıcı nokta, ondalık virgül
    // Excel numFmt: . binlik, , ondalık. TR görünümü için hücre stili bölgeye bağlıdır; burada 2 ondalık sağlanır
    const numFmt = '#,##0.00;[Red]-#,##0.00'
    const boldCols = ['ihtiyac', 'stok', 'bakiye']
    for (const c of ['qty_prm_exploded', 'ihtiyac', 'stok', 'bakiye']) {
      const col = ws.getColumn(c)
      // @ts-ignore exceljs typings
      col.numFmt = numFmt
      if (boldCols.includes(c)) {
        // Tüm kolonu kalın yap (başlık zaten bold)
        // @ts-ignore exceljs typings
        col.eachCell({ includeEmpty: true }, (cell: any, rowNumber: number) => {
          if (rowNumber > 1) cell.font = { ...(cell.font || {}), bold: true }
        })
      }
    }

    // Negatif bakiye hücrelerini kırmızı dolgu ile işaretle
    const bakiyeIndex = ws.getColumn('bakiye').number
    ws.eachRow((row, rowNumber) => {
      if (rowNumber === 1) return
      const cell = row.getCell(bakiyeIndex)
      const val = Number(cell.value ?? 0)
      if (Number.isFinite(val) && val < 0) {
        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFFFB3B3' } }
      }
    })

    // Otomatik filtre
    ws.autoFilter = 'A1:K1'

    // Dinamik dosya adı: UrunAgaci_YYYYMMDD.xlsx
    const now = new Date()
    const yyyy = String(now.getFullYear())
    const mm = String(now.getMonth() + 1).padStart(2, '0')
    const dd = String(now.getDate()).padStart(2, '0')
    const filename = `UrunAgaci_${yyyy}${mm}${dd}.xlsx`

    const buffer = await wb.xlsx.writeBuffer()
    saveAs(new Blob([buffer], { type: 'application/octet-stream' }), filename)
  } catch (err) {
    // eslint-disable-next-line no-console
    console.error('Excel aktarımı başarısız:', err)
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

.query-input {
  inline-size: 200px;
}

.miktar-input {
  inline-size: 140px;
}

/* İhtiyaç kolonu değerlerini kalın göster */
.ihtiyac-bold {
  font-weight: 700;
}
</style>

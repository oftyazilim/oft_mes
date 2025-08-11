<template>
  <VCard class="mt-0 pa-0 pt-0">
    <VCardText class="pa-1 ma-2 mt-0">
      <DxContextMenu :data-source="menuItems" :width="200" target="#gridusers" @item-click="itemClick" />

      <DxDataGrid id="gridusers" :data-source="gridData" key-expr="id" :show-borders="true" ref="dataGridRef"
        @rowUpdating="onRowUpdating" @rowRemoving="onRowRemoving" :focused-row-enabled="true" :column-auto-width="false"
        :row-alternation-enabled="true" @exporting="onExporting" :allow-column-reordering="true"
        @contextMenuPreparing="onContextMenuPreparing" @focused-row-changed="onFocusedRowChanged"
        :allow-column-resizing="true" column-resizing-mode="widget" @content-ready="onContentReady">

        <DxColumn data-field="AKTIF" caption="AKTİF" data-type="boolean" :visible="true" :width="70"
          cell-template="aktifTemplate" />
        <DxColumn data-field="id" caption="ID" :allow-editing="false" :visible="true" width="70" />
        <DxColumn data-field="name" caption="İSİM" :allow-editing="true" :visible="true"
          :validation-rules="[{ type: 'required' }]" width="170" />
        <DxColumn data-field="unvan" caption="ÜNVAN" :validation-rules="[{ type: 'required' }]" width="150" />
        <DxColumn data-field="email" caption="E-POSTA" :allow-editing="false" :visible="true" width="150" />
        <DxColumn data-field="email_verified_at" caption="ONAY TARİHİ" data-type="date" :allow-editing="false"
          :width="150" :visible="true" alignment="center" :format="{
            formatter: (date: any) => {
              const formattedDate = new Intl.DateTimeFormat('tr-TR', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
              }).format(new Date(date));

              return formattedDate.replace(/\//g, '.');
            },
          }" />
        <DxColumn data-field="roles" caption="ROLLER" :cell-template="cellTemplate" width="350">
          <!-- <template #cellTemplate="{ data }">
          <span>{{ data.roles }}</span>
        </template> -->
        </DxColumn>
        <DxColumn data-field="proses" caption="PROSES" width="200" />
        <DxColumn data-field="tip" caption="KADEME" :validation-rules="[{ type: 'required' }]" width="150" />
        <!-- <DxLookup :data-source="kademeler" display-expr="tip" value-expr="tip" width="150" /> -->
        <DxColumn data-field="ismerkezi_id" caption="İş Merkezi" width="150" />
        <DxColumn data-field="istasyon_id" caption="İş İstasyonu" width="150" />

        <DxGrouping :auto-expand-all="expandAll" />
        <DxGroupPanel :visible="true" />
        <DxEditing :allow-updating="false" :allow-adding="false" :allow-deleting="false" mode="row" :use-icons="true" />
        <DxHeaderFilter :visible="true" />
        <DxSearchPanel :visible="true" :width="240" placeholder="Ara..." />
        <DxLoadPanel :enabled="true" />
        <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always" />
        <DxExport :enabled="true" :allow-export-selected-data="false" />
        <DxColumnChooser height="540px" :enabled="true" mode="select" title="Sütun Seçici">
          <DxPosition my="right top" at="right bottom" of=".dx-datagrid-column-chooser-button" />
          <DxColumnChooserSearch :enabled="true" :editor-options="editorOptions" />
          <DxColumnChooserSelection :allow-select-all="true" :select-by-click="true" :recursive="true" />
        </DxColumnChooser>

        <DxToolbar>
          <DxItem location="before" name="groupPanel" />
          <DxItem location="before" locate-in-menu="auto" template="collapseTemplate" />
          <DxItem location="before" template="totalRecordTemplate" />
          <DxItem location="after" locate-in-menu="auto" template="yenileTemplate"
            menu-item-template="menuYenileTemplate" @click="getData()" />
          <DxItem name="exportButton" />
          <DxItem name="columnChooserButton" />
          <DxItem name="searchPanel" />
        </DxToolbar>

        <DxSummary>
          <DxGroupItem :align-by-column="true" column="id" summary-type="count" display-format="{0} adet"
            alignment="right" />
          <!--            <DxTotalItem :align-by-column="true" column="isemri_no" summary-type="count" display-format="{0} adet"
              :alignment="right" /> -->
        </DxSummary>

        <template #aktifTemplate="{ data }">
          <template v-if="data.value === '0' || data.value === 0 || data.value === false">
            <VIcon size="20" icon="tabler-x" />
          </template>
          <template v-else>
            <VIcon size="20" icon="tabler-check" />
          </template>
        </template>

        <template #totalRecordTemplate>
          <div class="informer">
            <div class="count">{{ totalRecord }}</div>
            <span>Toplam Kayıt</span>
          </div>
        </template>

        <template #yenileTemplate>
          <DxButton icon="refresh" styling-mode="text" hint="Yenile" id="sayim" />
        </template>
        <template #menuYenileTemplate>
          <div style="display: flex; align-items: center;">
            <i class="dx-icon dx-icon-refresh"></i>
            <span style="margin-inline-start: 8px;">Yenile</span>
          </div>
        </template>

        <template #collapseTemplate>
          <DxButton :icon="expandAll ? 'collapse' : 'expand'" hint="expandAll ? 'Daralt' : 'Genişlet'" width="40"
            height="40" styling-mode="text" @click="toggleExpandAll" />
        </template>

      </DxDataGrid>

    </VCardText>
  </VCard>

  <div class="grids mt-4">


    <div class="list-container">
      <VCard class="pa-2">
        <VCardTitle>Atanmış Roller</VCardTitle>
        <hr>
        <DxList :data-source="users">
          <template #item="{ data }">
            <div>
              <div>{{ data.name }}</div>
            </div>
          </template>
        </DxList>
      </VCard>
    </div>
    <VCard class="perm-card">
      <DxDataGrid class="perm-grid" :data-source="availablePermissions" :show-borders="true"
        :repaint-changes-only="true" key-expr="id" height="100%" ref="availableGridRef">
        <DxPaging :enabled="false" />
        <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always" />
        <DxRowDragging group="permissionsGroup" :on-add="removePermission" />
        <DxColumn data-field="name" caption="Atanmamış İzinler" />
      </DxDataGrid>
    </VCard>
    <VCard class="perm-card">
      <DxDataGrid class="perm-grid" :data-source="assignedPermissions" :show-borders="true" :repaint-changes-only="true"
        key-expr="id" height="100%" ref="assignedGridRef">
        <DxPaging :enabled="false" />
        <DxScrolling mode="virtual" row-rendering-mode="virtual" show-scrollbar="always" />
        <DxRowDragging group="permissionsGroup" :on-add="assignPermission" />
        <DxColumn data-field="name" caption="Atanmış İzinler" />
      </DxDataGrid>
    </VCard>
  </div>
  <AddNewUserDrawer v-model:isDrawerOpen="isAddNewUserDrawerVisible" :userData="modalParametre" />

</template>

<script setup lang="ts">
import AddNewUserDrawer from './kullanici-ekle.vue';
import axios from 'axios';
import DxContextMenu, { DxContextMenuTypes } from 'devextreme-vue/context-menu';
import {
  DxColumn,
  DxColumnChooser,
  DxColumnChooserSearch,
  DxColumnChooserSelection,
  DxDataGrid,
  DxDataGridTypes,
  DxExport,
  DxGrouping,
  DxGroupItem,
  DxGroupPanel,
  DxHeaderFilter,
  DxRowDragging,
  DxScrolling,
  DxSearchPanel,
  DxSummary,
  DxToolbar
} from "devextreme-vue/data-grid";
import { DxLoadPanel } from 'devextreme-vue/load-panel';
import { DxItem } from 'devextreme-vue/tabs';
import { DxTextBoxTypes } from "devextreme-vue/text-box";
import { exportDataGrid } from "devextreme/excel_exporter";
import notify from 'devextreme/ui/notify';
import { Workbook } from "exceljs";
import { saveAs } from "file-saver-es";
import { computed, onMounted, ref, watch } from "vue";
// import { usePageTitleStore } from "@/stores/pageTitle";
import { DxButton } from "devextreme-vue/button";
import DxList from 'devextreme-vue/list';
import Swal from "sweetalert2";

// const pageTitleStore = usePageTitleStore();
// pageTitleStore.setTitle('Kullanıcılar');
// pageTitleStore.setToplam('');
document.title = "OFT - Kullanıcılar";

definePage({
  meta: { action: ['manage', 'read'], subject: ['genel', 'montaj', 'all'] }
})

const apiUrl = '/api/users'; // Backend API rotası
const gridData = ref<any[]>([]);
const kademeler = ref<any[]>([]);
const dataGridRef = ref<DxDataGrid | null>(null);
const isAddNewUserDrawerVisible = ref(false);
const selectedRow = ref<any | null>(null);
const roles = ref([]);
const users = ref<any[]>([]);
type PermissionItem = { id: number; name: string };
const availablePermissions = ref<PermissionItem[]>([]);
const assignedPermissions = ref<PermissionItem[]>([]);
const availableGridRef = ref<DxDataGrid | null>(null);
const assignedGridRef = ref<DxDataGrid | null>(null);
const expandAll = ref(true);
const totalRecord = ref(0);

const onFocusedRowChanged = (e: any) => {
  if (e.rowType === 'group') return;
  const data = e.row!.data!;
  selectedRow.value = data;
  let tempData: any[] = [];
  const roles = (selectedRow.value.roles || "").split(",");
  roles.forEach((role: string) => {
    tempData.push({
      name: role.trim()
    });
  });
  users.value = tempData;
  loadPermissions();
};

// İzinleri yükle
const loadPermissions = async () => {
  if (!selectedRow.value) return;
  const { data } = await axios.get(`/api/users/${selectedRow.value.id}/permissions`);
  assignedPermissions.value = data.assigned;
  availablePermissions.value = data.available;
};

// İzin atama
const assignPermission = async (e: any) => {
  try {
    const permission = e.itemData;
    if (!selectedRow.value || !permission) return;

    assignedPermissions.value.push(permission as PermissionItem);
    availablePermissions.value = availablePermissions.value.filter((p: PermissionItem) => p.id !== permission.id);
    // ensure reactivity and force grid refresh under virtual scrolling
    assignedPermissions.value = [...assignedPermissions.value];
    availablePermissions.value = [...availablePermissions.value];
    // force refresh both grids so UI updates instantly
    assignedGridRef.value?.instance?.refresh();
    availableGridRef.value?.instance?.refresh();
    axios.post(`/api/users/${selectedRow.value.id}/permissions/${permission.id}`);
  }
  catch (error) {
    console.error("İzin atama işlemi başarısız oldu:", error);
  }
};

// İzin kaldırma
const removePermission = async (e: any) => {
  const permission = e.itemData;
  if (!selectedRow.value || !permission) return;

  availablePermissions.value.push(permission as PermissionItem);
  assignedPermissions.value = assignedPermissions.value.filter((p: PermissionItem) => p.id !== permission.id);
  // ensure reactivity and force grid refresh under virtual scrolling
  assignedPermissions.value = [...assignedPermissions.value];
  availablePermissions.value = [...availablePermissions.value];
  // force refresh both grids so UI updates instantly
  assignedGridRef.value?.instance?.refresh();
  availableGridRef.value?.instance?.refresh();
  await axios.delete(`/api/users/${selectedRow.value.id}/permissions/${permission.id}`);
};

const openAddNewUserDrawer = async () => {
  isAddNewUserDrawerVisible.value = true
  await getData();
}

const onRowUpdating = async (e: any) => {
  handleRowUpdating(e);
  await getData();
};

const onRowRemoving = async (e: any) => {
  handleRowRemoving(e);
  await getData();
};

const onContentReady = async (e: any) => {
  const gridInstance = dataGridRef.value?.instance;
  totalRecord.value = gridInstance?.getDataSource()?.totalCount() ?? 0;
};

const editorOptions: DxTextBoxTypes.Properties = { placeholder: "Sütun ara" };

const getData = async () => {
  try {
    const response = await axios.get(apiUrl);
    gridData.value = response.data.data;
    kademeler.value = response.data.kademeler;
    notify(`Veri başarıyla alındı`, 'success', 1500)
  } catch (error) {
    console.error('Error fetching data:', error);
    notify(`Kullanıcılar verisi alınamadı`, 'error', 1500)

  }
};

const cellTemplate = (container: HTMLElement, options: any) => {
  // Hücre verisini alın
  const roles = (options.data.roles || "").split(",");
  container.className = "d-flex gap-2";

  roles.forEach((role: string) => {
    const trimmedRole = role.trim();
    const roleData = colors[trimmedRole] || {};

    const span = document.createElement("span");
    span.textContent = roleData.text || trimmedRole;
    span.style.display = "inline-block";
    span.style.padding = "2px 8px";
    span.style.borderRadius = "8px";
    span.style.backgroundColor = roleData.color || "#ccc";
    span.style.color = "#fff";
    span.style.fontSize = "12px";

    container.appendChild(span);
  });
};

const colors: any = {
  'SuperAdmin': { color: 'red', text: 'SuperAdmin' },
  'Montaj': { color: '#28a745', text: 'Montaj' },
  'Planlama': { color: '#ffc107', text: 'Planlama' },
  'Satınalma': { color: '#ffc107', text: 'Satınalma' },
  'Satış': { color: '#ffc107', text: 'Satış' },
  'user': { color: 'magenta', text: 'user' },
  'Dashboard': { color: '#007bff', text: 'Dashboard' },
  'all': { color: '#ac3545', text: 'all' },
}

const handleRowUpdating = (e: any) => {
  e.promise = new Promise<void>(async (resolve, reject) => {
    try {
      const updatedRow = { ...e.oldData, ...e.newData }; // Eski ve yeni veriyi birleştir
      await axios.put(`${apiUrl}/${e.key}`, updatedRow); // API çağrısı
      await getData(); // Veriyi tekrar yenile
      e.component.cancelEditData(); // Edit modundan çık
      resolve(); // İşlemi başarıyla tamamla
    } catch (error) {
      console.error('Error updating data:', error);
      reject(); // İşlemi başarısız olarak bildir
    }
  });
};

const handleRowRemoving = (e: any) => {
  e.promise = new Promise<void>(async (resolve, reject) => {
    try {
      await axios.delete(`${apiUrl}/${e.key}`); // Veriyi sil
      await getData(); // Veriyi tekrar yenile
      e.component.cancelEditData(); // Edit modundan çık
      resolve(); // İşlemi başarıyla tamamla
    } catch (error) {
      console.error('Error deleting data:', error);
      reject(); // İşlemi başarısız olarak bildir
    }
  });
};

onMounted( async () => {
  await getData();
});

watch(isAddNewUserDrawerVisible, (newValue, oldValue) => {
  if (!newValue && oldValue) {
    if (!isAddNewUserDrawerVisible.value) getData();
  }
});

function onContextMenuPreparing(e: any) {
  selectedRow.value = e.row.data;
  // selectedRow.value = {
  //   id: e.row.data.id,
  //   name: e.row.data.name,
  //   email: e.row.data.email,
  //   unvan: e.row.data.unvan,
  //   ismerkezi: e.row.data.is_merkezi_id,
  //   istasyon: e.row.data.istasyon_id,
  //   proses: e.row.data.proses,
  //   tip: e.row.data.tip,
  //   roles: e.row.data.roles,
  //   aktif: e.row.data.AKTIF,
  // };
}

const modalParametre = computed(() => {
  const selected = selectedRow.value || {};
  return {
    id: selected.id || 0,
    name: selected.name || '',
    email: selected.email || '',
    unvan: selected.unvan || '',
    ismerkezi: selected.ismerkezi_id || '',
    istasyon: selected.istasyon_id || '',
    proses: selected.proses || '',
    tip: selected.tip || '',
    roles: selected.roles || '',
  };
});

const menuItems = [
  { text: 'Yenile' },
  { text: 'Kullanıcı Ekle' },
  { text: 'Kullanıcı Düzenle' },
  { text: 'Aktif/Pasif Yap' },
  { text: 'Şifreyi Sıfırla' },
];

const sifreSifirla = async (id: any) => {
  try {
    const response = await axios.put(`/api/sifresifirla/${id}`);
  }
  catch (error) {
    console.error("Veri silinirken hata oluştu: ", error);
  }
};

const toggleExpandAll = () => {
  expandAll.value = !expandAll.value;
};

function itemClick({ itemData }: DxContextMenuTypes.ItemClickEvent) {
  if (!itemData?.items) {
    switch (itemData?.text) {
      case 'Yenile':
        getData()
        break;
      case 'Kullanıcı Ekle':
        selectedRow.value = null;
        openAddNewUserDrawer();
        break;
      case 'Kullanıcı Düzenle':
        openAddNewUserDrawer();
        break;
      case 'Aktif/Pasif Yap':
        // if (parseInt(selectedRow.value.AKTIF) === 0) {
        //   Swal.fire({
        //     title: "Olamaz...",
        //     confirmButtonColor: "#88efb3",
        //     text: "Zaten Pasif!",
        //     icon: "error",
        //   });
        //   return;
        // }
        const swalWithBootstrapButtons = Swal.mixin({
          customClass: {
            confirmButton: "btn btn-primary",
            cancelButton: "btn btn-error",
          },
          buttonsStyling: true,
        });
        swalWithBootstrapButtons
          .fire({
            title: "Emin misiniz?",
            text: "İstersen sonra yine değiştirirsin :)",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#88efb3",
            cancelButtonColor: "#ed9595",
            reverseButtons: true,
            confirmButtonText: "Evet, yap gitsin!",
            cancelButtonText: "Hayır, vazcaydım!",
          })
          .then((result: any) => {
            if (result.isConfirmed) {
              durumDegistir(selectedRow.value.id, selectedRow.value.AKTIF);
              swalWithBootstrapButtons.fire({
                confirmButtonColor: "#88efb3",
                title: "Durum değiştirildi!",
                text: "Yoksa pişman mı oldun :)",
                icon: "success",
              });
            } else if (
              /* Read more about handling dismissals below */
              result.dismiss === (Swal as any).DismissReason.cancel
            ) {
              swalWithBootstrapButtons.fire({
                confirmButtonColor: "#88efb3",
                title: "Vazgeçildi",
                text: "Birşey yapmadım merak etmeyin :)",
                icon: "error",
              });
            }
          });

        break;
      case 'Şifreyi Sıfırla':
        const swalWithBootstrapButtons1 = Swal.mixin({
          customClass: {
            confirmButton: "btn btn-primary",
            cancelButton: "btn btn-error",
          },
          buttonsStyling: true,
        });
        swalWithBootstrapButtons1
          .fire({
            title: "Emin misiniz?",
            text: "Şifre sıfırlanacak!!!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#88efb3",
            cancelButtonColor: "#ed9595",
            reverseButtons: true,
            confirmButtonText: "Evet, yap gitsin!",
            cancelButtonText: "Hayır, vazcaydım!",
          })
          .then((result: any) => {
            if (result.isConfirmed) {
              sifreSifirla(selectedRow.value.id);
              swalWithBootstrapButtons1.fire({
                confirmButtonColor: "#88efb3",
                title: "Şifre Sıfırlandı!",
                text: "pass1234",
                icon: "success",
              });
            } else if (
              /* Read more about handling dismissals below */
              result.dismiss === (Swal as any).DismissReason.cancel
            ) {
              swalWithBootstrapButtons1.fire({
                confirmButtonColor: "#88efb3",
                title: "Vazgeçildi",
                text: "Birşey yapmadım merak etmeyin :)",
                icon: "error",
              });
            }
          });
        break;
      default:
        break;
    }
  }
}

const userData = useCookie<any>("userData");

const durumDegistir = async (id: any, aktif: any) => {
  try {
    const response = await axios.put(`/api/users/durumdegistir/${id}`, {
      userID: userData.value.id,
      aktif: parseInt(aktif),
    });
    // console.log("Veri başarıyla silindi", response);
    await getData();
  }
  catch (error) {
    console.error("Durum değiştirilirken hata oluştu: ", error);
  }
};

const aktifYap = async (id: any) => {
  try {
    const response = await axios.put(`/api/userpasif/${id}`, {
      userID: userData.value.id,
    });
    // console.log("Veri başarıyla silindi", response);
    getData();
  }
  catch (error) {
    console.error("Veri silinirken hata oluştu: ", error);
  }
};
const onExporting = (e: DxDataGridTypes.ExportingEvent) => {
  const workbook = new Workbook();
  const worksheet = workbook.addWorksheet("Kullanicilar");

  exportDataGrid({
    component: e.component,
    worksheet,
    autoFilterEnabled: true,
  }).then(() => {
    workbook.xlsx.writeBuffer().then((buffer: any) => {
      saveAs(
        new Blob([buffer], { type: "application/octet-stream" }),
        "Kullanicilar.xlsx"
      );
    });
  });

  e.cancel = true;
};

</script>


<style scoped>
#gridusers {
  display: flex;
  flex-direction: column;
  block-size: 50vh;
}

.grids {
  display: flex;
  justify-content: space-between;
  block-size: 32vh;
  gap: 16px;
}

.informer {
  display: grid;
  grid-template-columns: 100%;
  inline-size: 120px;
  padding-inline-end: 20px;
  text-align: center;
}

.count {
  font-size: 18px;
  font-weight: 500;
}

.gridusers .dx-toolbar-items-container {
  min-block-size: 44px;
}

.dx-list-item-content>div {
  padding: 5px;
  font-size: 15px;
}

.caption {
  font-size: 18px;
  font-weight: 500;
}

.option {
  margin-block-start: 20px;
}

/* Allow permission grids to stretch and scroll internally */
.perm-card {
  display: flex;
  flex: 1 1 0;
  flex-direction: column;
  block-size: 100%;
  min-inline-size: 0;
}

.perm-grid {
  block-size: 100%;
}
</style>

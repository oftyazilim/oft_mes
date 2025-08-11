<template>
  <div>
    <AppSelect v-model="selectedUser" :hint="`${selectedUser.id}, ${selectedUser.name}`" :items="users"
      item-title="name" item-value="id" label="Kullanıcı Seç" persistent-hint return-object single-line
      placeholder="Seçiniz..." @update:modelValue="loadPermissions" />

    <div class="grids mt-3">
      <!-- Atılmamış izinler Grid'i -->
      <DxDataGrid :data-source="availablePermissions" :height="440" :show-borders="true">
        <DxRowDragging group="permissionsGroup" :on-add="removePermission" />
        <DxColumn data-field="name" caption="Atanmamış İzinler" />
      </DxDataGrid>
      <!-- Atılmamış izinler Grid'i -->
      <DxDataGrid :data-source="availablePermissions" :height="440" :show-borders="true">
        <DxRowDragging group="permissionsGroup" :on-add="removePermission" />
        <DxColumn data-field="name" caption="Atanmamış İzinler" />
      </DxDataGrid>

      <!-- Atanmış izinler Grid'i -->
      <DxDataGrid :data-source="assignedPermissions" :height="440" :show-borders="true">
        <DxRowDragging group="permissionsGroup" :on-add="assignPermission" />
        <DxColumn data-field="name" caption="Atanmış İzinler" />
      </DxDataGrid>
    </div>
  </div>
</template>


<script setup lang="ts">
import { ref } from "vue";
import {
  DxDataGrid, DxColumn, DxRowDragging,
} from "devextreme-vue/data-grid";
import axios from "axios";

// Kullanıcı ve izin verileri
const users = ref([])
const selectedUser = ref([])
const availablePermissions = ref([]);
const assignedPermissions = ref([]);

import { onMounted } from "vue";

// Kullanıcıları API'den yükle
const loadUsers = async () => {
  try {
    const { data } = await axios.get("/api/users");
    users.value = data.data;
  } catch (error) {
    console.error("Kullanıcılar yüklenirken hata oluştu:", error);
  }
};

// Sayfa yüklendiğinde kullanıcıları yükle
onMounted(() => {
  loadUsers();
});

// Kullanıcı seçildiğinde izinleri yükle
const loadPermissions = async () => {
  if (!selectedUser.value) return;
  const { data } = await axios.get(`/api/users/${selectedUser.value.id}/permissions`);
  assignedPermissions.value = data.assigned;
  availablePermissions.value = data.available;
};

// İzin atama
const assignPermission = (e: any) => {
  try {
    const permission = e.itemData;
    if (!selectedUser.value || !permission) return;

    assignedPermissions.value.push(permission);
    availablePermissions.value = availablePermissions.value.filter((p) => p.id !== permission.id);
    assignedPermissions.value = [...assignedPermissions.value];
    availablePermissions.value = [...availablePermissions.value];
    axios.post(`/api/users/${selectedUser.value.id}/permissions/${permission.id}`);
  }
  catch (error) {
    console.error("İzin atama işlemi başarısız oldu:", error);
  }
};

// İzin kaldırma
const removePermission = async (e: any) => {
  const permission = e.itemData;
  if (!selectedUser.value || !permission) return;

  availablePermissions.value.push(permission);
  assignedPermissions.value = assignedPermissions.value.filter((p) => p.id !== permission.id);
  assignedPermissions.value = [...assignedPermissions.value];
  availablePermissions.value = [...availablePermissions.value];
  await axios.delete(`/api/users/${selectedUser.value.id}/permissions/${permission.id}`);
};

</script>


<style lang="scss">
.grids {
  display: flex;
  justify-content: space-between;
  gap: 16px;
}

.dx-datagrid {
  width: 100%;
}
</style>

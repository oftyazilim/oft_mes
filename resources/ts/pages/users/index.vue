<script setup lang="ts">
import UserFormDrawer from './UserFormDrawer.vue';

definePage({
  meta: { action: ['manage', 'read'], subject: ['montaj', 'all'] }
});

const itemsPerPage = ref(10)
const page = ref(1)
const searchQuery = ref('')
const selectedRole = ref('all')
const sortBy = ref('name')
const orderBy = ref<'asc' | 'desc'>('asc')

const isDrawerOpen = ref(false)
const editUser = ref<any | null>(null)

const headers = [
  { title: 'Ad Soyad', key: 'name' },
  { title: 'E-posta', key: 'email' },
  { title: 'Roller', key: 'roles' },
  { title: 'İzinler', key: 'permissions' },
  { title: 'İşlemler', key: 'actions', sortable: false },
]

const buildQuery = () => ({
  page: page.value,
  itemsPerPage: itemsPerPage.value,
  search: searchQuery.value,
  role: selectedRole.value,
  sortBy: sortBy.value,
  orderBy: orderBy.value,
})

// DevExtreme DataGrid setup
import { DxColumn, DxDataGrid, DxLoadPanel, DxScrolling, DxSearchPanel, DxSorting } from 'devextreme-vue/data-grid';
import CustomStore from 'devextreme/data/custom_store';

const gridRef = ref<any>(null)
const error = ref<any>(null)

const store = new CustomStore({
  key: 'id',
  load: async (loadOptions: any) => {
    error.value = null
    try {
      const take = loadOptions?.take || itemsPerPage.value
      const skip = loadOptions?.skip ?? (page.value - 1) * take
      const currentPage = Math.floor(skip / take) + 1
      const sort = Array.isArray(loadOptions?.sort) ? loadOptions.sort[0] : null
      const sortByKey = (typeof sort?.selector === 'string' ? sort.selector : undefined) || sortBy.value
      const order = sort?.desc ? 'desc' : orderBy.value

      const res = await $api('/users', {
        params: {
          page: currentPage,
          itemsPerPage: take,
          search: searchQuery.value,
          role: selectedRole.value,
          sortBy: sortByKey,
          orderBy: order,
        },
      })

      // sync reactive state
      itemsPerPage.value = take
      page.value = currentPage
      sortBy.value = sortByKey
      orderBy.value = order

      return {
        data: res.users || [],
        totalCount: res.totalUsers || 0,
      }
    } catch (e: any) {
      error.value = e?.message || 'Liste yüklenemedi'
      return { data: [], totalCount: 0 }
    }
  },
})

const dataSource = store

const rolesOptions = ref<{ title: string; value: string }[]>([])

onMounted(async () => {
  const res = await $api('/roles/available')
  rolesOptions.value = [{ title: 'Tümü', value: 'all' }, ...res.roles.map((r: any) => ({ title: r.name, value: r.name }))]
})

const onAddUser = () => { editUser.value = null; isDrawerOpen.value = true }
const onEditUser = (row: any) => { editUser.value = row; isDrawerOpen.value = true }

const onDeleteUser = async (row: any) => {
  await $api(`/users/${row.id}`, { method: 'DELETE' })
  gridRef.value?.instance?.refresh()
}

const displayNames = (arr?: any[]) => (arr || []).map(x => x.name || x).join(', ')
const rolesCell = (row: any) => displayNames(row?.roles)
const permissionsCell = (row: any) => displayNames(row?.permissions)

const onSaved = () => {
  gridRef.value?.instance?.refresh()
}

// manual filter triggers
watch([searchQuery, selectedRole], () => {
  gridRef.value?.instance?.refresh()
})
</script>

<template>
  <section>
    <VCard class="mb-6">
      <VCardItem class="pb-4">
        <VCardTitle>Kullanıcılar</VCardTitle>
      </VCardItem>

      <VCardText>
        <VRow class="align-center">
          <VCol cols="12" md="4">
            <AppTextField v-model="searchQuery" placeholder="Ara: ad, e-posta" clearable />
          </VCol>
          <VCol cols="12" md="3">
            <AppSelect v-model="selectedRole" :items="rolesOptions" placeholder="Rol" clearable />
          </VCol>
          <VCol cols="12" md="5" class="d-flex justify-end gap-3">
            <VBtn prepend-icon="tabler-plus" @click="onAddUser">Yeni Kullanıcı</VBtn>
          </VCol>
        </VRow>
      </VCardText>

      <VDivider class="my-0 py-0" />

      <VCardText class="my-0 py-0">
        <VAlert v-if="error" type="error" variant="tonal" class="mb-1">
          Liste yüklenemedi: {{ error }}
        </VAlert>
        <DxDataGrid
          ref="gridRef"
          :data-source="dataSource"
          key-expr="id"
          :remote-operations="{ paging: true, sorting: true }"
          :row-alternation-enabled="true"
          :hover-state-enabled="true"
          :column-auto-width="false"
          :allow-column-resizing="true"
          :height="700"
          class="dx-theme-material mt-0"
        >
          <DxLoadPanel :enabled="true" />
          <DxSearchPanel :visible="true" />
          <DxSorting mode="single" />
          <DxScrolling mode="virtual" />

          <DxColumn data-field="name" caption="Ad Soyad" />
          <DxColumn data-field="email" caption="E-posta" />
          <DxColumn caption="Roller" :calculate-cell-value="rolesCell" />
          <DxColumn caption="İzinler" :calculate-cell-value="permissionsCell" />
          <DxColumn caption="İşlemler" :width="130" :allow-sorting="false" cell-template="actionsCell" />

          <template #actionsCell="{ data }">
            <div class="d-flex gap-1">
              <IconBtn @click="onEditUser(data.data)"><VIcon icon="tabler-pencil" /></IconBtn>
              <IconBtn @click="onDeleteUser(data.data)"><VIcon icon="tabler-trash" /></IconBtn>
            </div>
          </template>
        </DxDataGrid>
      </VCardText>
    </VCard>

  <UserFormDrawer v-model="isDrawerOpen" :edit-user="editUser" @saved="onSaved" />
  </section>
</template>

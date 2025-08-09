<script setup lang="ts">
import UserFormDrawer from './UserFormDrawer.vue';

definePage({
  meta: { action: ['manage', 'read', 'manage_users', 'view_users'], subject: ['montaj', 'all'] }
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

const url = createUrl('/users', { query: buildQuery() })
const { data, execute: fetchUsers, isFetching, error } = await useApi<any>(url)

watch(url, (u) => {
  console.debug('[Users] fetch URL =>', u)
})

watch([page, itemsPerPage, searchQuery, selectedRole, sortBy, orderBy], () => {
  fetchUsers()
})

const users = computed(() => data.value?.users || [])
const totalUsers = computed(() => data.value?.totalUsers || 0)

const rolesOptions = ref<{ title: string; value: string }[]>([])

onMounted(async () => {
  const res = await $api('/roles/available')
  rolesOptions.value = [{ title: 'Tümü', value: 'all' }, ...res.roles.map((r: any) => ({ title: r.name, value: r.name }))]
})

const onAddUser = () => { editUser.value = null; isDrawerOpen.value = true }
const onEditUser = (row: any) => { editUser.value = row; isDrawerOpen.value = true }

const onDeleteUser = async (row: any) => {
  await $api(`/users/${row.id}`, { method: 'DELETE' })
  fetchUsers()
}

const displayNames = (arr?: any[]) => (arr || []).map(x => x.name || x).join(', ')

const updateOptions = (opts: any) => {
  sortBy.value = opts?.sortBy?.[0]?.key || 'name'
  // VDataTableServer uses 'asc' | 'desc'
  orderBy.value = opts?.sortBy?.[0]?.order === 'desc' ? 'desc' : 'asc'
}
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

      <VDivider />

      <VCardText>
        <VAlert v-if="error" type="error" variant="tonal" class="mb-4">
          Liste yüklenemedi: {{ (error as any)?.message || error }}
        </VAlert>
        <VDataTableServer
          :items="users"
          :items-length="totalUsers"
          :headers="headers"
          v-model:items-per-page="itemsPerPage"
          v-model:page="page"
          item-value="id"
          class="text-no-wrap"
          :loading="isFetching"
          @update:options="updateOptions"
        >
          <template #item.roles="slotProps">
            <span>{{ displayNames((slotProps as any).item.roles) }}</span>
          </template>
          <template #item.permissions="slotProps">
            <span>{{ displayNames((slotProps as any).item.permissions) }}</span>
          </template>
          <template #item.actions="{ item }">
            <div class="d-flex gap-1">
              <IconBtn @click="onEditUser(item)"><VIcon icon="tabler-pencil" /></IconBtn>
              <IconBtn @click="onDeleteUser(item)"><VIcon icon="tabler-trash" /></IconBtn>
            </div>
          </template>
        </VDataTableServer>
      </VCardText>
    </VCard>

  <UserFormDrawer v-model="isDrawerOpen" :edit-user="editUser" @saved="fetchUsers()" />
  </section>
</template>

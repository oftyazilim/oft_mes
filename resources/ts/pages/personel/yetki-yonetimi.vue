<template>
  <VContainer fluid class="pa-2">
    <VCard>
      <VCardTitle>Kullanıcı - Rol - İzin Yönetimi</VCardTitle>
      <VCardSubtitle>Sayfa ve aksiyon erişimlerini yönetin</VCardSubtitle>

      <VTabs v-model="activeTab" class="px-2">
        <VTab value="users">Kullanıcılar</VTab>
        <VTab value="roles">Roller</VTab>
        <VTab value="perms">İzinler</VTab>
      </VTabs>

      <VWindow v-model="activeTab">
        <!-- Kullanıcılar -->
        <VWindowItem value="users">
          <VCardText>
            <div class="d-flex flex-wrap gap-4">
              <div style="max-inline-size: 380px; min-inline-size: 320px;">
                <VTextField v-model="userSearch" label="Kullanıcı ara" density="compact" clearable />
                <VList lines="two" rounded border style="overflow: auto; max-block-size: 60vh;">
                  <VListItem v-for="u in filteredUsers" :key="u.id" :active="u.id === selectedUserId"
                    @click="selectUser(u)">
                    <VListItemTitle>{{ u.name }}</VListItemTitle>
                    <VListItemSubtitle>{{ u.email }}</VListItemSubtitle>
                  </VListItem>
                </VList>
              </div>

              <div class="flex-grow-1">
                <VCard variant="tonal" class="mb-4">
                  <VCardTitle>Rol Atama</VCardTitle>
                  <VCardText>
                    <div class="d-flex align-center gap-3 flex-wrap">
                      <VSelect v-model="userRoles" :items="allRoles" item-title="name" item-value="name" label="Roller"
                        multiple chips clearable hide-selected style="min-inline-size: 260px;" />
                      <VBtn :disabled="!selectedUserId" color="primary" @click="saveUserRoles">
                        Kaydet</VBtn>
                      <span v-if="!selectedUserId" class="text-medium-emphasis">Bir kullanıcı
                        seçiniz</span>
                    </div>
                  </VCardText>
                </VCard>

                <VCard variant="tonal">
                  <VCardTitle>İzin Atama</VCardTitle>
                  <VCardText>
                    <div class="d-flex gap-6 flex-wrap">
                      <div style="min-inline-size: 260px;">
                        <div class="d-flex align-center justify-space-between">
                          <div class="text-subtitle-2 mb-1">Atanmış İzinler</div>
                          <VTextField v-model="userPermSearch" label="Ara" density="compact" hide-details="auto"
                            clearable style="inline-size: 160px;" />
                        </div>
                        <VList rounded border style="overflow: auto; max-block-size: 36vh;">
                          <VListItem v-for="p in filteredAssignedPerms" :key="p.id">
                            <div class="d-flex align-center justify-space-between w-100">
                              <span>{{ p.name }}</span>
                              <VBtn size="small" variant="text" color="error" @click="removePerm(p.id)">Kaldır</VBtn>
                            </div>
                          </VListItem>
                          <VListItem v-if="filteredAssignedPerms.length === 0" title="İzin yok" />
                        </VList>
                      </div>
                      <div style="min-inline-size: 260px;">
                        <div class="text-subtitle-2 mb-1">Atanabilir İzinler</div>
                        <VList rounded border style="overflow: auto; max-block-size: 36vh;">
                          <VListItem v-for="p in filteredAvailablePerms" :key="p.id">
                            <div class="d-flex align-center justify-space-between w-100">
                              <span>{{ p.name }}</span>
                              <VBtn size="small" variant="text" color="primary" @click="addPerm(p.id)">Ekle</VBtn>
                            </div>
                          </VListItem>
                          <VListItem v-if="filteredAvailablePerms.length === 0" title="İzin yok" />
                        </VList>
                      </div>
                    </div>
                  </VCardText>
                </VCard>
              </div>
            </div>
          </VCardText>
        </VWindowItem>

        <!-- Roller -->
        <VWindowItem value="roles">
          <VCardText>
            <div class="d-flex gap-4 flex-wrap">
              <div style="max-inline-size: 380px; min-inline-size: 320px;">
                <div class="d-flex gap-2 mb-2">
                  <VTextField v-model="newRoleName" label="Yeni rol adı" density="compact" />
                  <VBtn color="primary" @click="createRole">Ekle</VBtn>
                </div>
                <VTextField v-model="roleSearch" label="Rol ara" density="compact" clearable class="mb-2" />
                <VList rounded border style="overflow: auto; max-block-size: 60vh;">
                  <VListItem v-for="r in filteredRoles" :key="r.id" :active="r.id === selectedRoleId"
                    @click="selectRole(r)">
                    <VListItemTitle>{{ r.name }}</VListItemTitle>
                    <VListItemSubtitle>{{ r.users_count ?? 0 }} kullanıcı</VListItemSubtitle>
                  </VListItem>
                </VList>
              </div>

              <div class="flex-grow-1">
                <VCard variant="tonal">
                  <VCardTitle>Rol İzinleri</VCardTitle>
                  <VCardText>
                    <div class="d-flex align-center gap-3 flex-wrap mb-3">
                      <VSelect v-model="selectedTemplateKey" :items="permTemplates" item-title="label" item-value="key"
                        label="Şablon uygula" density="compact" style="min-inline-size: 220px;" />
                      <VBtn :disabled="!selectedTemplateKey" color="secondary" variant="tonal" @click="applyTemplate">
                        Uygula</VBtn>
                    </div>
                    <div class="d-flex align-center gap-3 flex-wrap">
                      <VCombobox v-model="rolePerms" :items="allPerms" item-title="name" item-value="name"
                        label="İzinler" multiple chips clearable hide-selected style="min-inline-size: 320px;" />
                      <VBtn :disabled="!selectedRoleId" color="primary" @click="saveRolePerms">
                        Kaydet</VBtn>
                      <VBtn :disabled="!selectedRoleId" color="error" variant="tonal" @click="deleteRole">Rolü Sil
                      </VBtn>
                      <span v-if="!selectedRoleId" class="text-medium-emphasis">Bir rol
                        seçiniz</span>
                    </div>
                    <VDivider class="my-4" />
                    <div class="d-flex gap-6 flex-wrap">
                      <div style="min-inline-size: 260px;">
                        <div class="text-subtitle-2 mb-1">Bu Role Atanmış İzinler</div>
                        <VList rounded border style="overflow: auto; max-block-size: 36vh;">
                          <VListItem v-for="p in roleAssignedPerms" :key="p.id" :title="p.name" />
                          <VListItem v-if="roleAssignedPerms.length === 0" title="İzin yok" />
                        </VList>
                      </div>
                    </div>
                  </VCardText>
                </VCard>
              </div>
            </div>
          </VCardText>
        </VWindowItem>

        <!-- İzinler -->
        <VWindowItem value="perms">
          <VCardText>
            <div class="d-flex gap-4 flex-wrap">
              <div class="flex-grow-1" style="max-inline-size: 560px; min-inline-size: 380px;">
                <div class="d-flex gap-2 mb-2 flex-wrap">
                  <VSelect v-model="permAction" :items="actions" label="Aksiyon" density="compact"
                    style="min-inline-size: 160px;" />
                  <VSelect v-model="permSubject" :items="subjects" label="Konu (Sayfa/Modül)" density="compact"
                    style="min-inline-size: 220px;" />
                  <VBtn color="primary" @click="createCompositePerm">İzin Oluştur</VBtn>
                </div>
                <VTextField v-model="customPerm" label="Özel izin adı (örn: read_montaj)" density="compact" />
                <div class="mt-2">
                  <VBtn color="primary" @click="createCustomPerm">Ekle</VBtn>
                </div>

                <VDivider class="my-4" />

                <VTextField v-model="permSearch" label="İzin ara" density="compact" clearable />
                <VList rounded border style="overflow: auto; max-block-size: 52vh;">
                  <VListItem v-for="p in filteredPerms" :key="p.id">
                    <div class="d-flex align-center justify-space-between w-100">
                      <span>{{ p.name }}</span>
                      <VBtn size="small" color="error" variant="text" @click="removeCustomPerm(p.id)">Sil</VBtn>
                    </div>
                  </VListItem>
                </VList>
              </div>

              <div style="min-inline-size: 360px;">
                <VAlert type="info" variant="tonal" title="İpucu"
                  text="İzin adları CASL ve sayfa erişimleriyle eşleşir. Örnek: read_montaj => montaj sayfalarını görüntüleme. update_planlama => Planlama sayfalarında güncelleme aksiyonları." />
              </div>
            </div>
          </VCardText>
        </VWindowItem>
      </VWindow>

      <VOverlay persistent contained :model-value="loading" class="align-center justify-center">
        <VCard class="pa-4 d-flex align-center justify-center">
          <VProgressCircular indeterminate color="primary" class="me-3" />
          <span>{{ loadingMessage }}</span>
        </VCard>
      </VOverlay>

      <VSnackbar v-model="snackbar" :color="snackbarColor" timeout="2500">
        {{ snackbarMessage }}
      </VSnackbar>
    </VCard>
  </VContainer>
</template>

<script setup lang="ts">
import { useAbility } from '@/plugins/casl/composables/useAbility'
import { normalizeAbilityRules } from '@/utils/ability-normalizer'
import axios from 'axios'
import { computed, onMounted, ref } from 'vue'

// Sayfa yetkisi
definePage({ meta: { action: ['manage', 'read'], subject: ['users', 'roles', 'genel', 'all'] } })

// State
const activeTab = ref<'users' | 'roles' | 'perms'>('users')
const loading = ref(false)
const loadingMessage = ref('Yükleniyor...')
const snackbar = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref<'success' | 'error' | 'info' | 'warning' | 'primary'>('success')
const ability = useAbility()
const currentUserCookie = useCookie<any>('userData')

// Users
const users = ref<{ id: number; name: string; email: string }[]>([])
const userSearch = ref('')
const selectedUserId = ref<number | null>(null)
const allRoles = ref<{ id: number; name: string }[]>([])
const userRoles = ref<string[]>([])

const assignedPerms = ref<{ id: number; name: string }[]>([])
const availablePerms = ref<{ id: number; name: string }[]>([])

const filteredUsers = computed(() => {
  const q = String(userSearch.value ?? '').trim().toLowerCase()
  if (!q) return users.value
  return users.value.filter(u => (u.name + ' ' + u.email).toLowerCase().includes(q))
})

// Roles
const roles = ref<any[]>([])
const selectedRoleId = ref<number | null>(null)
const rolePerms = ref<string[]>([])
const newRoleName = ref('')
const roleSearch = ref('')
const permTemplates = [
  { key: 'planlama_oper', label: 'Planlama Operatörü', perms: ['read_planlama', 'update_planlama', 'read_montaj'] },
  { key: 'montaj_sorumlu', label: 'Montaj Sorumlusu', perms: ['read_montaj', 'update_montaj'] },
  { key: 'depo_gorevli', label: 'Depo Görevlisi', perms: ['read_depo', 'update_depo'] },
  { key: 'okuyucu', label: 'Sadece Görüntüleme', perms: ['read_genel', 'read_montaj', 'read_planlama', 'read_depo'] },
  { key: 'admin', label: 'Yönetici (Tüm Yetkiler)', perms: ['manage_all'] },
]
const selectedTemplateKey = ref<string | null>(null)

// Permissions
const allPerms = ref<{ id: number; name: string }[]>([])
const permAction = ref<string | null>('read')
const permSubject = ref<string | null>('montaj')
const customPerm = ref('')
const permSearch = ref('')
const actions = ['read', 'create', 'update', 'delete', 'manage']
const subjects = ['genel', 'montaj', 'planlama', 'kalite', 'depo', 'satinalma', 'satis', 'users', 'roles', 'mekanik']
const userPermSearch = ref('')

const filteredPerms = computed(() => {
  const q = String(permSearch.value ?? '').trim().toLowerCase()
  if (!q) return allPerms.value
  return allPerms.value.filter(p => p.name.toLowerCase().includes(q))
})
const filteredRoles = computed(() => {
  const q = String(roleSearch.value ?? '').trim().toLowerCase()
  if (!q) return roles.value
  return roles.value.filter((r: any) => String(r.name).toLowerCase().includes(q))
})
const filteredAssignedPerms = computed(() => {
  const q = String(userPermSearch.value ?? '').trim().toLowerCase()
  if (!q) return assignedPerms.value
  return assignedPerms.value.filter(p => p.name.toLowerCase().includes(q))
})
const filteredAvailablePerms = computed(() => {
  const q = String(userPermSearch.value ?? '').trim().toLowerCase()
  if (!q) return availablePerms.value
  return availablePerms.value.filter(p => p.name.toLowerCase().includes(q))
})

// Seçili rol için atanmış izinlerin listesi (görüntüleme amaçlı)
const roleAssignedPerms = computed(() => {
  const set = new Set(rolePerms.value)
  return allPerms.value.filter(p => set.has(p.name))
})

function showLoading(msg: string) {
  loadingMessage.value = msg
  loading.value = true
}
function hideLoading() {
  loading.value = false
  loadingMessage.value = ''
}

function showSnack(msg: string, color: 'success' | 'error' | 'info' | 'warning' | 'primary' = 'success') {
  snackbarMessage.value = msg
  snackbarColor.value = color
  snackbar.value = true
}

// Rol/izin değişikliklerinden sonra, etkilenen kullanıcı şu anki kullanıcı ise CASL yeteneklerini yenile
async function refreshAbilityIfCurrentUser(affectedUserId: number | null | undefined) {
  try {
    const meId = currentUserCookie.value?.id
    if (!affectedUserId || !meId || Number(affectedUserId) !== Number(meId)) return
    const res = await axios.get('/api/auth/debug')
    const rules = (res.data?.ability_rules as any) || []
    const normalized = normalizeAbilityRules(rules)
    // Cookie'yi ve runtime ability'yi güncelle
    useCookie('userAbilityRules').value = rules as any
    ability.update(normalized as any)
    showSnack('Yetkileriniz güncellendi.', 'info')
  } catch (e) {
    // sessizce geç
  }
}

// API helpers
async function loadUsers() {
  showLoading('Kullanıcılar yükleniyor...')
  try {
    const res = await axios.get('/api/users/all-basic')
    users.value = res.data || []
  } catch (e: any) {
    showSnack('Kullanıcılar yüklenemedi.', 'error')
  } finally { hideLoading() }
}

async function loadRoles() {
  showLoading('Roller yükleniyor...')
  try {
    const res = await axios.get('/api/roles')
    roles.value = res.data?.roles || []
    const resAvail = await axios.get('/api/roles/available')
    allRoles.value = resAvail.data?.roles || []
  } catch (e: any) {
    showSnack('Roller yüklenemedi.', 'error')
  } finally { hideLoading() }
}

async function loadPerms() {
  showLoading('İzinler yükleniyor...')
  try {
    const res = await axios.get('/api/permissions')
    allPerms.value = res.data || []
  } catch (e: any) {
    showSnack('İzinler yüklenemedi.', 'error')
  } finally { hideLoading() }
}

function selectUser(u: { id: number; name: string; email: string }) {
  selectedUserId.value = u.id
  fetchUserRoles()
  fetchUserPerms()
}

async function fetchUserRoles() {
  if (!selectedUserId.value) return
  showLoading('Kullanıcı rolleri yükleniyor...')
  try {
    const res = await axios.get(`/api/users/${selectedUserId.value}/roles`)
    const roleNames: string[] = res.data?.roles || []
    userRoles.value = roleNames
  } finally { hideLoading() }
}

async function saveUserRoles() {
  if (!selectedUserId.value) return
  showLoading('Roller kaydediliyor...')
  try {
    await axios.post(`/api/users/${selectedUserId.value}/roles`, { roles: userRoles.value })
    // Roller sekmesindeki kullanıcı sayıları güncellensin
    await loadRoles()
    // Kullanıcıya atanmış/atanabilir izin listelerini tazele
    await fetchUserPerms()
    showSnack('Kullanıcının rolleri güncellendi.', 'success')
    await refreshAbilityIfCurrentUser(selectedUserId.value)
  } catch (e: any) {
    showSnack('Roller kaydedilemedi.', 'error')
  } finally { hideLoading() }
}

async function fetchUserPerms() {
  if (!selectedUserId.value) return
  showLoading('Kullanıcı izinleri yükleniyor...')
  try {
    const res = await axios.get(`/api/users/${selectedUserId.value}/permissions`)
    assignedPerms.value = res.data?.assigned || []
    availablePerms.value = res.data?.available || []
  } finally { hideLoading() }
}

async function addPerm(id: number) {
  if (!selectedUserId.value) return
  showLoading('İzin ekleniyor...')
  try {
    await axios.post(`/api/users/${selectedUserId.value}/permissions/${id}`)
    await fetchUserPerms()
    showSnack('İzin eklendi.', 'success')
    await refreshAbilityIfCurrentUser(selectedUserId.value)
  } catch (e: any) {
    showSnack('İzin eklenemedi.', 'error')
  } finally { hideLoading() }
}

async function removePerm(id: number) {
  if (!selectedUserId.value) return
  showLoading('İzin kaldırılıyor...')
  try {
    await axios.delete(`/api/users/${selectedUserId.value}/permissions/${id}`)
    await fetchUserPerms()
    showSnack('İzin kaldırıldı.', 'success')
    await refreshAbilityIfCurrentUser(selectedUserId.value)
  } catch (e: any) {
    showSnack('İzin kaldırılamadı.', 'error')
  } finally { hideLoading() }
}

async function selectRole(r: any) {
  selectedRoleId.value = r.id
  showLoading('Rol detayları yükleniyor...')
  try {
    const res = await axios.get(`/api/roles/${r.id}`)
    const perms = res.data?.role?.permissions || []
    rolePerms.value = perms.map((p: any) => p.name)
  } catch (e: any) {
    // Liste yanıtında permissions yoksa boş kalabilir; kullanıcı yine de combobox ile ekleyebilir
    rolePerms.value = (r.permissions || []).map((p: any) => p.name)
  } finally { hideLoading() }
}

async function createRole() {
  const name = newRoleName.value.trim()
  if (!name) return
  showLoading('Rol oluşturuluyor...')
  try {
    await axios.post('/api/roles', { name })
    newRoleName.value = ''
    await loadRoles()
    showSnack('Rol oluşturuldu.', 'success')
  } catch (e: any) {
    if (axios.isAxiosError(e) && e.response?.status === 422) {
      const msg = (e.response.data?.errors?.name?.[0]) || e.response.data?.message || 'Rol adı geçersiz veya zaten kayıtlı.'
      showSnack(msg, 'error')
    } else {
      showSnack('Rol oluşturulurken bir hata oluştu.', 'error')
    }
  } finally { hideLoading() }
}

async function saveRolePerms() {
  if (!selectedRoleId.value) return
  showLoading('Rol izinleri kaydediliyor...')
  try {
    await axios.put(`/api/roles/${selectedRoleId.value}`, { name: roles.value.find(r => r.id === selectedRoleId.value)?.name, permissions: rolePerms.value })
    await loadRoles()
    // seçili rolü güncelle
    const sel = roles.value.find((r: any) => r.id === selectedRoleId.value)
    if (sel) selectRole(sel)
    showSnack('Rol izinleri güncellendi.', 'success')
  } catch (e: any) {
    if (axios.isAxiosError(e) && e.response?.status === 422) {
      const msg = (e.response.data?.errors?.name?.[0]) || e.response.data?.message || 'Güncelleme geçersiz: Rol adı çakışıyor.'
      showSnack(msg, 'error')
    } else {
      showSnack('Rol güncellenirken bir hata oluştu.', 'error')
    }
  } finally { hideLoading() }
}

async function deleteRole() {
  if (!selectedRoleId.value) return
  if (!confirm('Seçili rol silinsin mi? Bu işlem geri alınamaz.')) return
  showLoading('Rol siliniyor...')
  try {
    await axios.delete(`/api/roles/${selectedRoleId.value}`)
    selectedRoleId.value = null
    rolePerms.value = []
    await loadRoles()
    showSnack('Rol silindi.', 'success')
  } finally { hideLoading() }
}

async function createCompositePerm() {
  if (!permAction.value || !permSubject.value) return
  customPerm.value = `${permAction.value}_${permSubject.value}`
  await createCustomPerm()
}

async function createCustomPerm() {
  const name = customPerm.value.trim()
  if (!name) return
  showLoading('İzin oluşturuluyor...')
  try {
    await axios.post('/api/permissions', { name })
    customPerm.value = ''
    await loadPerms()
    showSnack('İzin oluşturuldu.', 'success')
  } catch (e: any) {
    if (axios.isAxiosError(e) && e.response?.status === 422) {
      const msg = (e.response.data?.errors?.name?.[0]) || e.response.data?.message || 'İzin adı geçersiz veya zaten kayıtlı.'
      showSnack(msg, 'error')
    } else {
      showSnack('İzin oluşturulurken bir hata oluştu.', 'error')
    }
  } finally { hideLoading() }
}

async function removeCustomPerm(id: number) {
  if (!confirm('Bu izin silinsin mi?')) return
  showLoading('İzin siliniyor...')
  try {
    await axios.delete(`/api/permissions/${id}`)
    await loadPerms()
    showSnack('İzin silindi.', 'success')
  } catch (e: any) {
    showSnack('İzin silinemedi.', 'error')
  } finally { hideLoading() }
}

function applyTemplate() {
  if (!selectedTemplateKey.value) return
  const tpl = permTemplates.find(t => t.key === selectedTemplateKey.value)
  if (!tpl) return
  const current = new Set(rolePerms.value)
  for (const p of tpl.perms) current.add(p)
  rolePerms.value = Array.from(current)
  showSnack('Şablon uygulandı.', 'success')
}

onMounted(async () => {
  await Promise.all([loadUsers(), loadRoles(), loadPerms()])
})
</script>

<script setup lang="ts">
import { emailValidator, requiredValidator } from '@core/utils/validators';
import { VForm } from 'vuetify/components/VForm';

definePage({
  meta: { action: ['manage', 'read'], subject: ['montaj', 'all'] }
});

interface UserPayload {
  id?: number
  name: string
  email: string
  password?: string
  roles: string[]
  permissions: string[]
}

const props = defineProps<{
  modelValue: boolean
  editUser?: any | null
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', v: boolean): void
  (e: 'saved'): void
}>()

const isOpen = computed({
  get: () => props.modelValue,
  set: v => emit('update:modelValue', v),
})

const refVForm = ref<VForm>()

const form = reactive<UserPayload>({
  id: undefined,
  name: '',
  email: '',
  password: '',
  roles: [],
  permissions: [],
})

const roles = ref<{ title: string; value: string }[]>([])
const permissions = ref<{ title: string; value: string }[]>([])
const loading = ref(false)
const saving = ref(false)
const errorMessage = ref<string | null>(null)

watch(
  () => props.editUser,
  val => {
    if (val) {
      form.id = val.id
      form.name = val.name
      form.email = val.email
      form.password = ''
      form.roles = (val.roles || []).map((r: any) => r.name)
      form.permissions = (val.permissions || []).map((p: any) => p.name)
    } else {
      form.id = undefined
      form.name = ''
      form.email = ''
      form.password = ''
      form.roles = []
      form.permissions = []
    }
  },
  { immediate: true }
)

const fetchMeta = async () => {
  loading.value = true
  try {
    const [rolesRes, permsRes] = await Promise.all([
      $api('/roles/available'),
      $api('/permissions'),
    ])
    roles.value = (rolesRes.roles || []).map((r: any) => ({ title: r.name, value: r.name }))
    permissions.value = (permsRes || []).map((p: any) => ({ title: p.name, value: p.name }))
  } catch (e: any) {
    errorMessage.value = e?.message || 'Meta veriler alınamadı.'
  } finally {
    loading.value = false
  }
}

watch(isOpen, v => {
  if (v) fetchMeta()
})

const close = () => {
  isOpen.value = false
}

const onSave = async () => {
  errorMessage.value = null
  const result = await refVForm.value?.validate()
  if (!result?.valid) return

  try {
    saving.value = true
    const payload: any = {
      name: form.name,
      email: form.email,
      roles: form.roles,
      permissions: form.permissions,
    }
    if (form.password) payload.password = form.password

    if (form.id) {
      await $api(`/users/${form.id}`, { method: 'PUT', body: payload })
    } else {
      await $api('/users', { method: 'POST', body: { ...payload, password: form.password || undefined } })
    }

    emit('saved')
    close()
  } catch (e: any) {
    errorMessage.value = e?.data?.message || e?.message || 'Kayıt başarısız.'
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <VNavigationDrawer
    v-model="isOpen"
    location="end"
    temporary
    width="480"
  >
    <VToolbar density="comfortable" flat>
      <VToolbarTitle>{{ form.id ? 'Kullanıcıyı Düzenle' : 'Yeni Kullanıcı' }}</VToolbarTitle>
      <VSpacer />
      <VBtn icon variant="text" @click="close">
        <VIcon icon="tabler-x" />
      </VBtn>
    </VToolbar>

    <VDivider />

    <VAlert v-if="errorMessage" type="error" variant="tonal" class="ma-4">
      {{ errorMessage }}
    </VAlert>

    <VCard flat>
      <VCardText>
        <VForm ref="refVForm" @submit.prevent="onSave">
          <VRow>
            <VCol cols="12">
              <AppTextField v-model="form.name" label="Ad Soyad" :rules="[requiredValidator]" />
            </VCol>
            <VCol cols="12">
              <AppTextField v-model="form.email" label="E-posta" :rules="[requiredValidator, emailValidator]" />
            </VCol>
            <VCol cols="12">
              <AppTextField v-model="form.password" :label="form.id ? 'Şifre (opsiyonel)' : 'Şifre'" :type="'password'" :rules="form.id ? [] : [requiredValidator]" />
            </VCol>
            <VCol cols="12">
              <AppSelect v-model="form.roles" :items="roles" label="Roller" multiple chips clearable />
            </VCol>
            <VCol cols="12">
              <AppSelect v-model="form.permissions" :items="permissions" label="İzinler" multiple chips clearable />
            </VCol>
            <VCol cols="12" class="d-flex justify-end gap-3">
              <VBtn variant="tonal" color="secondary" @click="close">İptal</VBtn>
              <VBtn type="submit" :loading="saving" :disabled="saving">Kaydet</VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </VNavigationDrawer>
</template>

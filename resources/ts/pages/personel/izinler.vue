<template>
  <div>
    <h3>Kullanıcı İzinleri</h3>
    <ul v-if="permissions.length > 0">
      <li v-for="permission in permissions" :key="permission.id">
        {{ permission.name }}
      </li>
    </ul>
    <p v-else>Kullanıcıya ait bir izin bulunamadı.</p>
    <VBtn v-if="permissions.some(permission => permission.name === 'read')">Ekle</VBtn>
    <VBtn v-if="permissions.some(permission => permission.name === 'update')">Değiştir</VBtn>
    <VBtn v-if="permissions.some(permission => permission.name === 'delete')">Sil</VBtn>
    <VBtn v-if="permissions.some(permission => permission.name === 'manage')">Ne bileyim</VBtn>

    <hr>
    <br>
    <div>
      @can('edit articles')
      You can EDIT ARTICLES.
      @endcan
      @can('publish articles')
      You can PUBLISH ARTICLES.
      @endcan
      @can('only super-admins can see this section')
      Congratulations, you are a super-admin!
      @endcan
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';





const permissions = ref([]); // Kullanıcı izinlerini saklayacağımız değişken
const userId = 131; // Test için 1 numaralı kullanıcıyı seçtik

// İzinleri API'den almak için bir fonksiyon
const fetchPermissions = () => {
  axios.get('/api/izin-al/47')
    .then(response => {
      permissions.value = response.data;
    })
    .catch(error => {
      console.error('Roller alınırken hata oluştu:', error)
    })
};

// Component yüklendiğinde izinleri getir
onMounted(() => {
  fetchPermissions();
});
</script>

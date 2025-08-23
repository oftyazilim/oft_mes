<template>
  <VCard>
    <VCardTitle>Yemek Listesi</VCardTitle>

    <VCardText>
      <div>
        <form v-show="yuklemeGoster" @submit.prevent="handleUpload">
          <input type="file" accept="application/pdf" @change="onFileChange" />
          <VBtn type="submit" color="primary" class="mt-2">Yükle</VBtn>
        </form>
      </div>
      <div class="mt-6">
        <iframe :src="pdfUrl + '#toolbar=0&navpanes=0&scrollbar=0'" width="100%" height="800px" style="border: none" />
      </div>
    </VCardText>
  </VCard>
</template>

<script setup lang="ts">
import type { Rule } from "./ability";

// Kullanıcı kurallarını al
const userAbilityRules = useCookie<Rule[]>("userAbilityRules").value || [];

let yuklemeGoster = false;
// İzinleri kontrol edip konsola yazdır
userAbilityRules.forEach((rule, index) => {
  if ((rule.action === "all" && rule.subject === "Planlama") || (rule.action === "read" && rule.subject === "Planlama")) yuklemeGoster = true;
});





import { ref, onMounted } from 'vue'
import axios from 'axios'

const selectedFile = ref<File | null>(null)
const pdfUrl = ref('/storage/yemeklistesi/yemeklistesi.pdf')
const userData = useCookie<any>("userData");

function onFileChange(event: Event) {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (file) selectedFile.value = file
}

async function handleUpload() {
  if (!selectedFile.value) return

  const formData = new FormData()
  formData.append('pdf', selectedFile.value)

  // Eğer backend'e userID de göndermek istiyorsan:
  formData.append('user_id', userData.value.id)

  try {
    await axios.post('/api/upload-yemek-listesi', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })

    alert('Yemek listesi yüklendi!')
    pdfUrl.value = '/storage/yemeklistesi/yemeklistesi.pdf?' + new Date().getTime()
  } catch (err) {
    alert('Yükleme başarısız oldu.')
    console.error(err)
  }
}

</script>
